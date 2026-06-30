<x-layout :title="__('app.rules')">
    <section class="mx-auto max-w-3xl">
        <h1 class="text-2xl font-black">{{ __('app.rules') }}</h1>
        <div class="mt-5 space-y-4 rounded-lg border border-stone-200 bg-white p-5 leading-7">
            <p>{{ __('app.rules_intro') }}</p>
            <ul class="list-disc space-y-2 pl-5">
                <li>{{ __('app.rule_language') }}</li>
                <li>{{ __('app.rule_respect') }}</li>
                <li>{{ __('app.rule_no_harm') }}</li>
                <li>{{ __('app.rule_no_spam') }}</li>
                <li>{{ __('app.rule_transparency') }}</li>
            </ul>
            <a href="{{ route('export.json') }}" class="inline-block rounded-md bg-emerald-700 px-4 py-3 text-sm font-bold text-white">{{ __('app.export_data') }}</a>
        </div>
    </section>
</x-layout>
