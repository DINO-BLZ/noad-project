<?php

namespace App\Actions\Products;

use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateProductAction
{
    public function execute(
        Product $product,
        array $data,
        ?UploadedFile $coverImage = null,
        array $galleryImages = []
    ): Product {
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
                            ->first();

                        if (! $variant) {
                            throw new \RuntimeException(
                                'Une variante sélectionnée est invalide.'
                            );
                        }

                        $variant->update([
                            'size' => $variantData['size'],
                            'stock' => $variantData['stock'],
                            'sku' => $variantData['sku'] ?? null,
                            'color' => $variantData['color'] ?? null,
                        ]);

                        $submittedVariantIds[] = $variant->id;
                    } else {
                        $variant = $product->variants()->create([
                            'size' => $variantData['size'],
                            'stock' => $variantData['stock'],
                            'sku' => $variantData['sku'] ?? null,
                            'color' => $variantData['color'] ?? null,
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
                        ->get();

                    foreach ($imagesToRemove as $image) {
                        if ($image->path) {
                            $filesToDelete[] = $image->path;
                        }

                        $image->delete();
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
                        throw new \RuntimeException(
                            'L’image principale sélectionnée est invalide.'
                        );
                    }

                    /*
                     * Une seule image principale.
                     */
                    $product->images()->update([
                        'is_primary' => false,
                    ]);

                    $primaryImage->update([
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
             * La DB a échoué :
             * on supprime uniquement les nouveaux fichiers créés
             * pendant cette opération.
             */
            foreach (array_unique($newFiles) as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }
}