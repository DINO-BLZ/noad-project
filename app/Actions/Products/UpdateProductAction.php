<?php

namespace App\Actions\Products;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Variant;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateProductAction
{
    /**
     * Update a product, its variants and its gallery.
     *
     * @param  array<string, mixed>  $data
     * @param  array<int, UploadedFile>  $galleryImages
     */
    public function execute(
        Product $product,
        array $data,
        ?UploadedFile $coverImage = null,
        array $galleryImages = []
    ): Product {
        $newCoverPath = null;
        $newGalleryPaths = [];
        $oldCoverPath = null;
        $removedGalleryPaths = [];

        try {
            /*
             * Upload the new cover before the transaction.
             *
             * Database transactions do not rollback filesystem changes,
             * so every uploaded file is tracked and cleaned up if
             * something fails.
             */
            if ($coverImage) {
                $newCoverPath = $coverImage->store(
                    'products',
                    'public'
                );
            }

            foreach ($galleryImages as $galleryImage) {
                if ($galleryImage instanceof UploadedFile) {
                    $newGalleryPaths[] = $galleryImage->store(
                        'products',
                        'public'
                    );
                }
            }

            $removedGalleryIds = collect(
                $data['remove_images'] ?? []
            )
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values();

            $selectedPrimaryImageId = isset($data['primary_image'])
                ? (int) $data['primary_image']
                : null;

            DB::transaction(function () use (
                $product,
                $data,
                $newCoverPath,
                $newGalleryPaths,
                $removedGalleryIds,
                $selectedPrimaryImageId,
                &$oldCoverPath,
                &$removedGalleryPaths
            ) {
                /*
                 * Lock the product while updating it.
                 */
                $product->lockForUpdate();

                /*
                 * Update basic product information.
                 */
                $product->update([
                    'category_id' => $data['category_id'],
                    'name' => $data['name'],
                    'description' => $data['description'] ?? null,
                    'price' => $data['price'],
                ]);

                /*
                 * Replace the main product image.
                 */
                if ($newCoverPath) {
                    $oldCoverPath = $product->image;

                    $product->update([
                        'image' => $newCoverPath,
                    ]);
                }

                /*
                 * ---------------------------------------------------------
                 * VARIANTS
                 * ---------------------------------------------------------
                 */

                $submittedVariants = $data['variants'] ?? [];

                $submittedVariantIds = [];

                foreach ($submittedVariants as $variantData) {
                    $variantId = isset($variantData['id'])
                        ? (int) $variantData['id']
                        : null;

                    /*
                     * Existing variant.
                     */
                    if ($variantId) {
                        $variant = $product->variants()
                            ->whereKey($variantId)
                            ->lockForUpdate()
                            ->first();

                        /*
                         * Never allow a variant belonging to another
                         * product to be modified.
                         */
                        if (! $variant) {
                            throw new \RuntimeException(
                                'Une variante sélectionnée n’appartient pas à ce produit.'
                            );
                        }

                        $variant->update([
                            'size' => $variantData['size'],
                            'stock' => $variantData['stock'],
                            'sku' => $variantData['sku'] ?? null,
                            'color' => $variantData['color'] ?? null,
                        ]);

                        $submittedVariantIds[] = $variant->id;

                        continue;
                    }

                    /*
                     * New variant.
                     */
                    $variant = $product->variants()->create([
                        'size' => $variantData['size'],
                        'stock' => $variantData['stock'],
                        'sku' => $variantData['sku'] ?? null,
                        'color' => $variantData['color'] ?? null,
                    ]);

                    $submittedVariantIds[] = $variant->id;
                }

                /*
                 * Variants removed from the form are deleted.
                 *
                 * Only variants belonging to this product can be deleted.
                 */
                $product->variants()
                    ->whereNotIn('id', $submittedVariantIds)
                    ->delete();

                /*
                 * ---------------------------------------------------------
                 * GALLERY
                 * ---------------------------------------------------------
                 */

                /*
                 * Remove selected gallery images.
                 */
                if ($removedGalleryIds->isNotEmpty()) {
                    $imagesToRemove = $product->images()
                        ->whereIn('id', $removedGalleryIds)
                        ->lockForUpdate()
                        ->get();

                    foreach ($imagesToRemove as $image) {
                        $removedGalleryPaths[] = $image->path;
                    }

                    $product->images()
                        ->whereIn('id', $removedGalleryIds)
                        ->delete();
                }

                /*
                 * Add new gallery images.
                 */
                foreach ($newGalleryPaths as $path) {
                    $product->images()->create([
                        'path' => $path,
                        'is_primary' => false,
                    ]);
                }

                /*
                 * ---------------------------------------------------------
                 * PRIMARY GALLERY IMAGE
                 * ---------------------------------------------------------
                 */

                /*
                 * If the administrator selected a primary image,
                 * verify that the image belongs to this product and
                 * has not been removed.
                 */
                if ($selectedPrimaryImageId !== null) {
                    $primaryImage = $product->images()
                        ->whereKey($selectedPrimaryImageId)
                        ->lockForUpdate()
                        ->first();

                    if (! $primaryImage) {
                        throw new \RuntimeException(
                            'L’image principale sélectionnée est invalide.'
                        );
                    }

                    $product->images()->update([
                        'is_primary' => false,
                    ]);

                    $primaryImage->update([
                        'is_primary' => true,
                    ]);
                } else {
                    /*
                     * If no primary image is selected, guarantee that
                     * the gallery still has at most one primary image.
                     */
                    $primaryImage = $product->images()
                        ->where('is_primary', true)
                        ->lockForUpdate()
                        ->first();

                    /*
                     * If the previous primary image was deleted,
                     * promote the first remaining gallery image.
                     */
                    if (! $primaryImage) {
                        $fallbackImage = $product->images()
                            ->orderBy('id')
                            ->lockForUpdate()
                            ->first();

                        if ($fallbackImage) {
                            $fallbackImage->update([
                                'is_primary' => true,
                            ]);
                        }
                    }
                }
            });

            /*
             * The database transaction succeeded.
             *
             * Now it is safe to delete files that are no longer used.
             */

            if ($oldCoverPath && $oldCoverPath !== $newCoverPath) {
                Storage::disk('public')->delete($oldCoverPath);
            }

            foreach ($removedGalleryPaths as $path) {
                if ($path) {
                    Storage::disk('public')->delete($path);
                }
            }

            return $product->fresh([
                'category',
                'variants',
                'images',
            ]);
        } catch (Throwable $e) {
            /*
             * Database transaction failed.
             *
             * Delete every newly uploaded file because the DB changes
             * were rolled back.
             */
            if ($newCoverPath) {
                Storage::disk('public')->delete($newCoverPath);
            }

            foreach ($newGalleryPaths as $path) {
                Storage::disk('public')->delete($path);
            }

            throw $e;
        }
    }
}