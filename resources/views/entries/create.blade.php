<x-layout :title="__('app.suggest')">
    <div class="mx-auto max-w-2xl">
        <h1 class="text-2xl font-black">{{ __('app.suggest') }}</h1>
        <form method="post" action="{{ route('entries.store') }}" class="mt-5 space-y-4 rounded-lg border border-stone-200 bg-white p-4">
            @csrf
            <label class="block">
                <span class="text-sm font-bold">{{ __('app.word_or_phrase') }}</span>
                <input
                    name="term"
                    value="{{ old('term') }}"
                    required
                    maxlength="120"
                    autocomplete="off"
                    data-dictionary-suggestion-input
                    class="mt-1 w-full rounded-md border border-stone-300 px-3 py-3"
                >
            </label>
            <div data-dictionary-suggestions class="hidden rounded-md border border-emerald-100 bg-emerald-50 p-3" aria-live="polite">
                <div class="text-xs font-bold uppercase text-emerald-900">{{ __('app.dictionary_hints') }}</div>
                <div data-dictionary-suggestion-list class="mt-2 space-y-2"></div>
            </div>
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

    <script>
        (() => {
            const input = document.querySelector('[data-dictionary-suggestion-input]');
            const panel = document.querySelector('[data-dictionary-suggestions]');
            const list = document.querySelector('[data-dictionary-suggestion-list]');
            const endpoint = @json(route('dictionary.suggestions'));
            let timeoutId;
            let controller;

            if (!input || !panel || !list) return;

            function hideSuggestions() {
                panel.classList.add('hidden');
                list.replaceChildren();
            }

            function renderSuggestions(suggestions) {
                list.replaceChildren();

                if (!suggestions.length) {
                    hideSuggestions();
                    return;
                }

                suggestions.forEach((suggestion) => {
                    const item = document.createElement('div');
                    item.className = 'rounded-md border border-emerald-100 bg-white p-3 shadow-sm';

                    const title = document.createElement('div');
                    title.className = 'break-words font-bold text-stone-950';
                    title.textContent = suggestion.headword;
                    item.appendChild(title);

                    if (suggestion.matched_alias) {
                        const alias = document.createElement('div');
                        alias.className = 'mt-1 text-xs text-emerald-800';
                        alias.textContent = `{{ __('app.matched_alias') }}: ${suggestion.matched_alias}`;
                        item.appendChild(alias);
                    }

                    const meaning = document.createElement('p');
                    meaning.className = 'mt-1 line-clamp-3 break-words text-sm leading-6 text-stone-700';
                    meaning.textContent = suggestion.meaning;
                    item.appendChild(meaning);

                    list.appendChild(item);
                });

                panel.classList.remove('hidden');
            }

            async function fetchSuggestions(query) {
                controller?.abort();
                controller = new AbortController();

                try {
                    const response = await fetch(`${endpoint}?q=${encodeURIComponent(query)}`, {
                        headers: {'Accept': 'application/json'},
                        signal: controller.signal,
                    });

                    if (!response.ok) {
                        hideSuggestions();
                        return;
                    }

                    const data = await response.json();
                    renderSuggestions(data.suggestions || []);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        hideSuggestions();
                    }
                }
            }

            input.addEventListener('input', () => {
                const query = input.value.trim();
                clearTimeout(timeoutId);

                if (query.length < 2) {
                    controller?.abort();
                    hideSuggestions();
                    return;
                }

                timeoutId = setTimeout(() => fetchSuggestions(query), 250);
            });

            if (input.value.trim().length >= 2) {
                fetchSuggestions(input.value.trim());
            }
        })();
    </script>
</x-layout>
