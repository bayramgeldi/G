<x-layout :title="__('app.suggest')">
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-black">{{ __('app.suggest') }}</h1>
        <form method="post" action="{{ route('entries.store') }}" class="mt-5 space-y-4 rounded-lg border border-stone-200 bg-white p-4">
            @csrf
            <label class="block">
                <span class="text-sm font-bold">{{ __('app.word_or_phrase') }}</span>
                <input name="term" value="{{ old('term') }}" required maxlength="120" class="mt-1 w-full rounded-md border border-stone-300 px-3 py-3">
            </label>
            <label class="block">
                <span class="text-sm font-bold">{{ __('app.definition') }}</span>
                <textarea name="meaning" required rows="5" class="mt-1 w-full rounded-md border border-stone-300 px-3 py-3">{{ old('meaning') }}</textarea>
            </label>
            <label class="block">
                <span class="text-sm font-bold">{{ __('app.example') }}</span>
                <textarea name="example" rows="3" class="mt-1 w-full rounded-md border border-stone-300 px-3 py-3">{{ old('example') }}</textarea>
            </label>
            <button class="w-full rounded-md bg-emerald-700 px-4 py-3 font-bold text-white hover:bg-emerald-800">{{ __('app.save') }}</button>
        </form>
    </div>
</x-layout>
