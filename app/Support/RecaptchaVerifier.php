<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

final class RecaptchaVerifier
{
    public static function verify(Request $request, string $expectedAction): void
    {
        $secretKey = config('services.recaptcha.secret_key');

        if (! $secretKey) {
            return;
        }

        $token = $request->string('g-recaptcha-response')->toString();

        if (! $token) {
            throw self::failed();
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secretKey,
            'response' => $token,
            'remoteip' => $request->ip(),
        ]);

        if (! $response->ok()) {
            throw self::failed();
        }

        $result = $response->json();

        if (
            ! ($result['success'] ?? false)
            || ($result['action'] ?? null) !== $expectedAction
            || (float) ($result['score'] ?? 0) < (float) config('services.recaptcha.min_score', 0.5)
        ) {
            throw self::failed();
        }
    }

    private static function failed(): ValidationException
    {
        return ValidationException::withMessages([
            'recaptcha' => __('app.recaptcha_failed'),
        ]);
    }
}
