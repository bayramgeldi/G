<x-layout :title="__('app.moderation_log')">
    <div class="mb-5 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-black">{{ __('app.moderation_log') }}</h1>
        <a href="{{ route('governance.rules') }}" class="text-sm font-bold text-emerald-800">{{ __('app.rules') }}</a>
    </div>

    <div class="space-y-3">
        @forelse ($events as $event)
            <article class="rounded-lg border border-stone-200 bg-white p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-black">{{ __('app.event_'.$event->event_type) }}</div>
                        <div class="mt-1 text-sm text-stone-600">
                            {{ __('app.actor_'.$event->actor_type) }}
                            @if ($event->actor)
                                · {{ $event->actor->name }}
                            @endif
                            · {{ class_basename($event->subject_type) }} #{{ $event->subject_id }}
                        </div>
                    </div>
                    <time class="shrink-0 text-xs text-stone-500">{{ $event->created_at->diffForHumans() }}</time>
                </div>
                @if ($event->reason)
                    <div class="mt-2 text-sm text-stone-700">{{ __('app.reason') }}: {{ __('app.report_reason_'.$event->reason) }}</div>
                @endif
                @if ($event->details)
                    <pre class="mt-3 overflow-x-auto rounded-md bg-stone-100 p-3 text-xs">{{ json_encode($event->details, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                @endif
            </article>
        @empty
            <div class="rounded-lg border border-dashed border-stone-300 bg-white p-8 text-center text-stone-600">{{ __('app.no_moderation_events') }}</div>
        @endforelse
    </div>

    <div class="mt-6">{{ $events->links() }}</div>
</x-layout>
