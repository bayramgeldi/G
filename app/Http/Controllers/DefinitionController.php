<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use Illuminate\Http\Request;

class DefinitionController extends Controller
{
    public function store(Request $request, Entry $entry)
    {
        abort_if($entry->is_hidden, 404);

        $validated = $request->validate([
            'meaning' => ['required', 'string', 'max:2000'],
            'example' => ['nullable', 'string', 'max:2000'],
        ]);

        $entry->definitions()->create([
            'user_id' => $request->user()->id,
            'meaning' => $validated['meaning'],
            'example' => $validated['example'] ?? null,
        ]);

        return redirect()->route('entries.show', $entry)->with('status', __('app.saved'));
    }
}
