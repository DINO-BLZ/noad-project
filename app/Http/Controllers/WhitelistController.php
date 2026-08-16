<?php

namespace App\Http\Controllers;

class WhitelistController extends Controller
{
    public function index()
    {
        $whitelists = auth()->user()
            ->dropWhitelists()
            ->with('drop')
            ->latest()
            ->get();

        return view('whitelist.index', compact('whitelists'));
    }
}