<x-layout :title="$entry->term">
    <section class="mb-5">
        <div class="flex items-start justify-between gap-3">
            <div>
                <h1 data-lookup-text class="break-words text-3xl font-black sm:text-5xl">{{ $entry->term }}</h1>
                <p class="mt-2 text-sm text-stone-500">{{ __('app.click_words') }}</p>
            </div>
            @auth
                @if (auth()->user()->is_admin)
                    <form method="post" action="{{ route('admin.entries.hide', $entry) }}">
                        @csrf
                        @method('patch')
                        <button class="rounded-md border border-red-200 px-3 py-2 text-sm font-bold text-red-700">{{ __('app.emergency_hide') }}</button>
                    </form>
                @endif
            @endauth
        </div>

        @if ($entry->is_hidden)
            <div class="mt-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">{{ __('app.hidden_content') }}</div>

            @auth
                @if (auth()->id() === $entry->user_id && ! $entryAppeal)
                    <form method="post" action="{{ route('entries.appeal', $entry) }}" class="mt-4 space-y-3 rounded-lg border border-stone-200 bg-white p-4">
                        @csrf
                        <label class="block">
                            <span class="text-sm font-bold">{{ __('app.appeal_statement') }}</span>
                            <textarea name="statement" required rows="3" class="mt-1 w-full rounded-md border border-stone-300 px-3 py-3"></textarea>
                        </label>
                        <button class="rounded-md bg-emerald-700 px-4 py-3 text-sm font-bold text-white">{{ __('app.open_appeal') }}</button>
                    </form>
                @endif
            @endauth

            @if ($entryAppeal)
                <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm">
                    <div class="font-bold">{{ __('app.appeal_open') }}</div>
                    <p class="mt-2">{{ $entryAppeal->statement }}</p>
                    <p class="mt-2 text-stone-600">{{ __('app.restore') }}: {{ $entryAppeal->restore_votes_count }} | {{ __('app.keep_hidden') }}: {{ $entryAppeal->keep_hidden_votes_count }}</p>
                    @auth
                        @if ($canModerate && $entryAppeal->isOpen())
                            <form method="post" action="{{ route('appeals.vote', $entryAppeal) }}" class="mt-3 flex gap-2">
                                @csrf
                                <button name="vote" value="restore" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-bold text-white">{{ __('app.restore') }}</button>
                                <button name="vote" value="keep_hidden" class="rounded-md border border-stone-300 px-3 py-2 text-sm font-bold">{{ __('app.keep_hidden') }}</button>
                            </form>
                        @endif
                    @endauth
                </div>
            @endif
        @elseif ($canModerate)
            <form method="post" action="{{ route('entries.report', $entry) }}" class="mt-4 grid gap-2 rounded-lg border border-stone-200 bg-white p-3 sm:grid-cols-[1fr_auto]">
                @csrf
                <select name="reason" required class="rounded-md border border-stone-300 px-3 py-2 text-sm">
                    @foreach (config('moderation.reasons') as $reason)
                        <option value="{{ $reason }}">{{ __('app.report_reason_'.$reason) }}</option>
                    @endforeach
                </select>
                <button class="rounded-md border border-amber-300 px-3 py-2 text-sm font-bold text-amber-800">{{ __('app.report') }}</button>
            </form>
        @endif
    </section>

    @forelse ($definitions as $definition)
        <article class="mb-4 rounded-lg border {{ $definition->is_hidden ? 'border-red-200 bg-red-50' : 'border-stone-200 bg-white' }} p-4 shadow-sm">
            <div class="flex items-start gap-4">
                <form method="post" action="{{ route('definitions.vote', $definition) }}" class="shrink-0">
                    @csrf
                    <button @guest disabled @endguest class="rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-center font-black text-emerald-900 disabled:opacity-50">
                        &uarr;<br>{{ $definition->votes_count }}
                    </button>
                </form>
                <div class="min-w-0 flex-1">
                    @if ($definition->is_hidden)
                        <div class="mb-2 text-xs font-bold uppercase text-red-700">{{ __('app.hidden_content') }}</div>
                    @endif
                    <p data-lookup-text class="whitespace-pre-line break-words leading-7">{{ $definition->meaning }}</p>
                    @if ($definition->example)
                        <blockquote data-lookup-text class="mt-3 border-l-4 border-amber-400 pl-3 text-sm leading-6 text-stone-600">{{ $definition->example }}</blockquote>
                    @endif
                    <div class="mt-3 flex items-center justify-between gap-3 text-xs text-stone-500">
                        <span>{{ __('app.by') }} {{ $definition->user->name }}</span>
                        @auth
                            @if (auth()->user()->is_admin)
                                <form method="post" action="{{ route('admin.definitions.hide', $definition) }}">
                                    @csrf
                                    @method('patch')
                                    <button class="font-bold text-red-700">{{ __('app.emergency_hide') }}</button>
                                </form>
                            @endif
                        @endauth
                    </div>

                    @if (! $definition->is_hidden && $canModerate)
                        <form method="post" action="{{ route('definitions.report', $definition) }}" class="mt-3 flex gap-2">
                            @csrf
                            <select name="reason" required class="min-w-0 flex-1 rounded-md border border-stone-300 px-3 py-2 text-sm">
                                @foreach (config('moderation.reasons') as $reason)
                                    <option value="{{ $reason }}">{{ __('app.report_reason_'.$reason) }}</option>
                                @endforeach
                            </select>
                            <button class="rounded-md border border-amber-300 px-3 py-2 text-sm font-bold text-amber-800">{{ __('app.report') }}</button>
                        </form>
                    @endif

                    @if ($definition->is_hidden)
                        @if (auth()->id() === $definition->user_id && ! $definition->openAppeal)
                            <form method="post" action="{{ route('definitions.appeal', $definition) }}" class="mt-4 space-y-3">
                                @csrf
                                <textarea name="statement" required rows="3" class="w-full rounded-md border border-stone-300 px-3 py-3" placeholder="{{ __('app.appeal_statement') }}"></textarea>
                                <button class="rounded-md bg-emerald-700 px-4 py-2 text-sm font-bold text-white">{{ __('app.open_appeal') }}</button>
                            </form>
                        @endif

                        @if ($definition->openAppeal)
                            <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm">
                                <div class="font-bold">{{ __('app.appeal_open') }}</div>
                                <p class="mt-1">{{ $definition->openAppeal->statement }}</p>
                                <p class="mt-2 text-stone-600">{{ __('app.restore') }}: {{ $definition->openAppeal->restore_votes_count }} | {{ __('app.keep_hidden') }}: {{ $definition->openAppeal->keep_hidden_votes_count }}</p>
                                @auth
                                    @if ($canModerate && $definition->openAppeal->isOpen())
                                        <form method="post" action="{{ route('appeals.vote', $definition->openAppeal) }}" class="mt-3 flex gap-2">
                                            @csrf
                                            <button name="vote" value="restore" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-bold text-white">{{ __('app.restore') }}</button>
                                            <button name="vote" value="keep_hidden" class="rounded-md border border-stone-300 px-3 py-2 text-sm font-bold">{{ __('app.keep_hidden') }}</button>
                                        </form>
                                    @endif
                                @endauth
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </article>
    @empty
        <div class="rounded-lg border border-dashed border-stone-300 bg-white p-8 text-center text-stone-600">
            {{ __('app.no_definitions') }}
        </div>
    @endforelse

    @auth
        @if (! $entry->is_hidden)
            <section class="mt-8 rounded-lg border border-stone-200 bg-white p-4">
                <h2 class="text-lg font-black">{{ __('app.add_definition') }}</h2>
                <form method="post" action="{{ route('definitions.store', $entry) }}" class="mt-4 space-y-4">
                    @csrf
                    <textarea name="meaning" required rows="4" class="w-full rounded-md border border-stone-300 px-3 py-3" placeholder="{{ __('app.definition') }}">{{ old('meaning') }}</textarea>
                    <textarea name="example" rows="3" class="w-full rounded-md border border-stone-300 px-3 py-3" placeholder="{{ __('app.example') }}">{{ old('example') }}</textarea>
                    <button class="w-full rounded-md bg-emerald-700 px-4 py-3 font-bold text-white">{{ __('app.save') }}</button>
                </form>
            </section>
        @endif
    @endauth
</x-layout>
