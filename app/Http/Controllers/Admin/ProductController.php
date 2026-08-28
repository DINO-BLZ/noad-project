<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Products\CreateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function create()
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.create', [
            'categories' => $categories,
        ]);
    }

    public function store(
        StoreProductRequest $request,
        CreateProductAction $createProduct
    ) {
        $product = $createProduct->execute(
            $request->validated(),
            $request->file('image')
        );

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                "Le produit {$product->name} a été créé."
            );
    }
}