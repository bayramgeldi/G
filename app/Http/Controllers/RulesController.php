<?php

namespace App\Http\Controllers;

class RulesController extends Controller
{
    public function __invoke()
    {
        return view('governance.rules');
    }
}
