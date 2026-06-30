<?php

namespace App\Support;

final class NormalizesTurkmenText
{
    public static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value), 'UTF-8');
        $value = str_replace(['’', "'", '`', '´'], '', $value);
        $value = preg_replace('/[^\p{L}\p{N}\s-]+/u', ' ', $value) ?? $value;
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return trim($value);
    }

    public static function slug(string $value): string
    {
        $normalized = self::normalize($value);
        $ascii = strtr($normalized, [
            'ä' => 'a',
            'ç' => 'c',
            'ň' => 'n',
            'ö' => 'o',
            'ş' => 's',
            'ü' => 'u',
            'ý' => 'y',
            'ž' => 'z',
        ]);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $ascii) ?? $ascii;

        return trim($slug, '-') ?: 'soz';
    }
}
