<?php

namespace App\Http\Controllers;

use App\Models\Definition;
use App\Models\Entry;
use App\Support\ModeratesContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ModerationReportController extends Controller
{
    public function reportEntry(Request $request, Entry $entry)
    {
        return $this->store($request, $entry);
    }

    public function reportDefinition(Request $request, Definition $definition)
    {
        abort_if($definition->entry->is_hidden, 404);

        return $this->store($request, $definition);
    }

    private function store(Request $request, Model $content)
    {
        abort_if($content->is_hidden, 404);
        abort_unless($request->user()->isModerationEligible(), 403);

        $validated = $request->validate([
            'reason' => ['required', Rule::in(config('moderation.reasons'))],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $report = $content->reports()->firstOrCreate(
            ['user_id' => $request->user()->id],
            [
                'reason' => $validated['reason'],
                'note' => $validated['note'] ?? null,
            ]
        );

        if (! $report->wasRecentlyCreated) {
            return back()->withErrors(['report' => __('app.report_duplicate')]);
        }

        $reportCount = $content->reports()->count();

        if ($reportCount >= config('moderation.report_threshold')) {
            ModeratesContent::hideByReports($content, $reportCount);
        }

        return back()->with('status', __('app.report_saved'));
    }
}
