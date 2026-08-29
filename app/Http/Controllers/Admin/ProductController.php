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

    public function update(UpdateProductRequest $request, Product $product)
    {
        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Pré-validation des variantes (avant toute écriture)
        |--------------------------------------------------------------------------
        |
        | La base impose une contrainte unique (product_id, size, color) et une
        | contrainte unique globale sur sku. On vérifie tout ça en amont pour
        | renvoyer une erreur de validation propre plutôt qu'un crash SQL.
        */
        $variantsInput = $data['variants'] ?? [];
        $seenCombos = [];
        $submittedSkus = [];

        foreach ($variantsInput as $variantData) {
            $combo = ($variantData['size'] ?? '').'|'.($variantData['color'] ?? '');

            if (isset($seenCombos[$combo])) {
                throw ValidationException::withMessages([
                    'variants' => ["La combinaison taille/couleur \"{$variantData['size']}\" est en double."],
                ]);
            }
            $seenCombos[$combo] = true;

            if (! empty($variantData['sku'])) {
                $submittedSkus[] = $variantData['sku'];
            }
        }

        if (count($submittedSkus) !== count(array_unique($submittedSkus))) {
            throw ValidationException::withMessages([
                'variants' => ['Un même SKU est utilisé plusieurs fois dans le formulaire.'],
            ]);
        }

        $newImagePath = null;

        try {
            DB::transaction(function () use ($request, $product, $data, $variantsInput, $submittedSkus, &$newImagePath) {

                /*
                |----------------------------------------------------------------
                | Champs de base
                |----------------------------------------------------------------
                */
                $product->update([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'category_id' => $data['category_id'],
                    'description' => $data['description'] ?? null,
                ]);

                /*
                |----------------------------------------------------------------
                | Remplacement de l'image de couverture
                |----------------------------------------------------------------
                */
                if ($request->hasFile('image')) {
                    $oldImage = $product->image;

                    $newImagePath = $request->file('image')->store('products', 'public');
                    $product->update(['image' => $newImagePath]);

                    if ($oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }
                }

                /*
                |----------------------------------------------------------------
                | Variantes : mise à jour des existantes + création des nouvelles
                |----------------------------------------------------------------
                */
                foreach ($variantsInput as $variantData) {
                    $sku = $variantData['sku'] ?? null;

                    if (! empty($variantData['id'])) {
                        $variant = Variant::where('product_id', $product->id)
                            ->find($variantData['id']);

                        if (! $variant) {
                            continue;
                        }

                        if ($sku && Variant::where('sku', $sku)->where('id', '!=', $variant->id)->exists()) {
                            throw ValidationException::withMessages([
                                'variants' => ["Le SKU \"{$sku}\" est déjà utilisé par une autre variante."],
                            ]);
                        }

                        $variant->update([
                            'size' => $variantData['size'],
                            'stock' => $variantData['stock'],
                            'color' => $variantData['color'] ?? null,
                            'sku' => $sku,
                        ]);
                    } else {
                        if ($sku && Variant::where('sku', $sku)->exists()) {
                            throw ValidationException::withMessages([
                                'variants' => ["Le SKU \"{$sku}\" est déjà utilisé par une autre variante."],
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
                |----------------------------------------------------------------
                | Galerie : suppression des images cochées
                |----------------------------------------------------------------
                |
                | ->delete() sur chaque modèle (et non une suppression en masse)
                | pour déclencher l'événement booted() qui nettoie le fichier
                | sur le disque.
                */
                foreach ($data['remove_images'] ?? [] as $imageId) {
                    ProductImage::where('product_id', $product->id)
                        ->find($imageId)
                        ?->delete();
                }

                /*
                |----------------------------------------------------------------
                | Galerie : ajout des nouvelles images
                |----------------------------------------------------------------
                */
                $position = $product->images()->max('position') ?? 0;

                foreach ($request->file('images', []) as $file) {
                    $position++;

                    $product->images()->create([
                        'path' => $file->store('products', 'public'),
                        'position' => $position,
                    ]);
                }

                /*
                |----------------------------------------------------------------
                | Galerie : image principale
                |----------------------------------------------------------------
                */
                if (! empty($data['primary_image'])) {
                    $product->images()->update(['is_primary' => false]);

                    $product->images()
                        ->where('id', $data['primary_image'])
                        ->update(['is_primary' => true]);
                }
            });
        } catch (ValidationException $e) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $e;
        }

        return redirect()
            ->route('admin.products.index')
            ->with('success', "Le produit {$product->name} a été mis à jour.");
    }

    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            // ->delete() modèle par modèle pour déclencher le nettoyage
            // des fichiers sur le disque (voir ProductImage::booted()).
            $product->images->each->delete();

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            // Les variantes sont supprimées en cascade au niveau base
            // (cascadeOnDelete). Les commandes passées conservent leur
            // historique via les champs "snapshot" sur order_items.
            $product->delete();
        });

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Produit supprimé.');
    }
}