<?php

namespace Database\Seeders;

use App\Models\DictionaryWord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use RuntimeException;

class TurkmenDictionarySeeder extends Seeder
{
    public function run(): void
    {
        if (DictionaryWord::query()->exists()) {
            $this->command?->info('Dictionary already seeded. Skipping import.');

            return;
        }

        $path = config('dictionary.sqlite.path');

        if (! is_file($path)) {
            throw new RuntimeException("Dictionary SQLite file not found: {$path}");
        }

        $exitCode = Artisan::call('dictionary:import-sqlite', [
            'path' => $path,
            '--table' => config('dictionary.sqlite.table'),
            '--word' => config('dictionary.sqlite.word_column'),
            '--meaning' => config('dictionary.sqlite.meaning_column'),
            '--source' => 'turkmen.sqlite',
        ]);

        if ($exitCode !== 0) {
            throw new RuntimeException(trim(Artisan::output()) ?: 'Dictionary import failed.');
        }

        $this->command?->info(trim(Artisan::output()));
    }
}
