<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PDO;
use Tests\TestCase;

class DictionaryImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sqlite_import_loads_words_and_is_idempotent(): void
    {
        $path = $this->makeDictionarySqlite();

        $this->artisan('dictionary:import-sqlite', [
            'path' => $path,
            '--table' => 'words',
            '--word' => 'headword',
            '--meaning' => 'definition',
        ])->assertExitCode(0);

        $this->artisan('dictionary:import-sqlite', [
            'path' => $path,
            '--table' => 'words',
            '--word' => 'headword',
            '--meaning' => 'definition',
        ])->assertExitCode(0);

        $this->assertDatabaseCount('dictionary_words', 2);
        $this->assertDatabaseHas('dictionary_words', [
            'headword' => 'suw',
            'meaning' => 'Içilýän suwuklyk.',
        ]);
    }

    public function test_import_fails_with_clear_error_for_invalid_mapping(): void
    {
        $path = $this->makeDictionarySqlite();

        $this->artisan('dictionary:import-sqlite', [
            'path' => $path,
            '--table' => 'words',
            '--word' => 'missing',
            '--meaning' => 'definition',
        ])->assertExitCode(1);
    }

    public function test_alias_command_maps_manual_word_forms(): void
    {
        $path = $this->makeDictionarySqlite();
        $this->artisan('dictionary:import-sqlite', [
            'path' => $path,
            '--table' => 'words',
            '--word' => 'headword',
            '--meaning' => 'definition',
        ])->assertExitCode(0);

        $this->artisan('dictionary:add-alias', [
            'headword' => 'suw',
            'alias' => 'suwy',
        ])->assertExitCode(0);

        $this->getJson(route('dictionary.lookup', ['word' => 'suwy']))
            ->assertOk()
            ->assertJsonPath('headword', 'suw');
    }

    private function makeDictionarySqlite(): string
    {
        $path = tempnam(sys_get_temp_dir(), 'dict').'.sqlite';
        $pdo = new PDO('sqlite:'.$path);
        $pdo->exec('create table words (headword text not null, definition text not null)');
        $statement = $pdo->prepare('insert into words (headword, definition) values (?, ?)');
        $statement->execute(['suw', 'Içilýän suwuklyk.']);
        $statement->execute(['ot', 'Ýanýan zat.']);

        return $path;
    }
}
