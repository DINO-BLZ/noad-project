<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Drops\ApproveWhitelistAction;
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
use LogicException;

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

        return view(
            'admin.drops.create',
            compact('products', 'categories')
        );
    }

    public function store(DropRequest $request)
    {
        $this->authorize('create', Drop::class);

        $data = $request->validated();

        $data['slug'] = Str::slug($data['name']) . '-' . uniqid();

        $validatedNewProducts = $this->prepareNewProducts(
            $request->input('new_products', [])
        );

        DB::transaction(function () use (
            $request,
            $data,
            $validatedNewProducts
        ) {
            $drop = Drop::create($data);

            $productIds = $request->input('products', []);

            foreach ($validatedNewProducts as $index => $newProduct) {
                $productIds[] = $this->createProductFromNewProductData(
                    $request,
                    $index,
                    $newProduct
                );
            }

            $drop->products()->sync($productIds);
        });

        return redirect()
            ->route('admin.drops.index')
            ->with('success', 'Drop créé avec succès.');
    }

    public function edit(Drop $drop)
    {
        $products = Product::all();
        $categories = Category::all();

        $whitelistRequests = $drop
            ->whitelists()
            ->with('user')
            ->latest()
            ->get();

        return view(
            'admin.drops.edit',
            compact(
                'drop',
                'products',
                'categories',
                'whitelistRequests'
            )
        );
    }

    public function update(DropRequest $request, Drop $drop)
    {
        $this->authorize('update', $drop);

        $data = $request->validated();

        $validatedNewProducts = $this->prepareNewProducts(
            $request->input('new_products', [])
        );

        DB::transaction(function () use (
            $request,
            $data,
            $drop,
            $validatedNewProducts
        ) {
            $drop->update($data);

            $productIds = $request->input('products', []);

            foreach ($validatedNewProducts as $index => $newProduct) {
                $productIds[] = $this->createProductFromNewProductData(
                    $request,
                    $index,
                    $newProduct
                );
            }

            $drop->products()->sync($productIds);
        });

        return redirect()
            ->route('admin.drops.index')
            ->with('success', 'Drop mis à jour.');
    }

    public function destroy(Drop $drop)
    {
        $this->authorize('delete', $drop);

        $drop->delete();

        return redirect()
            ->route('admin.drops.index')
            ->with('success', 'Drop supprimé.');
    }

    /**
     * Approuve une demande de whitelist.
     *
     * La logique métier complexe est déléguée à
     * ApproveWhitelistAction, notamment la gestion du quota
     * et de la concurrence.
     */
    public function approveWhitelist(
        Drop $drop,
        $whitelistId,
        ApproveWhitelistAction $approveWhitelist
    ) {
        $whitelist = $drop
            ->whitelists()
            ->with('user')
            ->findOrFail($whitelistId);

        $this->authorize('approve', $whitelist);

        try {
            $whitelist = $approveWhitelist->execute(
                $drop,
                $whitelist
            );
        } catch (LogicException $exception) {
            return back()->withErrors([
                'whitelist' => $exception->getMessage(),
            ]);
        }

        Mail::to($whitelist->user->email)
            ->send(new WhitelistStatusMail($whitelist));

        return back()->with(
            'success',
            'Demande approuvée.'
        );
    }

    /**
     * Refuse une demande de whitelist.
     *
     * Le rejet ne nécessite actuellement pas d'Action dédiée :
     * aucune règle métier complexe n'est exécutée ici.
     */
    public function rejectWhitelist(
        Drop $drop,
        $whitelistId
    ) {
        $whitelist = $drop
            ->whitelists()
            ->with('user')
            ->findOrFail($whitelistId);

        $this->authorize('reject', $whitelist);

        $whitelist->update([
            'status' => 'rejected',
        ]);

        /*
         * On rattache explicitement le Drop à la relation
         * afin que le Mailable puisse l'utiliser sans effectuer
         * une nouvelle requête inutile.
         */
        $whitelist->setRelation('drop', $drop);

        Mail::to($whitelist->user->email)
            ->send(new WhitelistStatusMail($whitelist));

        return back()->with(
            'success',
            'Demande refusée.'
        );
    }

    /**
     * Valide tous les nouveaux produits avant toute écriture
     * en base ou sur le filesystem.
     *
     * Les lignes complètement vides sont ignorées.
     *
     * @throws ValidationException
     */
    private function prepareNewProducts(array $newProducts): array
    {
        $errors = [];
        $validated = [];

        foreach ($newProducts as $index => $newProduct) {
            $name = $newProduct['name'] ?? null;
            $price = $newProduct['price'] ?? null;

            /*
             * Ligne totalement vide :
             * slot inutilisé du formulaire.
             */
            if (empty($name) && empty($price)) {
                continue;
            }

            /*
             * Ligne partiellement remplie :
             * erreur de validation.
             */
            if (empty($name) || empty($price)) {
                $errors["new_products.$index"] = [
                    'Produit #' . ($index + 1) .
                    ' : le nom et le prix sont tous les deux requis.',
                ];

                continue;
            }

            /*
             * Vérification des combinaisons taille/couleur
             * dans un même produit.
             */
            $seenCombinations = [];

            foreach ($newProduct['sizes'] ?? [] as $sizeData) {
                if (
                    empty($sizeData['size']) ||
                    !isset($sizeData['stock'])
                ) {
                    continue;
                }

                $combination =
                    ($sizeData['size'] ?? '') .
                    '|' .
                    ($sizeData['color'] ?? '');

                if (isset($seenCombinations[$combination])) {
                    $errors["new_products.$index.sizes"] = [
                        'Produit #' . ($index + 1) .
                        ' : la combinaison taille/couleur "' .
                        ($sizeData['size'] ?? '') .
                        ' / ' .
                        ($sizeData['color'] ?? '') .
                        '" est en double.',
                    ];
                }

                $seenCombinations[$combination] = true;
            }

            $validated[$index] = $newProduct;
        }

        /*
         * Collecte des SKU.
         */
        $allSkus = [];

        foreach ($validated as $newProduct) {
            foreach ($newProduct['sizes'] ?? [] as $sizeData) {
                if (!empty($sizeData['sku'])) {
                    $allSkus[] = $sizeData['sku'];
                }
            }
        }

        if (!empty($allSkus)) {
            /*
             * Détection des doublons parmi les nouveaux produits.
             */
            $skuCounts = array_count_values($allSkus);

            $duplicateSkus = array_keys(
                array_filter(
                    $skuCounts,
                    fn (int $count): bool => $count > 1
                )
            );

            if (!empty($duplicateSkus)) {
                $errors['new_products'][] =
                    'Doublon de SKU dans les nouveaux produits : ' .
                    implode(', ', $duplicateSkus);
            }

            /*
             * Vérification des SKU déjà présents en base.
             */
            $existingSkus = Variant::query()
                ->whereIn('sku', array_unique($allSkus))
                ->pluck('sku')
                ->all();

            if (!empty($existingSkus)) {
                $errors['new_products'][] =
                    'SKU déjà utilisé en base : ' .
                    implode(', ', $existingSkus);
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $validated;
    }

    /**
     * Crée un produit et ses variantes.
     *
     * Si la création SQL échoue après l'upload de l'image,
     * l'image est supprimée manuellement car une transaction
     * SQL ne rollback pas le filesystem.
     */
    private function createProductFromNewProductData(
        DropRequest $request,
        int $index,
        array $newProduct
    ): int {
        $imagePath = null;

        if ($request->hasFile("new_products.$index.image")) {
            $imagePath = $request
                ->file("new_products.$index.image")
                ->store('products', 'public');
        }

        try {
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
                if (
                    !empty($sizeData['size']) &&
                    isset($sizeData['stock'])
                ) {
                    $product->variants()->create([
                        'size' => $sizeData['size'],
                        'stock' => $sizeData['stock'],
                        'sku' => $sizeData['sku'] ?? null,
                        'color' => $sizeData['color'] ?? null,
                    ]);

                    $hasValidSize = true;
                }
            }

            /*
             * Si aucune variante valide n'est fournie,
             * création d'une variante unique.
             */
            if (!$hasValidSize) {
                $product->variants()->create([
                    'size' => 'Unique',
                    'stock' => 1,
                ]);
            }

            return $product->id;
        } catch (\Throwable $exception) {
            /*
             * La transaction SQL ne supprime pas automatiquement
             * le fichier déjà envoyé sur le disque.
             */
            if ($imagePath) {
                Storage::disk('public')->delete($imagePath);
            }

            throw $exception;
        }
    }
}

