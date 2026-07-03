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
            $wordFont = $this->wordFontPath();
            $path = 'og/entries/'.$entry->slug.'.png';
            $absolutePath = public_path($path);

            if (! is_dir(dirname($absolutePath))) {
                mkdir(dirname($absolutePath), 0755, true);
            }

            $image = imagecreatetruecolor(self::WIDTH, self::HEIGHT);
            imagealphablending($image, true);
            imagesavealpha($image, true);

            $this->drawBackground($image);
            $this->fillRoundedRectangle($image, 590, 76, 520, 478, 20, '#f6f1df');

            $cream = $this->color($image, '#fff7e6');
            $gold = $this->color($image, '#f2b84b');
            $ink = $this->color($image, '#17201d');
            $soft = $this->color($image, '#61706b');
            $line = $this->color($image, '#d8c99f');

            $siteName = __('app.app_name');
            $term = Str::limit($entry->term, 60, '...');
            $meaning = Str::limit(trim(strip_tags($definition->meaning)), 170, '...');
            $termSize = $this->fitFontSize($term, $wordFont, 78, 40, 440);

            imagettftext($image, 27, 0, 82, 108, $gold, $font, $siteName);
            imagettftext($image, 18, 0, 82, 144, $cream, $font, 'Turkmen slang dictionary');
            $termLineHeight = (int) round($termSize * 1.12);
            $termLines = $this->wrappedLines($term, $wordFont, $termSize, 440, 2);
            $this->drawLines($image, $termLines, $wordFont, $termSize, $cream, 82, 306, $termLineHeight);
            $underlineY = 306 + ((count($termLines) - 1) * $termLineHeight) + 36;
            imagefilledrectangle($image, 86, $underlineY, 230, $underlineY + 5, $gold);

            imagettftext($image, 26, 0, 662, 158, $soft, $font, 'Definition:');
            imagefilledrectangle($image, 662, 184, 1030, 186, $line);
            $this->drawWrapped($image, $meaning, $font, 38, $ink, 662, 268, 360, 54, 4);
            imagettftext($image, 21, 0, 662, 498, $soft, $font, Str::lower($siteName));

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
        $this->drawLines(
            $image,
            $this->wrappedLines($text, $font, $size, $maxWidth, $maxLines),
            $font,
            $size,
            $color,
            $x,
            $y,
            $lineHeight
        );
    }

    private function wrappedLines(string $text, string $font, int $size, int $maxWidth, int $maxLines): array
    {
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/u', $text) as $word) {
            if ($word === '') {
                continue;
            }

            if ($this->textWidth($word, $font, $size) > $maxWidth) {
                if ($line !== '') {
                    $lines[] = $line;
                    $line = '';
                }

                array_push($lines, ...$this->splitLongWord($word, $font, $size, $maxWidth));
                continue;
            }

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

        return $visibleLines;
    }

    private function splitLongWord(string $word, string $font, int $size, int $maxWidth): array
    {
        $chunks = [];
        $chunk = '';

        foreach (preg_split('//u', $word, -1, PREG_SPLIT_NO_EMPTY) as $character) {
            $candidate = $chunk.$character;

            if ($chunk !== '' && $this->textWidth($candidate, $font, $size) > $maxWidth) {
                $chunks[] = $chunk;
                $chunk = $character;

                continue;
            }

            $chunk = $candidate;
        }

        if ($chunk !== '') {
            $chunks[] = $chunk;
        }

        return $chunks;
    }

    private function drawLines($image, array $lines, string $font, int $size, int $color, int $x, int $y, int $lineHeight): void
    {
        foreach ($lines as $index => $line) {
            imagettftext($image, $size, 0, $x, $y + ($index * $lineHeight), $color, $font, $line);
        }
    }

    private function textWidth(string $text, string $font, int $size): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return abs($box[2] - $box[0]);
    }

    private function fitFontSize(string $text, string $font, int $maxSize, int $minSize, int $maxWidth): int
    {
        for ($size = $maxSize; $size >= $minSize; $size -= 2) {
            if ($this->textWidth($text, $font, $size) <= $maxWidth) {
                return $size;
            }
        }

        return $minSize;
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

    private function wordFontPath(): string
    {
        $paths = [
            '/usr/share/fonts/truetype/dejavu/DejaVuSerif.ttf',
            '/usr/share/fonts/dejavu/DejaVuSerif.ttf',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return $this->fontPath();
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

        imagefilledrectangle($image, 0, 0, self::WIDTH, self::HEIGHT, imagecolorallocatealpha($image, 6, 30, 26, 46));
        imagefilledrectangle($image, 0, 0, 555, self::HEIGHT, imagecolorallocatealpha($image, 3, 28, 25, 40));
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
