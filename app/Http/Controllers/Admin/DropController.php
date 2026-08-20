<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Drop;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Mail\WhitelistStatusMail;
use Illuminate\Support\Facades\Mail;

class DropController extends Controller
{
    public function index()
    {
        $drops = Drop::latest()->get();

        return view('admin.drops.index', compact('drops'));
    }

    public function create()
    {
        $products = Product::all();
        $categories = \App\Models\Category::all();

        return view('admin.drops.create', compact('products', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:upcoming,active,ended',
            'products' => 'array',
            'max_whitelist_slots' => 'nullable|integer|min:0',
            'products.*' => 'exists:products,id',
            'new_products' => 'array',
            'new_products.*.name' => 'nullable|string|max:255',
            'new_products.*.price' => 'nullable|numeric|min:0',
            'new_products.*.image' => 'nullable|image|max:4096',
            'new_products.*.category_id' => 'nullable|exists:categories,id',
            'new_products.*.sizes' => 'nullable|array',
            'new_products.*.sizes.*.size' => 'nullable|string|max:10',
            'new_products.*.sizes.*.stock' => 'nullable|integer|min:0',
            'new_products.*.sizes.*.sku' => 'nullable|string|max:50',
            'new_products.*.sizes.*.color' => 'nullable|string|max:50',
        ]);

        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();

        // Pré-validation des SKU fournis pour les nouveaux produits
        $skus = [];
        foreach ($request->input('new_products', []) as $npIndex => $np) {
            foreach ($np['sizes'] ?? [] as $sizeData) {
                if (!empty($sizeData['sku'])) {
                    $skus[] = $sizeData['sku'];
                }
            }
        }

        if (!empty($skus)) {
            $duplicates = array_diff_assoc($skus, array_unique($skus));
            if (!empty($duplicates)) {
                throw ValidationException::withMessages(['new_products' => ['Doublon de SKU dans les nouveaux produits : ' . implode(', ', array_unique($duplicates))]]);
            }

            if (\App\Models\Variant::whereIn('sku', $skus)->exists()) {
                throw ValidationException::withMessages(['new_products' => ['Un des SKU fournis est déjà utilisé.']]);
            }
        }

        DB::transaction(function () use ($request, $data) {
            $drop = Drop::create($data);

            $productIds = $request->input('products', []);

            foreach ($request->input('new_products', []) as $index => $newProduct) {
                if (empty($newProduct['name']) || empty($newProduct['price'])) {
                    continue;
                }

                $imagePath = null;
                if ($request->hasFile("new_products.$index.image")) {
                    $imagePath = $request->file("new_products.$index.image")->store('products', 'public');
                }

                $product = Product::create([
                    'name' => $newProduct['name'],
                    'slug' => Str::slug($newProduct['name']) . '-' . uniqid(),
                    'price' => $newProduct['price'],
                    'image' => $imagePath,
                    'category_id' => $newProduct['category_id'] ?? null,
                ]);

                $sizes = $newProduct['sizes'] ?? [];
                $hasValidSize = false;

                foreach ($sizes as $sizeData) {
                    if (!empty($sizeData['size']) && isset($sizeData['stock'])) {
                        $product->variants()->create([
                            'size' => $sizeData['size'],
                            'stock' => $sizeData['stock'],
                            'sku' => $sizeData['sku'] ?? null,
                            'color' => $sizeData['color'] ?? null,
                        ]);
                        $hasValidSize = true;
                    }
                }

                if (!$hasValidSize) {
                    $product->variants()->create(['size' => 'Unique', 'stock' => 1]);
                }

                $productIds[] = $product->id;
            }

            $drop->products()->sync($productIds);
        });

        return redirect()->route('admin.drops.index')->with('success', 'Drop créé avec succès.');
    }

    public function edit(Drop $drop)
    {
        $products = Product::all();
        $categories = \App\Models\Category::all();
        $whitelistRequests = $drop->whitelists()->with('user')->latest()->get();

        return view('admin.drops.edit', compact('drop', 'products', 'categories', 'whitelistRequests'));
    }

    public function update(Request $request, Drop $drop)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'required|in:upcoming,active,ended',
            'products' => 'array',
            'max_whitelist_slots' => 'nullable|integer|min:0',
            'products.*' => 'exists:products,id',
            'new_products' => 'array',
            'new_products.*.name' => 'nullable|string|max:255',
            'new_products.*.price' => 'nullable|numeric|min:0',
            'new_products.*.image' => 'nullable|image|max:4096',
            'new_products.*.category_id' => 'nullable|exists:categories,id',
            'new_products.*.sizes' => 'nullable|array',
            'new_products.*.sizes.*.size' => 'nullable|string|max:10',
            'new_products.*.sizes.*.stock' => 'nullable|integer|min:0',
            'new_products.*.sizes.*.sku' => 'nullable|string|max:50',
            'new_products.*.sizes.*.color' => 'nullable|string|max:50',
        ]);

        DB::transaction(function () use ($request, $data, $drop) {
            $drop->update($data);

            $productIds = $request->input('products', []);

            foreach ($request->input('new_products', []) as $index => $newProduct) {
                if (empty($newProduct['name']) || empty($newProduct['price'])) {
                    continue;
                }

                $imagePath = null;
                if ($request->hasFile("new_products.$index.image")) {
                    $imagePath = $request->file("new_products.$index.image")->store('products', 'public');
                }

                $product = Product::create([
                    'name' => $newProduct['name'],
                    'slug' => Str::slug($newProduct['name']) . '-' . uniqid(),
                    'price' => $newProduct['price'],
                    'image' => $imagePath,
                    'category_id' => $newProduct['category_id'] ?? null,
                ]);

                $sizes = $newProduct['sizes'] ?? [];
                $hasValidSize = false;

                foreach ($sizes as $sizeData) {
                    if (!empty($sizeData['size']) && isset($sizeData['stock'])) {
                        // SKU uniqueness check
                        if (!empty($sizeData['sku']) && \App\Models\Variant::where('sku', $sizeData['sku'])->exists()) {
                            throw \Illuminate\Validation\ValidationException::withMessages(['new_products.'.$index.'.sizes' => ["SKU {$sizeData['sku']} déjà utilisé."]]);
                        }

                        $product->variants()->create([
                            'size' => $sizeData['size'],
                            'stock' => $sizeData['stock'],
                            'sku' => $sizeData['sku'] ?? null,
                            'color' => $sizeData['color'] ?? null,
                        ]);
                        $hasValidSize = true;
                    }
                }

                if (!$hasValidSize) {
                    $product->variants()->create(['size' => 'Unique', 'stock' => 1]);
                }

                $productIds[] = $product->id;
            }

            $drop->products()->sync($productIds);
        });

        return redirect()->route('admin.drops.index')->with('success', 'Drop mis à jour.');
    }

    public function destroy(Drop $drop)
    {
        $drop->delete();

        return redirect()->route('admin.drops.index')->with('success', 'Drop supprimé.');
    }

    public function approveWhitelist(Drop $drop, $whitelistId)
    {
        if (! $drop->hasWhitelistSlotsAvailable()) {
            return back()->withErrors(['whitelist' => 'Toutes les places de whitelist pour ce drop sont déjà attribuées.']);
        }

        $whitelist = $drop->whitelists()->with('user')->findOrFail($whitelistId);
        $whitelist->update(['status' => 'approved']);
        $whitelist->setRelation('drop', $drop);

        Mail::to($whitelist->user->email)->send(new WhitelistStatusMail($whitelist));

        return back()->with('success', 'Demande approuvée.');
    }

    public function rejectWhitelist(Drop $drop, $whitelistId)
    {
        $whitelist = $drop->whitelists()->with('user')->findOrFail($whitelistId);
        $whitelist->update(['status' => 'rejected']);
        $whitelist->setRelation('drop', $drop);

        Mail::to($whitelist->user->email)->send(new WhitelistStatusMail($whitelist));

        return back()->with('success', 'Demande refusée.');
    }
}