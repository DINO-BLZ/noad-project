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
use Illuminate\Support\Facades\Storage;
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

        $validatedNewProducts = $this->prepareNewProducts($request->input('new_products', []));

        DB::transaction(function () use ($request, $data, $validatedNewProducts) {
            $drop = Drop::create($data);

            $productIds = $request->input('products', []);

            foreach ($validatedNewProducts as $index => $newProduct) {
                $productIds[] = $this->createProductFromNewProductData($request, $index, $newProduct);
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
        $validatedNewProducts = $this->prepareNewProducts($request->input('new_products', []));

        DB::transaction(function () use ($request, $data, $drop, $validatedNewProducts) {
            $drop->update($data);

            $productIds = $request->input('products', []);

            foreach ($validatedNewProducts as $index => $newProduct) {
                $productIds[] = $this->createProductFromNewProductData($request, $index, $newProduct);
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

    /**
     * Valide tous les nouveaux produits d'un coup, avant toute écriture en base ou sur disque.
     * Retourne uniquement les produits valides (les lignes totalement vides sont ignorées).
     * Lève une ValidationException regroupant toutes les erreurs si au moins un produit est invalide.
     */
    private function prepareNewProducts(array $newProducts): array
    {
        $errors = [];
        $validated = [];

        foreach ($newProducts as $index => $newProduct) {
            $name = $newProduct['name'] ?? null;
            $price = $newProduct['price'] ?? null;

            // Ligne totalement vide : slot de formulaire non utilisé, on ignore silencieusement.
            if (empty($name) && empty($price)) {
                continue;
            }

            // Ligne partiellement remplie : vraie erreur de saisie, on la signale.
            if (empty($name) || empty($price)) {
                $errors['new_products.'.$index] = [
                    'Produit #'.($index + 1).' : le nom et le prix sont tous les deux requis.',
                ];

                continue;
            }

            $seenCombinations = [];

            foreach ($newProduct['sizes'] ?? [] as $sizeData) {
                if (empty($sizeData['size']) || ! isset($sizeData['stock'])) {
                    continue;
                }

                $combination = ($sizeData['size'] ?? '').'|'.($sizeData['color'] ?? '');

                if (isset($seenCombinations[$combination])) {
                    $errors['new_products.'.$index.'.sizes'] = [
                        'Produit #'.($index + 1).' : la combinaison taille/couleur "'.($sizeData['size'] ?? '').' / '.($sizeData['color'] ?? '').'" est en double.',
                    ];
                }

                $seenCombinations[$combination] = true;
            }

            $validated[$index] = $newProduct;
        }

        // Doublons de SKU, à la fois entre les nouveaux produits et contre la base existante.
        $allSkus = [];
        foreach ($validated as $newProduct) {
            foreach ($newProduct['sizes'] ?? [] as $sizeData) {
                if (! empty($sizeData['sku'])) {
                    $allSkus[] = $sizeData['sku'];
                }
            }
        }

        if (! empty($allSkus)) {
            $duplicates = array_unique(array_diff_assoc($allSkus, array_unique($allSkus)));
            if (! empty($duplicates)) {
                $errors['new_products'][] = 'Doublon de SKU dans les nouveaux produits : '.implode(', ', $duplicates);
            }

            $existingSkus = Variant::whereIn('sku', $allSkus)->pluck('sku')->all();
            if (! empty($existingSkus)) {
                $errors['new_products'][] = 'SKU déjà utilisé en base : '.implode(', ', $existingSkus);
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $validated;
    }

    /**
     * Crée un produit (et ses variantes) à partir d'une entrée new_products déjà validée.
     * Nettoie l'image uploadée si la création échoue après l'upload (filet de sécurité,
     * car une transaction SQL ne rollback jamais le filesystem).
     */
    private function createProductFromNewProductData($request, int $index, array $newProduct): int
    {
        $imagePath = null;
        if ($request->hasFile("new_products.$index.image")) {
            $imagePath = $request->file("new_products.$index.image")->store('products', 'public');
        }

        try {
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
        } catch (\Throwable $e) {
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $e;
        }

        return $product->id;
    }
}