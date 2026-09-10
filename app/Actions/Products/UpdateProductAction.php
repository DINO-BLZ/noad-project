<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateProductAction
{
    public function execute(
        Product $product,
        array $data,
        ?UploadedFile $coverImage = null,
        array $galleryImages = []
    ): Product {
        $this->assertNoDuplicateVariants($product, $data['variants'] ?? []);

        $newFiles = [];
        $filesToDelete = [];

        try {
            $updatedProduct = DB::transaction(function () use (
                $product,
                $data,
                $coverImage,
                $galleryImages,
                &$newFiles,
                &$filesToDelete
            ) {
                /*
                 * ---------------------------------------------------------
                 * 1. INFORMATIONS DU PRODUIT
                 * ---------------------------------------------------------
                 */
                $product->update([
                    'name' => $data['name'],
                    'price' => $data['price'],
                    'category_id' => $data['category_id'],
                    'description' => $data['description'] ?? null,
                ]);

                /*
                 * ---------------------------------------------------------
                 * 2. IMAGE DE COUVERTURE
                 * ---------------------------------------------------------
                 */
                if ($coverImage instanceof UploadedFile) {
                    $newCoverPath = $coverImage->store(
                        'products',
                        'public'
                    );

                    if (! $newCoverPath) {
                        throw new \RuntimeException(
                            'Impossible d’enregistrer l’image de couverture.'
                        );
                    }

                    $newFiles[] = $newCoverPath;

                    if ($product->image) {
                        $filesToDelete[] = $product->image;
                    }

                    $product->update([
                        'image' => $newCoverPath,
                    ]);
                }

                /*
                 * ---------------------------------------------------------
                 * 3. VARIANTES
                 * ---------------------------------------------------------
                 */
                $submittedVariants = $data['variants'] ?? [];
                $submittedVariantIds = [];

                foreach ($submittedVariants as $variantData) {
                    $variantId = $variantData['id'] ?? null;

                    if ($variantId) {
                        /*
                         * Sécurité importante :
                         * la variante doit appartenir à CE produit.
                         */
                        $variant = $product->variants()
                            ->whereKey($variantId)
                            ->lockForUpdate()
                            ->first();

                        if (! $variant) {
                            throw ValidationException::withMessages([
                                'variants' => ['Une variante sélectionnée est invalide.'],
                            ]);
                        }

                        $variant->update([
                            'size' => $variantData['size'],
                            'stock' => $variantData['stock'],
                            'sku' => $this->nullableSku($variantData['sku'] ?? null),
                            'color' => $variantData['color'] ?? '',
                        ]);

                        $submittedVariantIds[] = $variant->id;
                    } else {
                        $variant = $product->variants()->create([
                            'size' => $variantData['size'],
                            'stock' => $variantData['stock'],
                            'sku' => $this->nullableSku($variantData['sku'] ?? null),
                            'color' => $variantData['color'] ?? '',
                        ]);

                        $submittedVariantIds[] = $variant->id;
                    }
                }

                /*
                 * Supprime les variantes qui ne sont plus présentes
                 * dans le formulaire.
                 */
                $variantsToDelete = $product->variants()
                    ->when(
                        ! empty($submittedVariantIds),
                        fn ($query) => $query->whereNotIn(
                            'id',
                            $submittedVariantIds
                        )
                    )
                    ->get();

                foreach ($variantsToDelete as $variant) {
                    $variant->delete();
                }

                /*
                 * ---------------------------------------------------------
                 * 4. SUPPRESSION DES IMAGES GALERIE
                 * ---------------------------------------------------------
                 */
                $removeImageIds = $data['remove_images'] ?? [];

                if (! empty($removeImageIds)) {
                    $imagesToRemove = $product->images()
                        ->whereIn('id', $removeImageIds)
                        ->lockForUpdate()
                        ->get();

                    foreach ($imagesToRemove as $image) {
                        if ($image->path) {
                            $filesToDelete[] = $image->path;
                        }

                        /*
                         * ProductImage's deleting hook removes the file
                         * immediately. Skip it here so a later failure in
                         * this transaction can roll back without leaving a
                         * DB row that points at a missing file. Physical
                         * files are deleted only after commit.
                         */
                        ProductImage::withoutEvents(
                            fn () => $image->delete()
                        );
                    }
                }

                /*
                 * ---------------------------------------------------------
                 * 5. AJOUT DES IMAGES GALERIE
                 * ---------------------------------------------------------
                 */
                foreach ($galleryImages as $galleryImage) {
                    if (! $galleryImage instanceof UploadedFile) {
                        continue;
                    }

                    $path = $galleryImage->store(
                        'products/gallery',
                        'public'
                    );

                    if (! $path) {
                        throw new \RuntimeException(
                            'Impossible d’enregistrer une image de galerie.'
                        );
                    }

                    $newFiles[] = $path;

                    $product->images()->create([
                        'path' => $path,
                        'is_primary' => false,
                    ]);
                }

                /*
                 * ---------------------------------------------------------
                 * 6. IMAGE PRINCIPALE
                 * ---------------------------------------------------------
                 */
                $primaryImageId = $data['primary_image'] ?? null;

                if ($primaryImageId !== null && $primaryImageId !== '') {
                    $primaryImage = $product->images()
                        ->whereKey($primaryImageId)
                        ->lockForUpdate()
                        ->first();

                    if (! $primaryImage) {
                        throw ValidationException::withMessages([
                            'primary_image' => ['L’image principale sélectionnée est invalide.'],
                        ]);
                    }

                    /*
                     * Une seule image principale. The in-memory model still
                     * has the pre-update is_primary value, so a second
                     * $primaryImage->update() would be skipped as "not dirty"
                     * if it was already primary — leaving none after the
                     * mass update. Write through the query instead.
                     */
                    $product->images()->update([
                        'is_primary' => false,
                    ]);

                    $product->images()
                        ->whereKey($primaryImage->id)
                        ->update([
                            'is_primary' => true,
                        ]);
                }

                /*
                 * ---------------------------------------------------------
                 * 7. FALLBACK IMAGE PRINCIPALE
                 * ---------------------------------------------------------
                 */
                if (
                    ! $product->images()
                        ->where('is_primary', true)
                        ->exists()
                ) {
                    $fallbackImage = $product->images()
                        ->orderBy('id')
                        ->first();

                    if ($fallbackImage) {
                        $fallbackImage->update([
                            'is_primary' => true,
                        ]);
                    }
                }

                return $product->fresh([
                    'category',
                    'variants',
                    'images',
                ]);
            });

            /*
             * -------------------------------------------------------------
             * 8. SUPPRESSION DES ANCIENS FICHIERS
             *
             * La transaction DB est maintenant terminée avec succès.
             * On peut supprimer les anciens fichiers.
             * -------------------------------------------------------------
             */
            foreach (array_unique($filesToDelete) as $path) {
                Storage::disk('public')->delete($path);
            }

            return $updatedProduct;

        } catch (Throwable $e) {
            /*
             * La DB a échoué (ou la validation a rejeté la demande avant
             * même d'entrer en transaction) :
             * on supprime uniquement les nouveaux fichiers créés
             * pendant cette opération.
             */
            foreach (array_unique($newFiles) as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }

    /**
     * Vérifie, avant toute écriture, qu'aucune combinaison taille/couleur
     * n'est soumise deux fois et qu'aucun SKU soumis n'est déjà utilisé
     * par une autre variante (une contrainte UNIQUE existe en base sur
     * les deux, mais on veut un message de validation propre plutôt
     * qu'une erreur SQL brute).
     */
    private function assertNoDuplicateVariants(Product $product, array $variantsInput): void
    {
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

            $sku = $this->nullableSku($variantData['sku'] ?? null);

            if ($sku) {
                $submittedSkus[] = $sku;

                $conflict = Variant::where('sku', $sku)
                    ->when(
                        ! empty($variantData['id']),
                        fn ($query) => $query->where('id', '!=', $variantData['id'])
                    )
                    ->exists();

                if ($conflict) {
                    throw ValidationException::withMessages([
                        'variants' => ["Le SKU \"{$sku}\" est déjà utilisé par une autre variante."],
                    ]);
                }
            }
        }

        if (count($submittedSkus) !== count(array_unique($submittedSkus))) {
            throw ValidationException::withMessages([
                'variants' => ['Un même SKU est utilisé plusieurs fois dans le formulaire.'],
            ]);
        }
    }

    private function nullableSku(mixed $sku): ?string
    {
        if (! is_string($sku)) {
            return null;
        }

        $sku = trim($sku);

        return $sku === '' ? null : $sku;
    }
}
