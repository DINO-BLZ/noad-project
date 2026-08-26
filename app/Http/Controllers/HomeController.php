<?php

namespace App\Http\Controllers;

use App\Models\Drop;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $activeDrop = Drop::active()->latest('start_date')->first();

        $upcomingDrop = $activeDrop
            ? null
            : Drop::upcoming()->orderBy('start_date')->first();

        $newProducts = Product::with('category')
            ->latest()
            ->take(4)
            ->get();

        return view('home', compact('activeDrop', 'upcomingDrop', 'newProducts'));
    }
}
