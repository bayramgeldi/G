<x-layout :title="__('app.leaderboard')">
    <h1 class="mb-5 text-2xl font-black">{{ __('app.leaderboard') }}</h1>
    <div class="overflow-hidden rounded-lg border border-stone-200 bg-white">
        @foreach ($users as $user)
            <div class="grid grid-cols-[3rem_1fr_auto] items-center gap-3 border-b border-stone-100 px-4 py-3 last:border-b-0">
                <div class="text-lg font-black text-emerald-800">{{ $loop->iteration }}</div>
                <div class="min-w-0">
                    <div class="truncate font-bold">{{ $user->name }}</div>
                    <div class="text-xs text-stone-500">{{ $user->entries_count }} {{ __('app.word_or_phrase') }}</div>
                </div>
                <div class="text-right text-sm">
                    <div class="font-black">{{ $user->definitions_count }}</div>
                    <div class="text-xs text-stone-500">{{ __('app.contributions') }}</div>
                    <div class="mt-1 text-xs text-stone-500">{{ (int) $user->received_votes_sum }} {{ __('app.votes') }}</div>
                </div>
            </div>
        @endforeach
    </div>
</x-layout>
