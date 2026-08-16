<?php

namespace App\Http\Controllers;

use App\Models\Drop;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DropController extends Controller
{
    public function index()
    {
        $drops = Drop::withCount(['products', 'approvedWhitelists'])
            ->orderBy('start_date', 'desc')
            ->get();

        return view('drops.index', compact('drops'));
    }

    public function show(Drop $drop)
    {
        $drop->load(['products.variants', 'approvedWhitelists']);

        /** @var User|null $user */
        $user = Auth::user();
        $isWhitelisted = $user instanceof User && $user->isWhitelistedForDrop($drop);

        return view('drops.show', compact('drop', 'isWhitelisted'));
    }
}