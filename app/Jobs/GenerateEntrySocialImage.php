<?php

namespace App\Jobs;

use App\Models\Entry;
use App\Services\EntrySocialImageGenerator;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateEntrySocialImage implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function __construct(public Entry $entry)
    {
    }

    public function handle(EntrySocialImageGenerator $generator): void
    {
        $generator->generate($this->entry->fresh());
    }
}
