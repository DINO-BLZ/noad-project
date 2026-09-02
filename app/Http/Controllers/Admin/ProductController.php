<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Products\CreateProductAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

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
        $product->load(['variants', 'images']);

        $categories = Category::query()
            ->orderBy('name')
            ->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(
        UpdateProductRequest $request,
        Product $product
    ) {
        $this->authorize('update', $product);

        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Pré-validation des variantes
        |--------------------------------------------------------------------------
        |
        | Vérification des doublons taille/couleur et des SKU présents
        | plusieurs fois dans le formulaire avant toute écriture.
        |
        */

        $variantsInput = $data['variants'] ?? [];
        $seenCombos = [];
        $submittedSkus = [];

        foreach ($variantsInput as $variantData) {
            $combo = ($variantData['size'] ?? '')
                . '|'
                . ($variantData['color'] ?? '');

            if (isset($seenCombos[$combo])) {
                throw ValidationException::withMessages([
                    'variants' => [
                        "La combinaison taille/couleur \"{$variantData['size']}\" est en double."
                    ],
                ]);
            }

            $seenCombos[$combo] = true;

            if (!empty($variantData['sku'])) {
                $submittedSkus[] = $variantData['sku'];
            }
        }

        if (count($submittedSkus) !== count(array_unique($submittedSkus))) {
            throw ValidationException::withMessages([
                'variants' => [
                    'Un même SKU est utilisé plusieurs fois dans le formulaire.'
                ],
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Mise à jour
        |--------------------------------------------------------------------------
        */

        $newImagePath = null;
        $newGalleryPaths = [];

        try {
            DB::transaction(function () use (
                $request,
                $product,
                $data,
                $variantsInput,
                &$newImagePath,
                &$newGalleryPaths
            ) {

                /*
                |--------------------------------------------------------------------------
                | Informations principales du produit
                |--------------------------------------------------------------------------
                */

                $product->update([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'category_id' => $data['category_id'],
                    'description' => $data['description'] ?? null,
                ]);

                /*
                |--------------------------------------------------------------------------
                | Image de couverture
                |--------------------------------------------------------------------------
                */

                if ($request->hasFile('image')) {
                    $oldImage = $product->image;

                    $newImagePath = $request
                        ->file('image')
                        ->store('products', 'public');

                    $product->update([
                        'image' => $newImagePath,
                    ]);

                    /*
                    | L'ancien fichier est supprimé seulement après
                    | la mise à jour du produit.
                    */
                    if ($oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Variantes
                |--------------------------------------------------------------------------
                */

                foreach ($variantsInput as $variantData) {
                    $sku = $variantData['sku'] ?? null;

                    /*
                    | Mise à jour d'une variante existante
                    */
                    if (!empty($variantData['id'])) {
                        $variant = Variant::where('product_id', $product->id)
                            ->find($variantData['id']);

                        /*
                        | Si la variante n'appartient pas au produit,
                        | on l'ignore.
                        */
                        if (!$variant) {
                            continue;
                        }

                        /*
                        | Vérification du SKU
                        */
                        if (
                            $sku &&
                            Variant::where('sku', $sku)
                                ->where('id', '!=', $variant->id)
                                ->exists()
                        ) {
                            throw ValidationException::withMessages([
                                'variants' => [
                                    "Le SKU \"{$sku}\" est déjà utilisé par une autre variante."
                                ],
                            ]);
                        }

                        $variant->update([
                            'size' => $variantData['size'],
                            'stock' => $variantData['stock'],
                            'color' => $variantData['color'] ?? null,
                            'sku' => $sku,
                        ]);
                    }

                    /*
                    | Création d'une nouvelle variante
                    */
                    else {
                        if (
                            $sku &&
                            Variant::where('sku', $sku)->exists()
                        ) {
                            throw ValidationException::withMessages([
                                'variants' => [
                                    "Le SKU \"{$sku}\" est déjà utilisé par une autre variante."
                                ],
                            ]);
                        }

                        $product->variants()->create([
                            'size' => $variantData['size'],
                            'stock' => $variantData['stock'],
                            'color' => $variantData['color'] ?? null,
                            'sku' => $sku,
                        ]);
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Suppression des images de galerie
                |--------------------------------------------------------------------------
                */

                foreach ($data['remove_images'] ?? [] as $imageId) {
                    ProductImage::where('product_id', $product->id)
                        ->find($imageId)
                        ?->delete();
                }

                /*
                |--------------------------------------------------------------------------
                | Ajout des nouvelles images de galerie
                |--------------------------------------------------------------------------
                */

                $position = $product->images()->max('position') ?? 0;

                foreach ($request->file('images', []) as $file) {
                    $position++;

                    $path = $file->store('products', 'public');

                    /*
                    | On garde une trace des fichiers créés.
                    | Si la transaction échoue, ils seront supprimés.
                    */
                    $newGalleryPaths[] = $path;

                    $product->images()->create([
                        'path' => $path,
                        'position' => $position,
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Image principale de la galerie
                |--------------------------------------------------------------------------
                */

                if (!empty($data['primary_image'])) {
                    $product->images()
                        ->update([
                            'is_primary' => false,
                        ]);

                    $product->images()
                        ->where('id', $data['primary_image'])
                        ->update([
                            'is_primary' => true,
                        ]);
                }
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Gestion du rollback
        |--------------------------------------------------------------------------
        |
        | On intercepte \Throwable (et pas seulement ValidationException) pour
        | garantir le nettoyage des fichiers même en cas d'erreur imprévue
        | (contrainte SQL, erreur de connexion, etc.).
        |
        */

        catch (\Throwable $e) {
            /*
            | Suppression de la nouvelle image de couverture
            */
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            /*
            | Suppression des nouvelles images de galerie
            */
            foreach ($newGalleryPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }

        /*
        |--------------------------------------------------------------------------
        | Succès
        |--------------------------------------------------------------------------
        */

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