<!doctype html>
<html lang="tk">
<head>
    @php
        $siteName = __('app.app_name');
        $pageTitle = $title && $title !== $siteName ? $title.' | '.$siteName : $siteName;
        $metaDescription = trim(strip_tags($description ?: __('app.seo_default_description')));
        $canonicalPath = parse_url($canonical ?: url()->current(), PHP_URL_PATH) ?: '/';
        $canonicalUrl = rtrim(config('services.seo.canonical_origin'), '/').'/'.ltrim($canonicalPath, '/');
        $socialImage = $ogImage ?: config('services.seo.og_image_url');
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta name="robots" content="{{ $noindex ? 'noindex, nofollow' : 'index, follow' }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:locale" content="tk_TM">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($socialImage)
        <meta property="og:image" content="{{ $socialImage }}">
        <meta property="og:image:width" content="1200">
        <meta property="og:image:height" content="630">
        @if ($ogImageAlt)
            <meta property="og:image:alt" content="{{ $ogImageAlt }}">
        @endif
    @endif
    <meta name="twitter:card" content="{{ $socialImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if ($socialImage)
        <meta name="twitter:image" content="{{ $socialImage }}">
        @if ($ogImageAlt)
            <meta name="twitter:image:alt" content="{{ $ogImageAlt }}">
        @endif
    @endif
    @if (config('services.google_analytics.id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', @json(config('services.google_analytics.id')));
        </script>
    @endif
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-stone-50 text-stone-950">
    <div class="min-h-screen">
        <header class="sticky top-0 z-20 border-b border-stone-200 bg-white/95 backdrop-blur">
            <nav class="mx-auto flex max-w-5xl flex-wrap items-center justify-between gap-3 px-4 py-5">
                <a href="{{ route('home') }}" class="min-w-0">
                    <span class="block text-lg font-black tracking-tight">{{ __('app.app_name') }}</span>
                    <span class="hidden text-xs text-stone-500 sm:block">{{ __('app.tagline') }}</span>
                </a>
                <div class="flex flex-wrap items-center gap-2 text-sm">
                    <a class="rounded-md px-2 py-2 font-medium text-stone-700 hover:bg-stone-100" href="{{ route('leaderboard') }}">{{ __('app.leaderboard') }}</a>
                    <a class="rounded-md px-2 py-2 font-medium text-stone-700 hover:bg-stone-100" href="{{ route('roadmap') }}">{{ __('app.roadmap') }}</a>
                    <a class="rounded-md px-2 py-2 font-medium text-stone-700 hover:bg-stone-100" href="{{ route('governance.rules') }}">{{ __('app.rules') }}</a>
                    <a class="rounded-md px-2 py-2 font-medium text-stone-700 hover:bg-stone-100" href="{{ route('governance.log') }}">{{ __('app.moderation_log') }}</a>
                    @auth
                        @if (auth()->user()->canReviewAnonymousSubmissions())
                            <a class="rounded-md px-2 py-2 font-medium text-stone-700 hover:bg-stone-100" href="{{ route('moderation.anonymous-submissions') }}">{{ __('app.review_anonymous') }}</a>
                        @endif
                        <a class="rounded-md bg-emerald-700 px-3 py-2 font-semibold text-white hover:bg-emerald-800" href="{{ route('entries.create') }}">{{ __('app.suggest') }}</a>
                        <form method="post" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-md px-2 py-2 font-medium text-stone-700 hover:bg-stone-100">{{ __('app.logout') }}</button>
                        </form>
                    @else
                        <a class="rounded-md bg-emerald-700 px-3 py-2 font-semibold text-white hover:bg-emerald-800" href="{{ route('entries.create') }}">{{ __('app.suggest') }}</a>
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
                <div id="dictionary-meaning" class="dictionary-meaning mt-1 text-sm leading-6 text-stone-700"></div>
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

        function sanitizeDictionaryHtml(html) {
            const allowedTags = new Set(['B', 'BR', 'DIV', 'EM', 'I', 'LI', 'OL', 'P', 'SPAN', 'STRONG', 'UL']);
            const template = document.createElement('template');
            template.innerHTML = html || '';

            template.content.querySelectorAll('*').forEach((element) => {
                if (!allowedTags.has(element.tagName)) {
                    element.replaceWith(...element.childNodes);
                    return;
                }

                [...element.attributes].forEach((attribute) => element.removeAttribute(attribute.name));
            });

            return template.innerHTML;
        }

        async function lookup(word) {
            if (!word) return;
            popover.classList.remove('hidden');
            popoverWord.textContent = word;
            popoverMeaning.textContent = '...';

            try {
                const response = await fetch(`/dictionary/lookup?word=${encodeURIComponent(word)}`, {
                    headers: {'Accept': 'application/json'},
                });
                const data = await response.json();
                popoverWord.textContent = data.headword || word;
                if (data.meaning) {
                    popoverMeaning.innerHTML = sanitizeDictionaryHtml(data.meaning);
                } else {
                    popoverMeaning.textContent = data.message || '';
                }
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
