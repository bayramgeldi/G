<?php

return [
    'google_analytics' => [
        'id' => env('GOOGLE_ANALYTICS_ID'),
    ],

    'recaptcha' => [
        'site_key' => env('RECAPTCHA_SITE_KEY'),
        'secret_key' => env('RECAPTCHA_SECRET_KEY'),
        'min_score' => (float) env('RECAPTCHA_MIN_SCORE', 0.5),
    ],

    'seo' => [
        'canonical_origin' => env('SEO_CANONICAL_ORIGIN', 'https://gmss.armyt.co'),
        'og_image_url' => env('SEO_OG_IMAGE_URL'),
        'og_image_font_path' => env('SEO_OG_IMAGE_FONT_PATH'),
        'og_image_background_path' => env('SEO_OG_IMAGE_BACKGROUND_PATH'),
    ],
];
