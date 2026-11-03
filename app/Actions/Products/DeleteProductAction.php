<?php

namespace App\Actions\Products;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        $filesToDelete = [];

        DB::transaction(function () use ($product, &$filesToDelete) {
            /*
             * 1. Verrouiller la ligne du produit en premier.
             */
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            /*
             * Collecter les fichiers à supprimer pour ne les effacer
             * physiquement qu'après le commit réussi de la transaction SQL.
             */
            if ($lockedProduct->image) {
                $filesToDelete[] = $lockedProduct->image;
            }

            foreach ($lockedProduct->images as $image) {
                if ($image->path) {
                    $filesToDelete[] = $image->path;
                }
            }

            /*
             * 2. Récupérer les identifiants de variantes triés de façon
             *    déterministe, sur la base du produit verrouillé.
             */
            $variantIds = $lockedProduct->variants()
                ->orderBy('id')
                ->pluck('id');

            /*
             * 3. Verrouiller les variantes dans l'ordre croissant.
             */
            if ($variantIds->isNotEmpty()) {
                Variant::query()
                    ->whereIn('id', $variantIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
            }

            /*
             * 4. Vérifier s'il existe des commandes actives ou en cours (Pending, Paid ou Shipped).
             *
             * On bloque également 'Shipped' car CancelOrderAction permet d'annuler
             * une commande expédiée (ex: colis refusé ou retour client) afin de réintégrer
             * le stock dans la variante. Supprimer la variante pendant l'expédition corromprait
             * la restitution du stock lors d'un éventuel retour.
             */
            $hasActiveOrders = DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where(function ($query) use ($lockedProduct, $variantIds) {
                    $query->where('order_items.product_id', $lockedProduct->id);
                    if ($variantIds->isNotEmpty()) {
                        $query->orWhereIn('order_items.variant_id', $variantIds);
                    }
                })
                ->whereIn('orders.status', [
                    OrderStatus::Pending->value,
                    OrderStatus::Paid->value,
                    OrderStatus::Shipped->value,
                ])
                ->exists();

            if ($hasActiveOrders) {
                throw ValidationException::withMessages([
                    'product' => ['Impossible de supprimer un produit associé à des commandes actives ou expédiées (en attente, payées ou en cours de livraison).'],
                ]);
            }

            /*
             * 5. Supprimer les lignes d'images et le produit.
             */
            $lockedProduct->images()->delete();
            $lockedProduct->delete();
        }, 3);

        /*
         * 6. Suppression physique des fichiers après le commit réussi de la transaction.
         */
        foreach (array_unique($filesToDelete) as $filePath) {
            Storage::disk('public')->delete($filePath);
        }
    }
}
