<x-layout :title="__('app.register')">
    <div class="mx-auto max-w-md">
        <h1 class="text-2xl font-black">{{ __('app.register') }}</h1>
        <form method="post" action="{{ route('register') }}" class="mt-5 space-y-4 rounded-lg border border-stone-200 bg-white p-4">
            @csrf
            <label class="block">
                <span class="text-sm font-bold">{{ __('app.name') }}</span>
                <input name="name" value="{{ old('name') }}" required class="mt-1 w-full rounded-md border border-stone-300 px-3 py-3">
            </label>
            <label class="block">
                <span class="text-sm font-bold">{{ __('app.email') }}</span>
                <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full rounded-md border border-stone-300 px-3 py-3">
            </label>
            <label class="block">
                <span class="text-sm font-bold">{{ __('app.password') }}</span>
                <input type="password" name="password" required class="mt-1 w-full rounded-md border border-stone-300 px-3 py-3">
            </label>
            <label class="block">
                <span class="text-sm font-bold">{{ __('app.confirm_password') }}</span>
                <input type="password" name="password_confirmation" required class="mt-1 w-full rounded-md border border-stone-300 px-3 py-3">
            </label>
            <button class="w-full rounded-md bg-emerald-700 px-4 py-3 font-bold text-white">{{ __('app.register') }}</button>
        </form>
    </div>
</x-layout>
