<?php

namespace App\Console\Commands;

use App\Models\Entry;
use App\Services\EntrySocialImageGenerator;
use Illuminate\Console\Command;

class RegenerateEntrySocialImages extends Command
{
    protected $signature = 'entries:regenerate-social-images
        {--missing : Only generate images for entries without an image path}
        {--chunk=100 : Number of entries to process per batch}';

    protected $description = 'Regenerate social sharing images for visible dictionary entries.';

    public function handle(EntrySocialImageGenerator $generator): int
    {
        $chunkSize = max(1, (int) $this->option('chunk'));
        $processed = 0;
        $skipped = 0;

        Entry::query()
            ->visible()
            ->whereHas('visibleDefinitions')
            ->when($this->option('missing'), fn ($query) => $query->whereNull('og_image_path'))
            ->orderBy('id')
            ->chunkById($chunkSize, function ($entries) use ($generator, &$processed, &$skipped): void {
                foreach ($entries as $entry) {
                    $generator->generate($entry);
                    $entry->refresh();

                    if ($entry->og_image_path) {
                        $processed++;
                    } else {
                        $skipped++;
                    }
                }
            });

        $this->info("Social image regeneration complete. Generated or refreshed: {$processed}. Skipped: {$skipped}.");

        return self::SUCCESS;
    }
}
