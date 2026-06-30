<?php

namespace App\Console\Commands;

use App\Models\DictionaryAlias;
use App\Models\DictionaryWord;
use App\Support\NormalizesTurkmenText;
use Illuminate\Console\Command;

class AddDictionaryAlias extends Command
{
    protected $signature = 'dictionary:add-alias {headword} {alias}';

    protected $description = 'Add a manual dictionary alias for an inflected or alternate word form.';

    public function handle(): int
    {
        $headword = DictionaryWord::where('normalized_headword', NormalizesTurkmenText::normalize((string) $this->argument('headword')))->first();

        if (! $headword) {
            $this->error('Headword not found. Import the dictionary word before adding aliases.');

            return self::FAILURE;
        }

        DictionaryAlias::updateOrCreate(
            ['normalized_alias' => NormalizesTurkmenText::normalize((string) $this->argument('alias'))],
            [
                'dictionary_word_id' => $headword->id,
                'alias' => (string) $this->argument('alias'),
            ]
        );

        $this->info('Alias saved.');

        return self::SUCCESS;
    }
}
