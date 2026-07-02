<?php

namespace App\Http\Controllers;

use App\Models\DictionaryWord;
use Illuminate\Http\Request;

class DictionarySuggestionController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
        ]);

        $suggestions = DictionaryWord::suggestions($validated['q'] ?? '')
            ->map(fn (DictionaryWord $word) => [
                'headword' => $word->headword,
                'meaning' => $word->meaning,
                'matched_alias' => $word->getAttribute('matched_alias'),
            ]);

        return response()->json([
            'suggestions' => $suggestions,
        ]);
    }
}
