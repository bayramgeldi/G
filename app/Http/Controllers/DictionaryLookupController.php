<?php

namespace App\Http\Controllers;

use App\Models\DictionaryWord;
use Illuminate\Http\Request;

class DictionaryLookupController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'word' => ['required', 'string', 'max:120'],
        ]);

        $word = DictionaryWord::lookup($validated['word']);

        if (! $word) {
            return response()->json([
                'found' => false,
                'word' => $validated['word'],
                'message' => __('app.dictionary_not_found'),
            ], 404);
        }

        return response()->json([
            'found' => true,
            'headword' => $word->headword,
            'meaning' => $word->meaning,
        ]);
    }
}
