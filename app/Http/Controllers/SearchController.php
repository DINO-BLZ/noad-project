<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Throwable;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = trim((string) $request->input('q', ''));

        if ($query === '') {
            $products = Product::query()->whereRaw('0 = 1')->paginate(12);
        } else {
            try {
                $products = Product::search($query)->paginate(12)->withQueryString();
            } catch (Throwable $exception) {
                report($exception);
                $products = $this->databaseSearch($query)->paginate(12)->withQueryString();
            }
        }

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

        try {
            $products = Product::search($query)->take(5)->get();
        } catch (Throwable $exception) {
            report($exception);
            $products = $this->databaseSearch($query)->take(5)->get();
        }

        return response()->json(
            $products->map(fn ($product) => [
                'name' => $product->name,
                'price' => number_format($product->price, 0).' DA',
                'url' => route('products.show', $product->slug),
                'image' => $product->image ? asset('storage/'.$product->image) : null,
            ])
        );
    }

    private function databaseSearch(string $query)
    {
        $term = '%'.$query.'%';

        return Product::query()->where(function ($builder) use ($term) {
            $builder->where('name', 'LIKE', $term)
                ->orWhere('description', 'LIKE', $term)
                ->orWhereHas('category', fn ($category) => $category->where('name', 'LIKE', $term));
        });
    }
}
