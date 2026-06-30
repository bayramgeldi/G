<?php

namespace Tests\Feature;

use App\Models\DictionaryWord;
use Database\Seeders\TurkmenDictionarySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TurkmenDictionarySeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_imports_committed_sqlite_dictionary(): void
    {
        $path = config('dictionary.sqlite.path');

        if (! is_file($path)) {
            $this->markTestSkipped("Dictionary SQLite file not found: {$path}");
        }

        $this->seed(TurkmenDictionarySeeder::class);

        $this->assertGreaterThan(0, DictionaryWord::count());
        $this->assertNotNull(DictionaryWord::lookup('a'));
    }
}
