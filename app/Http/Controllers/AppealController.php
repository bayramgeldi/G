<?php

namespace App\Http\Controllers;

use App\Models\Appeal;
use App\Models\Definition;
use App\Models\Entry;
use App\Support\ModeratesContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AppealController extends Controller
{
    public function appealEntry(Request $request, Entry $entry)
    {
        return $this->store($request, $entry);
    }

    public function appealDefinition(Request $request, Definition $definition)
    {
        return $this->store($request, $definition);
    }

    public function vote(Request $request, Appeal $appeal)
    {
        abort_unless($request->user()->isModerationEligible(), 403);
        abort_unless($appeal->isOpen(), 404);

        $validated = $request->validate([
            'vote' => ['required', Rule::in(['restore', 'keep_hidden'])],
        ]);

        DB::transaction(function () use ($request, $appeal, $validated): void {
            $vote = $appeal->votes()->firstOrCreate(
                ['user_id' => $request->user()->id],
                ['vote' => $validated['vote']]
            );

            if (! $vote->wasRecentlyCreated) {
                return;
            }

            $column = $validated['vote'] === 'restore' ? 'restore_votes_count' : 'keep_hidden_votes_count';
            $appeal->increment($column);
            $appeal->refresh();

            if (
                $appeal->restore_votes_count >= config('moderation.appeal_restore_threshold')
                && $appeal->restore_votes_count > $appeal->keep_hidden_votes_count
            ) {
                ModeratesContent::restoreFromAppeal($appeal->appealable, $appeal);
            }
        });

        return back()->with('status', __('app.appeal_vote_saved'));
    }

    private function store(Request $request, Model $content)
    {
        abort_unless($content->is_hidden, 404);
        abort_unless($request->user()->id === ModeratesContent::authorId($content), 403);

        $validated = $request->validate([
            'statement' => ['required', 'string', 'max:2000'],
        ]);

        $appeal = $content->appeals()->firstOrCreate(
            ['status' => 'open'],
            [
                'user_id' => $request->user()->id,
                'statement' => $validated['statement'],
            ]
        );

        if (! $appeal->wasRecentlyCreated) {
            return back()->withErrors(['appeal' => __('app.appeal_duplicate')]);
        }

        ModeratesContent::log('appeal_opened', $content, $request->user(), 'author_appeal', [
            'appeal_id' => $appeal->id,
        ], 'author');

        return back()->with('status', __('app.appeal_opened'));
    }
}
