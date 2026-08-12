<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('category')->latest()->get();

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'required|image|max:4096',
            'sizes' => 'required|array|min:1',
            'sizes.*.size' => 'required|string|max:10',
            'sizes.*.stock' => 'required|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        $data['image'] = $request->file('image')->store('products', 'public');

        $sizes = $data['sizes'];
        unset($data['sizes']);

        $product = Product::create($data);

        foreach ($sizes as $sizeData) {
            $product->variants()->create([
                'size' => $sizeData['size'],
                'stock' => $sizeData['stock'],
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produit ajouté avec succès.');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $product->load('variants');

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:4096',
            'sizes' => 'required|array|min:1',
            'sizes.*.size' => 'required|string|max:10',
            'sizes.*.stock' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $sizes = $data['sizes'];
        unset($data['sizes']);

        $product->update($data);

        $product->variants()->delete();
        foreach ($sizes as $sizeData) {
            $product->variants()->create([
                'size' => $sizeData['size'],
                'stock' => $sizeData['stock'],
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produit supprimé.');
    }
}