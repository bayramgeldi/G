<?php

namespace App\Http\Controllers;

use App\Models\AnonymousSubmission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AnonymousSubmissionController extends Controller
{
    public function index(Request $request)
    {
        abort_unless($request->user()->canReviewAnonymousSubmissions(), 403);

        $submissions = AnonymousSubmission::query()
            ->with(['votes' => fn ($query) => $query->latest(), 'publishedEntry'])
            ->withCount([
                'votes as approvals_count' => fn ($query) => $query->where('vote', AnonymousSubmission::VOTE_APPROVE),
                'votes as rejects_count' => fn ($query) => $query->where('vote', AnonymousSubmission::VOTE_REJECT),
            ])
            ->where('status', AnonymousSubmission::STATUS_PENDING)
            ->oldest()
            ->paginate(20);

        return view('moderation.anonymous-submissions', [
            'submissions' => $submissions,
        ]);
    }

    public function vote(Request $request, AnonymousSubmission $submission)
    {
        abort_unless($request->user()->canReviewAnonymousSubmissions(), 403);
        abort_unless($submission->isPending(), 404);

        $validated = $request->validate([
            'vote' => ['required', Rule::in([AnonymousSubmission::VOTE_APPROVE, AnonymousSubmission::VOTE_REJECT])],
        ]);

        $vote = $submission->votes()->firstOrCreate(
            ['user_id' => $request->user()->id],
            ['vote' => $validated['vote']]
        );

        if (! $vote->wasRecentlyCreated) {
            return back()->withErrors(['anonymous_submission' => __('app.anonymous_vote_duplicate')]);
        }

        if ($submission->publishIfApproved()) {
            return redirect()
                ->route('moderation.anonymous-submissions')
                ->with('status', __('app.anonymous_submission_published'));
        }

        return back()->with('status', __('app.anonymous_vote_saved'));
    }
}
