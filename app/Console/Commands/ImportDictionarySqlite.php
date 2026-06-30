<?php

namespace App\Console\Commands;

use App\Models\DictionaryWord;
use App\Support\NormalizesTurkmenText;
use Illuminate\Console\Command;
use PDO;
use RuntimeException;

class ImportDictionarySqlite extends Command
{
    protected $signature = 'dictionary:import-sqlite
        {path : Path to the SQLite dictionary file}
        {--table= : Source table name}
        {--word= : Source word/headword column}
        {--meaning= : Source meaning column}
        {--source=sqlite : Source label stored with imported rows}
        {--inspect : Only inspect tables and columns; do not import}';

    protected $description = 'Import a Türkmen dictionary SQLite file into PostgreSQL dictionary tables.';

    public function handle(): int
    {
        $path = (string) $this->argument('path');

        if (! is_file($path)) {
            $this->error("SQLite file not found: {$path}");

            return self::FAILURE;
        }

        $pdo = new PDO('sqlite:'.$path);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $tables = $this->tables($pdo);

        if ($this->option('inspect')) {
            foreach ($tables as $table) {
                $this->line($table.': '.implode(', ', $this->columns($pdo, $table)));
            }

            return self::SUCCESS;
        }

        $table = $this->option('table') ?: config('dictionary.sqlite.table');
        $wordColumn = $this->option('word') ?: config('dictionary.sqlite.word_column');
        $meaningColumn = $this->option('meaning') ?: config('dictionary.sqlite.meaning_column');

        if (! $table || ! in_array($table, $tables, true)) {
            $this->error('Provide a valid source table with --table= or DICTIONARY_SQLITE_TABLE.');

            return self::FAILURE;
        }

        $columns = $this->columns($pdo, $table);
        foreach ([$wordColumn, $meaningColumn] as $column) {
            if (! in_array($column, $columns, true)) {
                $this->error("Column '{$column}' was not found in table '{$table}'. Run with --inspect to see available columns.");

                return self::FAILURE;
            }
        }

        $quotedTable = $this->quoteIdentifier($table);
        $quotedWord = $this->quoteIdentifier($wordColumn);
        $quotedMeaning = $this->quoteIdentifier($meaningColumn);
        $statement = $pdo->query("select {$quotedWord} as word, {$quotedMeaning} as meaning from {$quotedTable}");

        $count = 0;
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $word = trim((string) ($row['word'] ?? ''));
            $meaning = trim((string) ($row['meaning'] ?? ''));

            if ($word === '' || $meaning === '') {
                continue;
            }

            DictionaryWord::updateOrCreate(
                ['normalized_headword' => NormalizesTurkmenText::normalize($word)],
                [
                    'headword' => $word,
                    'meaning' => $meaning,
                    'source' => (string) $this->option('source'),
                ]
            );
            $count++;
        }

        $this->info("Imported or updated {$count} dictionary words.");

        return self::SUCCESS;
    }

    private function tables(PDO $pdo): array
    {
        $rows = $pdo->query("select name from sqlite_master where type = 'table' and name not like 'sqlite_%' order by name")
            ->fetchAll(PDO::FETCH_COLUMN);

        return array_map('strval', $rows);
    }

    private function columns(PDO $pdo, string $table): array
    {
        $statement = $pdo->query('pragma table_info('.$this->quoteIdentifier($table).')');
        $columns = [];

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $columns[] = (string) $row['name'];
        }

        return $columns;
    }

    private function quoteIdentifier(string $identifier): string
    {
        if ($identifier === '') {
            throw new RuntimeException('Identifier cannot be empty.');
        }

        return '"'.str_replace('"', '""', $identifier).'"';
    }
}
