<?php

namespace App\Http\Controllers;

use App\Models\Drop;
use Illuminate\Http\Request;

class WhitelistController extends Controller
{
    public function index()
    {
        $applications = auth()->user()
            ->dropWhitelists()
            ->with('drop')
            ->latest()
            ->get();

        return view('whitelist.index', compact('applications'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'drop_id' => ['required', 'integer', 'exists:drops,id'],
        ]);

        $drop = Drop::findOrFail($request->input('drop_id'));

        return app(DropRequestController::class)->store($request, $drop);
    }
}
