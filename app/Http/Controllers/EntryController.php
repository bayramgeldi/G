<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Support\NormalizesTurkmenText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntryController extends Controller
{
    public function index(Request $request)
    {
        $entries = Entry::query()
            ->visible()
            ->search($request->string('q')->toString())
            ->with('user')
            ->withCount(['visibleDefinitions as definitions_count'])
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('entries.index', [
            'entries' => $entries,
            'query' => $request->string('q')->toString(),
        ]);
    }

    public function create()
    {
        return view('entries.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'term' => ['required', 'string', 'max:120'],
            'meaning' => ['required', 'string', 'max:2000'],
            'example' => ['nullable', 'string', 'max:2000'],
        ]);

        $entry = DB::transaction(function () use ($request, $validated) {
            $normalized = NormalizesTurkmenText::normalize($validated['term']);
            $entry = Entry::firstOrCreate(
                ['normalized_term' => $normalized],
                [
                    'user_id' => $request->user()->id,
                    'term' => $validated['term'],
                    'slug' => Entry::uniqueSlug($validated['term']),
                ]
            );

            $entry->definitions()->create([
                'user_id' => $request->user()->id,
                'meaning' => $validated['meaning'],
                'example' => $validated['example'] ?? null,
            ]);

            return $entry;
        });

        return redirect()->route('entries.show', $entry)->with('status', __('app.saved'));
    }

    public function show(Entry $entry)
    {
        $viewer = auth()->user();
        $canInspectHiddenEntry = $viewer && ($viewer->is_admin || $viewer->id === $entry->user_id);

        abort_if($entry->is_hidden && ! $canInspectHiddenEntry, 404);

        $definitions = $entry->definitions()
            ->when(! $viewer?->is_admin, function ($query) use ($viewer) {
                $query->where(function ($query) use ($viewer) {
                    $query->where('is_hidden', false);

                    if ($viewer) {
                        $query->orWhere('user_id', $viewer->id);
                    }
                });
            })
            ->with([
                'user',
                'openAppeal.votes',
                'votes' => fn ($query) => $query->where('user_id', auth()->id()),
            ])
            ->orderByDesc('votes_count')
            ->oldest()
            ->get();

        return view('entries.show', [
            'entry' => $entry,
            'definitions' => $definitions,
            'canModerate' => $viewer?->isModerationEligible() ?? false,
            'entryAppeal' => $entry->openAppeal()->with('votes')->first(),
        ]);
    }
}
