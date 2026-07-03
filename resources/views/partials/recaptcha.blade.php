@if (config('services.recaptcha.site_key'))
    <input type="hidden" name="g-recaptcha-response" data-recaptcha-response>
    <input type="hidden" name="g-recaptcha-action" value="{{ $action }}">
    <p class="text-xs leading-5 text-stone-500">{{ __('app.recaptcha_notice') }}</p>

    @once
        <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    @endonce

    <script>
        (() => {
            const script = document.currentScript;
            const form = script.closest('form');
            if (!form) return;

            form.addEventListener('submit', (event) => {
                if (form.dataset.recaptchaSubmitted === 'true') return;

                event.preventDefault();

                grecaptcha.ready(() => {
                    grecaptcha.execute(@json(config('services.recaptcha.site_key')), {action: @json($action)})
                        .then((token) => {
                            form.querySelector('[data-recaptcha-response]').value = token;
                            form.dataset.recaptchaSubmitted = 'true';
                            form.submit();
                        });
                });
            });
        })();
    </script>
@endif
