<?php

namespace App\Http\Controllers;

use App\Models\Definition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VoteController extends Controller
{
    public function store(Request $request, Definition $definition)
    {
        abort_if($definition->is_hidden || $definition->entry->is_hidden, 404);

        DB::transaction(function () use ($request, $definition): void {
            $created = $definition->votes()->firstOrCreate([
                'user_id' => $request->user()->id,
            ]);

            if ($created->wasRecentlyCreated) {
                $definition->increment('votes_count');
            }
        });

        return back()->with('status', __('app.voted'));
    }
}
