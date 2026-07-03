<?php

namespace App\Services;

use App\Models\Entry;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

class EntrySocialImageGenerator
{
    private const WIDTH = 1200;
    private const HEIGHT = 630;

    public function generate(?Entry $entry): void
    {
        $this->generateImage($entry, false);
    }

    public function generateOrFail(?Entry $entry): void
    {
        $this->generateImage($entry, true);
    }

    private function generateImage(?Entry $entry, bool $throw): void
    {
        if (! $entry || $entry->is_hidden) {
            return;
        }

        $definition = $entry->bestVisibleDefinition();

        if (! $definition) {
            return;
        }

        if (! function_exists('imagecreatetruecolor')) {
            $entry->forceFill([
                'og_image_error' => 'The GD extension is not installed.',
            ])->save();

            return;
        }

        try {
            $font = $this->fontPath();
            $path = 'og/entries/'.$entry->slug.'.png';
            $absolutePath = public_path($path);

            if (! is_dir(dirname($absolutePath))) {
                mkdir(dirname($absolutePath), 0755, true);
            }

            $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
            imagealphablending($image, true);
            imagesavealpha($image, true);

            $this->drawBackground($image);
            $this->fillRoundedRectangle($image, 626, 70, 452, 490, 24, '#f6f1df');

            $cream = $this->color($image, '#fff7e6');
            $gold = $this->color($image, '#f2b84b');
            $ink = $this->color($image, '#17201d');
            $soft = $this->color($image, '#61706b');

            $siteName = __('app.app_name');
            $term = Str::limit($entry->term, 34, '...');
            $meaning = 'Manysy: '.Str::limit(trim(strip_tags($definition->meaning)), 190, '...');

            imagettftext($image, 30, 0, 84, 114, $gold, $font, $siteName);
            $this->drawWrapped($image, $term, $font, 88, $cream, 80, 226, 480, 98, 2);
            imagefilledrectangle($image, 90, 350, 340, 357, $gold);
            $this->drawWrapped($image, __('app.og_card_tagline'), $font, 30, $cream, 84, 392, 450, 44, 3);

            imagettftext($image, 48, 0, 682, 168, $soft, $font, __('app.og_card_label'));
            $this->drawWrapped($image, $meaning, $font, 36, $ink, 682, 218, 330, 52, 5);
            imagettftext($image, 24, 0, 682, 526, $soft, $font, Str::lower($siteName));

            imagepng($image, $absolutePath);
            imagedestroy($image);

            $entry->forceFill([
                'og_image_path' => $path,
                'og_image_generated_at' => now(),
                'og_image_error' => null,
            ])->save();
        } catch (Throwable $exception) {
            $entry->forceFill([
                'og_image_error' => Str::limit($exception->getMessage(), 1000, '...'),
            ])->save();

            if ($throw) {
                throw $exception;
            }
        }
    }

    private function fillBackground($image): void
    {
        for ($x = 0; $x < self::WIDTH; $x++) {
            $ratio = $x / self::WIDTH;
            $r = (int) round(16 + ((24 - 16) * $ratio));
            $g = (int) round(35 + ((71 - 35) * $ratio));
            $b = (int) round(31 + ((63 - 31) * $ratio));
            imageline($image, $x, 0, $x, self::HEIGHT, imagecolorallocate($image, $r, $g, $b));
        }
    }

    private function fillRoundedRectangle($image, int $x, int $y, int $width, int $height, int $radius, string $hex): void
    {
        $color = $this->color($image, $hex);

        imagefilledrectangle($image, $x + $radius, $y, $x + $width - $radius, $y + $height, $color);
        imagefilledrectangle($image, $x, $y + $radius, $x + $width, $y + $height - $radius, $color);

        imagefilledellipse($image, $x + $radius, $y + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x + $width - $radius, $y + $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x + $radius, $y + $height - $radius, $radius * 2, $radius * 2, $color);
        imagefilledellipse($image, $x + $width - $radius, $y + $height - $radius, $radius * 2, $radius * 2, $color);
    }

    private function drawWrapped($image, string $text, string $font, int $size, int $color, int $x, int $y, int $maxWidth, int $lineHeight, int $maxLines): void
    {
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/u', $text) as $word) {
            $candidate = trim($line.' '.$word);

            if ($this->textWidth($candidate, $font, $size) <= $maxWidth) {
                $line = $candidate;
                continue;
            }

            if ($line !== '') {
                $lines[] = $line;
            }

            $line = $word;
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        $visibleLines = array_slice($lines, 0, $maxLines);

        if (count($lines) > $maxLines) {
            $visibleLines[$maxLines - 1] = rtrim($visibleLines[$maxLines - 1], '.').'...';
        }

        foreach ($visibleLines as $index => $visibleLine) {
            imagettftext($image, $size, 0, $x, $y + ($index * $lineHeight), $color, $font, $visibleLine);
        }
    }

    private function textWidth(string $text, string $font, int $size): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return abs($box[2] - $box[0]);
    }

    private function color($image, string $hex): int
    {
        $hex = ltrim($hex, '#');

        return imagecolorallocate(
            $image,
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2))
        );
    }

    private function fontPath(): string
    {
        $paths = array_filter([
            config('services.seo.og_image_font_path'),
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
            '/usr/share/fonts/dejavu/DejaVuSans.ttf',
            'C:\\Windows\\Fonts\\segoeui.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
        ]);

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        throw new RuntimeException('No TrueType font found for social image generation.');
    }

    private function drawBackground($image): void
    {
        $background = $this->loadBackgroundImage();

        if (! $background) {
            $this->fillBackground($image);

            return;
        }

        $sourceWidth = imagesx($background);
        $sourceHeight = imagesy($background);
        $sourceRatio = $sourceWidth / $sourceHeight;
        $targetRatio = self::WIDTH / self::HEIGHT;

        if ($sourceRatio > $targetRatio) {
            $cropHeight = $sourceHeight;
            $cropWidth = (int) round($sourceHeight * $targetRatio);
            $cropX = (int) round(($sourceWidth - $cropWidth) / 2);
            $cropY = 0;
        } else {
            $cropWidth = $sourceWidth;
            $cropHeight = (int) round($sourceWidth / $targetRatio);
            $cropX = 0;
            $cropY = (int) round(($sourceHeight - $cropHeight) / 2);
        }

        imagecopyresampled(
            $image,
            $background,
            0,
            0,
            $cropX,
            $cropY,
            self::WIDTH,
            self::HEIGHT,
            $cropWidth,
            $cropHeight
        );
        imagedestroy($background);

        imagefilledrectangle($image, 0, 0, self::WIDTH, self::HEIGHT, imagecolorallocatealpha($image, 12, 35, 30, 35));
        imagefilledrectangle($image, 0, 0, 570, self::HEIGHT, imagecolorallocatealpha($image, 4, 32, 28, 34));
    }

    private function loadBackgroundImage()
    {
        $path = config('services.seo.og_image_background_path') ?: public_path('images/og-card-background.png');
        $path = $this->absolutePath($path);

        if (! is_file($path)) {
            return null;
        }

        return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : null,
            default => null,
        };
    }

    private function absolutePath(string $path): string
    {
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\\\\/', $path)) {
            return $path;
        }

        return public_path($path);
    }
}
