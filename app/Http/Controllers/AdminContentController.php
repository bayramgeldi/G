<?php

namespace App\Http\Controllers;

use App\Models\Definition;
use App\Models\Entry;
use App\Support\ModeratesContent;
use Illuminate\Http\Request;

class AdminContentController extends Controller
{
    public function hideEntry(Request $request, Entry $entry)
    {
        ModeratesContent::emergencyHide($entry, $request->user(), 'emergency');

        return redirect()->route('home')->with('status', __('app.hidden'));
    }

    public function hideDefinition(Request $request, Definition $definition)
    {
        ModeratesContent::emergencyHide($definition, $request->user(), 'emergency');

        return back()->with('status', __('app.hidden'));
    }
}
