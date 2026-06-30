<?php

namespace App\Http\Controllers;

use App\Models\DictionaryWord;
use App\Models\Entry;

class ExportController extends Controller
{
    public function __invoke()
    {
        return response()->json([
            'generated_at' => now()->toISOString(),
            'license_note' => 'Community export for preservation and forkability.',
            'entries' => Entry::query()
                ->visible()
                ->with(['visibleDefinitions' => fn ($query) => $query->orderByDesc('votes_count')])
                ->orderBy('term')
                ->get()
                ->map(fn (Entry $entry) => [
                    'term' => $entry->term,
                    'slug' => $entry->slug,
                    'definitions' => $entry->visibleDefinitions->map(fn ($definition) => [
                        'meaning' => $definition->meaning,
                        'example' => $definition->example,
                        'votes_count' => $definition->votes_count,
                    ])->values(),
                ]),
            'dictionary_words' => DictionaryWord::query()
                ->orderBy('headword')
                ->get(['headword', 'meaning'])
                ->map(fn (DictionaryWord $word) => [
                    'headword' => $word->headword,
                    'meaning' => $word->meaning,
                ]),
        ]);
    }
}
