<?php

namespace App\Http\Controllers;

use App\Models\ModerationEvent;

class ModerationLogController extends Controller
{
    public function __invoke()
    {
        $events = ModerationEvent::with(['actor', 'subject'])
            ->latest()
            ->paginate(30);

        return view('governance.log', ['events' => $events]);
    }
}
