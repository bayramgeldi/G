<!doctype html>
<html lang="tk">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('app.app_name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-950">
    <div class="min-h-screen">
        <header class="sticky top-0 z-20 border-b border-stone-200 bg-white/95 backdrop-blur">
            <nav class="mx-auto flex max-w-5xl items-center justify-between gap-3 px-4 py-3">
                <a href="{{ route('home') }}" class="min-w-0">
                    <span class="block text-lg font-black tracking-tight">{{ __('app.app_name') }}</span>
                    <span class="hidden text-xs text-stone-500 sm:block">{{ __('app.tagline') }}</span>
                </a>
                <div class="flex shrink-0 items-center gap-2 text-sm">
                    <a class="rounded-md px-2 py-2 font-medium text-stone-700 hover:bg-stone-100" href="{{ route('leaderboard') }}">{{ __('app.leaderboard') }}</a>
                    @auth
                        <a class="rounded-md bg-emerald-700 px-3 py-2 font-semibold text-white hover:bg-emerald-800" href="{{ route('entries.create') }}">{{ __('app.suggest') }}</a>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-md px-2 py-2 font-medium text-stone-700 hover:bg-stone-100">{{ __('app.logout') }}</button>
                        </form>
                    @else
                        <a class="rounded-md px-2 py-2 font-medium text-stone-700 hover:bg-stone-100" href="{{ route('login') }}">{{ __('app.login') }}</a>
                        <a class="rounded-md bg-emerald-700 px-3 py-2 font-semibold text-white hover:bg-emerald-800" href="{{ route('register') }}">{{ __('app.register') }}</a>
                    @endauth
                </div>
            </nav>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-5 sm:py-8">
            @if (session('status'))
                <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-4 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900">
                    {{ $errors->first() }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    <div id="dictionary-popover" class="fixed inset-x-3 bottom-3 z-50 hidden rounded-lg border border-stone-200 bg-white p-4 shadow-2xl sm:inset-x-auto sm:right-5 sm:max-w-sm">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div id="dictionary-word" class="font-bold text-stone-950"></div>
                <div id="dictionary-meaning" class="mt-1 text-sm leading-6 text-stone-700"></div>
            </div>
            <button type="button" data-close-dictionary class="rounded-md px-2 py-1 text-xl leading-none text-stone-500 hover:bg-stone-100" aria-label="Close">&times;</button>
        </div>
    </div>

    <script>
        const popover = document.getElementById('dictionary-popover');
        const popoverWord = document.getElementById('dictionary-word');
        const popoverMeaning = document.getElementById('dictionary-meaning');

        function wordAtPoint(event) {
            const range = document.caretRangeFromPoint
                ? document.caretRangeFromPoint(event.clientX, event.clientY)
                : null;

            const node = range?.startContainer;
            if (!node || node.nodeType !== Node.TEXT_NODE) return '';

            const text = node.textContent;
            let start = range.startOffset;
            let end = range.startOffset;
            const matcher = /[\p{L}\p{N}'’`´-]/u;

            while (start > 0 && matcher.test(text[start - 1])) start--;
            while (end < text.length && matcher.test(text[end])) end++;

            return text.slice(start, end).trim();
        }

        async function lookup(word) {
            if (!word) return;
            popover.classList.remove('hidden');
            popoverWord.textContent = word;
            popoverMeaning.textContent = '...';

            try {
                const response = await fetch(`{{ route('dictionary.lookup') }}?word=${encodeURIComponent(word)}`, {
                    headers: {'Accept': 'application/json'},
                });
                const data = await response.json();
                popoverWord.textContent = data.headword || word;
                popoverMeaning.textContent = data.meaning || data.message;
            } catch (error) {
                popoverMeaning.textContent = '{{ __('app.dictionary_not_found') }}';
            }
        }

        document.querySelectorAll('[data-lookup-text]').forEach((element) => {
            element.addEventListener('click', (event) => lookup(wordAtPoint(event)));
        });

        document.querySelectorAll('[data-close-dictionary]').forEach((button) => {
            button.addEventListener('click', () => popover.classList.add('hidden'));
        });
    </script>
</body>
</html>
