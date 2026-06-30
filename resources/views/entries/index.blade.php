<x-layout :title="__('app.app_name')">
    <section class="mb-6 rounded-lg bg-emerald-800 px-4 py-6 text-white sm:px-6">
        <h1 class="text-2xl font-black sm:text-4xl">{{ __('app.app_name') }}</h1>
        <p class="mt-2 max-w-2xl text-sm leading-6 text-emerald-50">{{ __('app.tagline') }}</p>
        <form method="get" action="{{ route('home') }}" class="mt-5 flex gap-2">
            <input name="q" value="{{ $query }}" class="min-w-0 flex-1 rounded-md border-0 px-3 py-3 text-stone-950 placeholder:text-stone-400" placeholder="{{ __('app.search_placeholder') }}">
            <button class="rounded-md bg-stone-950 px-4 py-3 text-sm font-bold text-white">{{ __('app.search') }}</button>
        </form>
    </section>

    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-black">{{ __('app.latest') }}</h2>
        @auth
            <a href="{{ route('entries.create') }}" class="text-sm font-bold text-emerald-800">{{ __('app.suggest') }}</a>
        @endauth
    </div>

    @forelse ($entries as $entry)
        <a href="{{ route('entries.show', $entry) }}" class="mb-3 block rounded-lg border border-stone-200 bg-white p-4 shadow-sm hover:border-emerald-500">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 class="break-words text-xl font-black">{{ $entry->term }}</h3>
                    <p class="mt-1 text-sm text-stone-500">{{ __('app.by') }} {{ $entry->user->name ?? 'unknown' }}</p>
                </div>
                <div class="shrink-0 rounded-md bg-stone-100 px-2 py-1 text-center text-xs font-bold text-stone-700">
                    {{ $entry->definitions_count }}<br>{{ __('app.definitions') }}
                </div>
            </div>
        </a>
    @empty
        <div class="rounded-lg border border-dashed border-stone-300 bg-white p-8 text-center text-stone-600">
            {{ __('app.no_entries') }}
        </div>
    @endforelse

    <div class="mt-6">{{ $entries->links() }}</div>
</x-layout>
