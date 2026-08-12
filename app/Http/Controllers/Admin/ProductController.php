<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use App\Models\OrderItem;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
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
            'sizes.*.stock' => 'required|integer|min:0',
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();
        $data['image'] = $request->file('image')->store('products', 'public');

        $sizes = $data['sizes'];
        unset($data['sizes']);

        $product = Product::create($data);

        foreach ($sizes as $size => $sizeData) {
            $product->variants()->create([
                'size' => $size,
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
            'variants' => 'nullable|array',
            'variants.*.id' => 'nullable|integer|exists:variants,id',
            'variants.*.size' => 'required_with:variants|string|max:50',
            'variants.*.stock' => 'required_with:variants|integer|min:0',
            'variants.*.sku' => 'nullable|string',
            'variants.*.color' => 'nullable|string|max:50',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|max:4096',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'integer',
            'primary_image' => 'nullable|string',
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        DB::transaction(function () use ($request, $data, $product) {
            $product->update($data);

            $existing = $product->variants()->get()->keyBy('id');
            $kept = [];

            foreach ($request->input('variants', []) as $v) {
                if (isset($v['id']) && $v['id'] && $existing->has($v['id'])) {
                    $variant = $existing->get($v['id']);

                    if (!empty($v['sku']) && $v['sku'] !== $variant->sku && \App\Models\Variant::where('sku', $v['sku'])->exists()) {
                        throw ValidationException::withMessages(['variants' => ["SKU {$v['sku']} déjà utilisé."]]);
                    }

                    $variant->update([
                        'size' => $v['size'],
                        'stock' => $v['stock'] ?? 0,
                        'sku' => $v['sku'] ?? $variant->sku,
                        'color' => $v['color'] ?? $variant->color,
                    ]);
                    $kept[] = $variant->id;
                } else {
                    if (empty($v['size'])) continue;

                    if (!empty($v['sku']) && \App\Models\Variant::where('sku', $v['sku'])->exists()) {
                        throw ValidationException::withMessages(['variants' => ["SKU {$v['sku']} déjà utilisé."]]);
                    }

                    $new = $product->variants()->create([
                        'size' => $v['size'],
                        'stock' => $v['stock'] ?? 0,
                        'sku' => $v['sku'] ?? null,
                        'color' => $v['color'] ?? null,
                    ]);
                    $kept[] = $new->id;
                }
            }

            $toDelete = $existing->keys()->diff($kept);
            foreach ($toDelete as $variantId) {
                $hasOrders = OrderItem::where('variant_id', $variantId)->exists();
                if ($hasOrders) continue;
                Variant::find($variantId)?->delete();
            }

            if ($request->hasFile('images')) {
                $maxPos = $product->images()->max('position');
                $position = is_null($maxPos) ? 0 : $maxPos + 1;
                foreach ($request->file('images') as $index => $img) {
                    $path = $img->store('products', 'public');
                    $product->images()->create([
                        'path' => $path,
                        'position' => $position++,
                        'is_primary' => ($request->input('primary_image') == 'new_'.$index),
                    ]);
                }
            }

            foreach ($request->input('remove_images', []) as $imgId) {
                $img = $product->images()->find($imgId);
                if ($img) {
                    if ($img->path) {
                        Storage::disk('public')->delete($img->path);
                    }
                    $img->delete();
                }
            }

            if ($primary = $request->input('primary_image')) {
                $product->images()->update(['is_primary' => false]);
                if (!str_starts_with($primary, 'new_')) {
                    $img = $product->images()->find($primary);
                    if ($img) {
                        $img->is_primary = true;
                        $img->save();
                    }
                }
            }
        });

        return redirect()->route('admin.products.index')->with('success', 'Produit mis à jour.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produit supprimé.');
    }
}