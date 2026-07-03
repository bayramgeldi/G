<x-layout :title="__('app.review_anonymous')" :description="__('app.seo_review_anonymous_description')" :canonical="route('moderation.anonymous-submissions')" :noindex="true">
    <section class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-black">{{ __('app.review_anonymous') }}</h1>
        <p class="mt-2 text-sm leading-6 text-stone-600">{{ __('app.anonymous_review_intro') }}</p>

        <div class="mt-5 space-y-4">
            @forelse ($submissions as $submission)
                @php
                    $totalVotes = $submission->approvals_count + $submission->rejects_count;
                    $approvalPercent = $totalVotes > 0 ? (int) floor(($submission->approvals_count / $totalVotes) * 100) : 0;
                    $viewerVote = $submission->votes->firstWhere('user_id', auth()->id());
                @endphp

                <article class="rounded-lg border border-stone-200 bg-white p-4 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="break-words text-xl font-black">{{ $submission->term }}</h2>
                            <div class="mt-1 text-xs font-bold uppercase text-stone-500">{{ __('app.anonymous') }}</div>
                        </div>
                        <div class="rounded-md bg-stone-100 px-3 py-2 text-right text-xs font-bold text-stone-700">
                            {{ $approvalPercent }}%<br>{{ __('app.approved') }}
                        </div>
                    </div>

                    <p class="mt-4 whitespace-pre-line break-words leading-7">{{ $submission->meaning }}</p>

                    @if ($submission->example)
                        <blockquote class="mt-3 border-l-4 border-amber-400 pl-3 text-sm leading-6 text-stone-600">{{ $submission->example }}</blockquote>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm text-stone-600">
                        <div>
                            {{ __('app.approve') }}: {{ $submission->approvals_count }}
                            <span class="mx-1">|</span>
                            {{ __('app.reject') }}: {{ $submission->rejects_count }}
                        </div>

                        @if ($viewerVote)
                            <div class="font-bold text-stone-700">{{ __('app.already_voted') }}</div>
                        @else
                            <form method="post" action="{{ route('moderation.anonymous-submissions.vote', $submission) }}" class="flex gap-2">
                                @csrf
                                <button name="vote" value="approve" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-bold text-white hover:bg-emerald-800">{{ __('app.approve') }}</button>
                                <button name="vote" value="reject" class="rounded-md border border-stone-300 px-3 py-2 text-sm font-bold text-stone-700 hover:bg-stone-100">{{ __('app.reject') }}</button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-lg border border-dashed border-stone-300 bg-white p-8 text-center text-stone-600">
                    {{ __('app.no_anonymous_submissions') }}
                </div>
            @endforelse
        </div>

        <div class="mt-6">{{ $submissions->links() }}</div>
    </section>
</x-layout>
