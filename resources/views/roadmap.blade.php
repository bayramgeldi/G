<x-layout :title="__('app.roadmap')">
    <section class="mx-auto max-w-3xl">
        <div class="mb-6">
            <p class="text-sm font-bold uppercase tracking-wide text-emerald-700">Product roadmap</p>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-stone-950">Roadmap</h1>
            <p class="mt-3 max-w-2xl leading-7 text-stone-700">
                Upcoming versions will make the dictionary easier to verify, cite, and understand across regions.
            </p>
        </div>

        <div class="space-y-4">
            <article class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-black text-stone-950">Next: APA style citation</h2>
                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-emerald-800">Incoming</span>
                </div>
                <p class="mt-3 leading-7 text-stone-700">
                    We will add APA style citation fields so entries and definitions can include references. This will help contributors show where a term, meaning, or usage example comes from.
                </p>
            </article>

            <article class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-black text-stone-950">Later: regional usage map</h2>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-bold uppercase tracking-wide text-amber-800">Planned</span>
                </div>
                <p class="mt-3 leading-7 text-stone-700">
                    We will add a map-based region selector so contributors can mark where a word or phrase is used. The first version will focus on clear regional context before adding deeper map interactions.
                </p>
            </article>

            <article class="rounded-lg border border-stone-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-xl font-black text-stone-950">Goal: trusted context</h2>
                    <span class="rounded-full bg-stone-100 px-3 py-1 text-xs font-bold uppercase tracking-wide text-stone-700">Research</span>
                </div>
                <p class="mt-3 leading-7 text-stone-700">
                    These features are meant to improve trust, source quality, and regional context while keeping the community dictionary simple to browse and contribute to.
                </p>
            </article>
        </div>
    </section>
</x-layout>
