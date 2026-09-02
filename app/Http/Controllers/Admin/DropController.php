<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\DropRequest;
use App\Mail\WhitelistStatusMail;
use App\Models\Category;
use App\Models\Drop;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

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
        $categories = Category::all();

        return view('admin.drops.create', compact('products', 'categories'));
    }

    public function store(DropRequest $request)
    {
        $this->authorize('create', Drop::class);

        $data = $request->validated();

        $data['slug'] = Str::slug($data['name']).'-'.uniqid();

        // Pré-validation des SKU fournis pour les nouveaux produits
        $skus = [];
        foreach ($request->input('new_products', []) as $npIndex => $np) {
            foreach ($np['sizes'] ?? [] as $sizeData) {
                if (! empty($sizeData['sku'])) {
                    $skus[] = $sizeData['sku'];
                }
            }
        }

        if (! empty($skus)) {
            $duplicates = array_diff_assoc($skus, array_unique($skus));
            if (! empty($duplicates)) {
                throw ValidationException::withMessages(['new_products' => ['Doublon de SKU dans les nouveaux produits : '.implode(', ', array_unique($duplicates))]]);
            }

            if (Variant::whereIn('sku', $skus)->exists()) {
                throw ValidationException::withMessages(['new_products' => ['Un des SKU fournis est déjà utilisé.']]);
            }
        }

        $this->validateNewProductSizeColorCombinations($request->input('new_products', []));

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
                    'slug' => Str::slug($newProduct['name']).'-'.uniqid(),
                    'price' => $newProduct['price'],
                    'image' => $imagePath,
                    'category_id' => $newProduct['category_id'] ?? null,
                ]);

                $sizes = $newProduct['sizes'] ?? [];
                $hasValidSize = false;

                foreach ($sizes as $sizeData) {
                    if (! empty($sizeData['size']) && isset($sizeData['stock'])) {
                        $product->variants()->create([
                            'size' => $sizeData['size'],
                            'stock' => $sizeData['stock'],
                            'sku' => $sizeData['sku'] ?? null,
                            'color' => $sizeData['color'] ?? null,
                        ]);
                        $hasValidSize = true;
                    }
                }

                if (! $hasValidSize) {
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
        $categories = Category::all();
        $whitelistRequests = $drop->whitelists()->with('user')->latest()->get();

        return view('admin.drops.edit', compact('drop', 'products', 'categories', 'whitelistRequests'));
    }

    public function update(DropRequest $request, Drop $drop)
    {
        $this->authorize('update', $drop);

        $data = $request->validated();
        $this->validateNewProductSizeColorCombinations($request->input('new_products', []));

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
                    'slug' => Str::slug($newProduct['name']).'-'.uniqid(),
                    'price' => $newProduct['price'],
                    'image' => $imagePath,
                    'category_id' => $newProduct['category_id'] ?? null,
                ]);

                $sizes = $newProduct['sizes'] ?? [];
                $hasValidSize = false;

                foreach ($sizes as $sizeData) {
                    if (! empty($sizeData['size']) && isset($sizeData['stock'])) {
                        // SKU uniqueness check
                        if (! empty($sizeData['sku']) && Variant::where('sku', $sizeData['sku'])->exists()) {
                            throw ValidationException::withMessages(['new_products.'.$index.'.sizes' => ["SKU {$sizeData['sku']} déjà utilisé."]]);
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

                if (! $hasValidSize) {
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
        $this->authorize('delete', $drop);

        $drop->delete();

        return redirect()->route('admin.drops.index')->with('success', 'Drop supprimé.');
    }

    public function approveWhitelist(Drop $drop, $whitelistId)
    {
        $whitelist = $drop->whitelists()->with('user')->findOrFail($whitelistId);
        $this->authorize('approve', $whitelist);

        $whitelist = DB::transaction(function () use ($drop, $whitelist) {
            $lockedDrop = Drop::query()->lockForUpdate()->findOrFail($drop->id);

            if (! $lockedDrop->hasWhitelistSlotsAvailable()) {
                return null;
            }

            $lockedWhitelist = $lockedDrop->whitelists()->with('user')->findOrFail($whitelist->id);
            $lockedWhitelist->update(['status' => 'approved']);
            $lockedWhitelist->setRelation('drop', $lockedDrop);

            return $lockedWhitelist;
        });

        if (! $whitelist) {
            return back()->withErrors(['whitelist' => 'Toutes les places de whitelist pour ce drop sont déjà attribuées.']);
        }

        Mail::to($whitelist->user->email)->send(new WhitelistStatusMail($whitelist));

        return back()->with('success', 'Demande approuvée.');
    }

    public function rejectWhitelist(Drop $drop, $whitelistId)
    {
        $whitelist = $drop->whitelists()->with('user')->findOrFail($whitelistId);
        $this->authorize('reject', $whitelist);

        $whitelist->update(['status' => 'rejected']);
        $whitelist->setRelation('drop', $drop);

        Mail::to($whitelist->user->email)->send(new WhitelistStatusMail($whitelist));

        return back()->with('success', 'Demande refusée.');
    }

    private function validateNewProductSizeColorCombinations(array $newProducts): void
    {
        foreach ($newProducts as $productIndex => $newProduct) {
            $seenCombinations = [];

            foreach ($newProduct['sizes'] ?? [] as $sizeData) {
                if (empty($sizeData['size']) || ! isset($sizeData['stock'])) {
                    continue;
                }

                $combination = ($sizeData['size'] ?? '').'|'.($sizeData['color'] ?? '');

                if (isset($seenCombinations[$combination])) {
                    throw ValidationException::withMessages([
                        'new_products.'.$productIndex.'.sizes' => [
                            'La combinaison taille/couleur "'.($sizeData['size'] ?? '').' / '.($sizeData['color'] ?? '').'" est en double.',
                        ],
                    ]);
                }

                $seenCombinations[$combination] = true;
            }
        }
    }
}
