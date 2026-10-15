<?php

namespace App\Actions\Products;

use App\Enums\OrderStatus;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteProductAction
{
    public function execute(Product $product): void
    {
        DB::transaction(function () use ($product) {
            /*
             * 1. Verrouiller la ligne du produit en premier.
             *
             * Ce verrou sérialise toute opération concurrente sur ce produit
             * (autre suppression, UpdateProductAction, etc.).
             *
             * Il doit être acquis AVANT les variantes pour respecter la
             * hiérarchie globale des verrous du projet :
             *
             *   Product (FOR UPDATE)
             *     └── Variants (FOR UPDATE, par ID croissant)
             *
             * UpdateProductAction doit également acquérir ce verrou en premier
             * pour que la sérialisation soit effective (voir UpdateProductAction).
             *
             * Pourquoi ce verrou est nécessaire même si le produit n'a aucune
             * variante : sans lui, deux suppressions concurrentes du même produit
             * sans variantes passeraient toutes les deux la vérification
             * hasActiveOrders avant de supprimer le produit.
             *
             * De plus, sans ce verrou, UpdateProductAction pourrait créer une
             * nouvelle variante entre la lecture des variantes existantes et la
             * vérification des commandes actives, rendant la vérification
             * potentiellement incomplète.
             *
             * CreateOrderAction ne verrouille jamais Product, donc ce verrou
             * ne crée aucun cycle.
             */
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            /*
             * 2. Récupérer les identifiants de variantes triés de façon
             *    déterministe, sur la base du produit verrouillé.
             */
            $variantIds = $lockedProduct->variants()
                ->orderBy('id')
                ->pluck('id');

            /*
             * 3. Verrouiller les variantes dans l'ordre croissant.
             *
             * Aligné sur CreateOrderAction & CancelOrderAction.
             *
             * Pourquoi lockForUpdate() est suffisant ici et pourquoi il n'est
             * pas nécessaire sur hasActiveOrders :
             *
             * CreateOrderAction verrouille les variantes (FOR UPDATE) AVANT de
             * créer une commande. Si DeleteProductAction tient déjà le verrou
             * sur ces variantes, CreateOrderAction est bloqué et ne peut pas
             * créer de commande pour ces variantes. Donc, une fois les variantes
             * verrouillées, la lecture hasActiveOrders est cohérente et stable.
             *
             * Ajouter lockForUpdate() sur hasActiveOrders (jointure
             * order_items + orders) créerait un risque de deadlock avec
             * CancelOrderAction (Variant → Order), ce qui est interdit par la
             * hiérarchie des verrous.
             */
            if ($variantIds->isNotEmpty()) {
                Variant::query()
                    ->whereIn('id', $variantIds)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();
            }

            /*
             * 4. Vérifier s'il existe des commandes actives (Pending ou Paid).
             *
             * Lecture NON verrouillante (pas de lockForUpdate), justifiée
             * ci-dessus.
             *
             * La condition sur product_id couvre le cas d'un produit sans
             * variante : si une commande existait quand une variante était
             * présente et que la variante a depuis été supprimée (nullOnDelete),
             * l'order_item conserve product_id mais a variant_id = NULL.
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
                ])
                ->exists();

            if ($hasActiveOrders) {
                throw ValidationException::withMessages([
                    'product' => ['Impossible de supprimer un produit associé à des commandes actives (en attente ou payées).'],
                ]);
            }

            /*
             * 5. Supprimer le produit.
             *
             * On supprime via $lockedProduct (instance fraîche et verrouillée)
             * plutôt que $product (instance passée en paramètre, potentiellement
             * périmée si un UPDATE concurrent a eu lieu avant l'entrée en
             * transaction).
             *
             * Déclenche la suppression en cascade des variantes
             * (cascadeOnDelete sur variants.product_id) et le nettoyage des
             * images via les observers/événements du modèle Product.
             */
            $lockedProduct->delete();
        });
    }
}
