<?php

namespace App\Http\Controllers;

use App\Models\User;

class LeaderboardController extends Controller
{
    public function __invoke()
    {
        $users = User::query()
            ->withCount([
                'entries' => fn ($query) => $query->where('is_hidden', false),
                'definitions' => fn ($query) => $query->where('is_hidden', false),
            ])
            ->withSum(['definitions as received_votes_sum' => fn ($query) => $query->where('is_hidden', false)], 'votes_count')
            ->orderByDesc('definitions_count')
            ->orderByDesc('received_votes_sum')
            ->orderBy('name')
            ->limit(50)
            ->get();

        return view('leaderboard', ['users' => $users]);
    }
}
