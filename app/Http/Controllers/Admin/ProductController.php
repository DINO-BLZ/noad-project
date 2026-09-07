<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Products\CreateProductAction;
use App\Actions\Products\UpdateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::query()
            ->with('category')
            ->latest()
            ->get();

        return view('admin.products.index', compact('products'));
    }

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
        $this->authorize('create', Product::class);

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

    public function edit(Product $product)
{
    $this->authorize('update', $product);

    $product->load(['variants', 'images']);

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(
        UpdateProductRequest $request,
        Product $product,
        UpdateProductAction $updateProduct
    ) {
        $this->authorize('update', $product);

        $product = $updateProduct->execute(
            $product,
            $request->validated(),
            $request->file('image'),
            $request->file('images', [])
        );

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                "Le produit {$product->name} a été mis à jour."
            );
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with(
                'success',
                'Produit supprimé.'
            );
    }
}
