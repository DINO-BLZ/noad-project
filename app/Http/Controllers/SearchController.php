<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        $products = $query !== ''
            ? Product::search($query)->paginate(12)->withQueryString()
            : Product::query()->whereRaw('0 = 1')->paginate(12);

        $products->load(['variants', 'category', 'drops' => fn ($q) => $q->active()]);

        return view('search.index', [
            'products' => $products,
            'query' => $query,
        ]);
    }
}