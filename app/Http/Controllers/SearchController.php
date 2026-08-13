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

    /**
     * Endpoint JSON pour la barre de recherche en direct (AJAX).
     * Renvoie seulement les 5 premiers résultats, avec le minimum de données
     * nécessaires pour afficher un menu déroulant de suggestions.
     */
    public function suggestions(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        if ($query === '') {
            return response()->json([]);
        }

        $products = Product::search($query)->take(5)->get();

        return response()->json(
            $products->map(fn ($product) => [
                'name' => $product->name,
                'price' => number_format($product->price, 0) . ' DA',
                'url' => route('products.show', $product->slug),
                'image' => $product->image ? asset('storage/' . $product->image) : null,
            ])
        );
    }
}