<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();
        $products = Product::with([
            'variants',
            'category',
            'drops' => fn ($query) => $query->active(),
        ])
            ->when($request->category, function ($query, $categorySlug) {
                $query->whereHas('category', fn ($q) => $q->where('slug', $categorySlug));
            })
            ->latest()
            ->paginate(12);

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product)
    {
        $product->load('variants', 'category', 'drops', 'images');

        return view('products.show', compact('product'));
    }
}
