<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);

        $products = Product::query()
            ->with([
                'variants',
                'category',
                'drops' => fn ($query) => $query->active(),
            ])
            ->when(
                $request->filled('category'),
                function ($query) use ($request) {
                    $query->whereHas(
                        'category',
                        fn ($q) => $q->where(
                            'slug',
                            $request->category
                        )
                    );
                }
            )
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view(
            'shop.index',
            compact('products', 'categories')
        );
    }

    public function show(Product $product)
    {
        $product->load([
            'variants',
            'category',
            'drops',
            'images',
        ]);

        return view(
            'products.show',
            compact('product')
        );
    }
}
