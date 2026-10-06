<?php

namespace App\Actions\Orders;

use App\Concerns\LocksRowsForUpdate;
use App\Enums\OrderStatus;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Variant;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    use LocksRowsForUpdate;

    public function execute(
        array $data,
        ?int $userId,
        ?string $sessionId,
        ?string $checkoutToken = null
    ): Order {
        try {
            return DB::transaction(function () use (
                $data,
                $userId,
                $sessionId,
                $checkoutToken
            ) {
                /*
                 * Idempotence checkout.
                 */
                if ($checkoutToken) {
                    $existingOrder = $this->orderForCheckoutToken(
                        $checkoutToken,
                        $userId
                    );

                    if ($existingOrder) {
                        return $existingOrder->fresh(['items']);
                    }
                }

                /*
                 * Verrouille les lignes du panier pendant tout le checkout.
                 */
                $cartItems = $this->withRowLock(
                    CartItem::forOwner($userId, $sessionId)
                        ->with('variant.product')
                )->get();

                if ($cartItems->isEmpty()) {
                    abort(422, 'Votre panier est vide.');
                }

                /*
                 * Toujours verrouiller les variantes dans le même ordre.
                 *
                 * Cela réduit fortement le risque de deadlock lorsqu'un
                 * checkout contient plusieurs variantes.
                 */
                $cartItems = $cartItems
                    ->sortBy('variant_id')
                    ->values();

                $total = 0;
                $orderItemsData = [];

                /*
                 * Variantes déjà verrouillées.
                 *
                 * [variant_id => Variant]
                 */
                $lockedVariants = [];

                /*
                 * Quantités demandées par couple :
                 *
                 * drop_id:product_id => quantité demandée
                 *
                 * Le quota appartient au produit dans le drop,
                 * pas à la variante.
                 */
                $quotaRequests = [];

                /*
                 * ==========================================================
                 * 1. VERROUILLAGE + VALIDATION DES VARIANTES
                 * ==========================================================
                 */
                foreach ($cartItems as $cartItem) {
                    /** @var Variant|null $variant */
                    $variant = $this->withRowLock(
                        Variant::query()
                            ->with('product')
                    )->find($cartItem->variant_id);

                    if (! $variant || ! $variant->product) {
                        abort(
                            422,
                            "Un article de votre panier n'est plus disponible."
                        );
                    }

                    /*
                     * Le stock est contrôlé sur la ligne verrouillée.
                     */
                    if ($variant->stock < $cartItem->quantity) {
                        abort(
                            422,
                            "Stock insuffisant pour {$variant->product->name} ({$variant->size})."
                        );
                    }

                    /*
                     * Détermine le drop actif.
                     */
                    $activeDrop = $variant->product->activeDrop();

                    /*
                     * Produit appartenant à un drop actif :
                     * whitelist obligatoire.
                     */
                    if ($activeDrop) {
                        if (
                            ! Auth::check()
                            || ! Auth::user()->isWhitelistedForDrop($activeDrop)
                        ) {
                            abort(
                                403,
                                "Vous n'êtes pas autorisé à acheter ce produit de drop."
                            );
                        }

                        /*
                         * Le quota est partagé entre toutes les variantes
                         * du même produit dans le même drop.
                         */
                        $quotaKey = $activeDrop->id . ':' . $variant->product_id;

                        $quotaRequests[$quotaKey] =
                            ($quotaRequests[$quotaKey] ?? 0)
                            + $cartItem->quantity;
                    }

                    /*
                     * Produit d'un drop futur :
                     * impossible à acheter maintenant.
                     */
                    $upcomingDrop = $variant->product->upcomingDrop();

                    if ($upcomingDrop) {
                        abort(
                            403,
                            "Ce produit fait partie d'un drop à venir et n'est pas encore disponible à l'achat."
                        );
                    }

                    $price = $variant->product->price;
                    $subtotal = $price * $cartItem->quantity;

                    $total += $subtotal;

                    $orderItemsData[] = [
                        'drop_id' => $activeDrop?->id,
                        'product_id' => $variant->product_id,
                        'variant_id' => $variant->id,
                        'quantity' => $cartItem->quantity,
                        'price' => $price,
                        'variant_sku' => $variant->sku,
                        'variant_size' => $variant->size,
                        'variant_color' => $variant->color,
                        'product_name' => $variant->product->name,
                    ];

                    $lockedVariants[$variant->id] = $variant;
                }

                /*
                 * ==========================================================
                 * 2. VERROUILLAGE + VALIDATION DES QUOTAS
                 * ==========================================================
                 *
                 * Très important :
                 *
                 * variant.stock
                 *       ↓
                 * drop_product.quota
                 *
                 * Le quota est partagé entre les variantes.
                 *
                 * Exemple :
                 *
                 * quota produit = 10
                 *
                 * M = 6
                 * L = 6
                 *
                 * Deux checkouts concurrents ne doivent PAS permettre
                 * 12 ventes.
                 */

                /*
                 * Ordre déterministe des verrous de quota.
                 *
                 * Cela évite qu'un checkout A verrouille :
                 *   drop 1 / produit 2
                 *
                 * pendant qu'un checkout B verrouille :
                 *   drop 1 / produit 1
                 *
                 * dans l'ordre inverse.
                 */
                ksort($quotaRequests, SORT_NATURAL);

                foreach ($quotaRequests as $quotaKey => $requestedQuantity) {
                    [$dropId, $productId] = array_map(
                        'intval',
                        explode(':', $quotaKey, 2)
                    );

                    /*
                     * Verrouille LA ligne qui porte réellement le quota.
                     *
                     * C'est cette ligne qu'il faut verrouiller,
                     * pas seulement la variante.
                     */
                    $dropProduct = DB::table('drop_product')
                        ->where('drop_id', $dropId)
                        ->where('product_id', $productId)
                        ->lockForUpdate()
                        ->first();

                    if (! $dropProduct) {
                        abort(
                            422,
                            "La configuration du drop n'est plus disponible."
                        );
                    }

                    /*
                     * Dans le projet actuel :
                     *
                     * NULL / 0 = aucun quota défini.
                     *
                     * Ces produits ne sont donc pas limités par le quota.
                     */
                    if (
                        $dropProduct->quota === null
                        || (int) $dropProduct->quota <= 0
                    ) {
                        continue;
                    }

                    /*
                     * Commandes qui réservent le quota.
                     *
                     * Pending est inclus parce que le checkout réserve
                     * immédiatement le stock.
                     *
                     * Cancelled libère le quota.
                     */
                    $reservedQuantity = (int) DB::table('order_items')
                        ->join(
                            'orders',
                            'orders.id',
                            '=',
                            'order_items.order_id'
                        )
                        ->where(
                            'order_items.drop_id',
                            $dropId
                        )
                        ->where(
                            'order_items.product_id',
                            $productId
                        )
                        ->whereIn(
                            'orders.status',
                            $this->quotaReservedStatuses()
                        )
                        /*
                         * Lecture verrouillante.
                         *
                         * C'est important avec MySQL :
                         * après avoir attendu le verrou du pivot,
                         * on veut lire les données validées les plus
                         * récentes, même sous REPEATABLE READ.
                         */
                        ->lockForUpdate()
                        ->get([
                            'order_items.quantity',
                        ])
                        ->sum('quantity');

                    $quota = (int) $dropProduct->quota;

                    $remainingQuantity = max(
                        0,
                        $quota - $reservedQuantity
                    );

                    if ($requestedQuantity > $remainingQuantity) {
                        abort(
                            422,
                            "Le quota disponible pour ce produit est insuffisant. Il reste {$remainingQuantity} article(s)."
                        );
                    }
                }

                /*
                 * ==========================================================
                 * 3. DÉCRÉMENTATION DU STOCK
                 * ==========================================================
                 *
                 * Le quota est maintenant validé.
                 *
                 * Les variantes sont déjà verrouillées.
                 */
                foreach ($cartItems as $cartItem) {
                    $variant = $lockedVariants[$cartItem->variant_id];

                    $variant->decrement(
                        'stock',
                        $cartItem->quantity
                    );
                }

                /*
                 * ==========================================================
                 * 4. CRÉATION DE LA COMMANDE
                 * ==========================================================
                 */
                $order = Order::create([
                    'user_id' => $userId,
                    'full_name' => $data['full_name'],
                    'phone' => $data['phone'],
                    'address' => $data['address'],
                    'wilaya' => $data['wilaya'],
                    'payment_method' => $data['payment_method'],
                    'status' => OrderStatus::Pending,
                    'checkout_token' => $checkoutToken,
                    'total' => $total,
                ]);

                /*
                 * ==========================================================
                 * 5. SNAPSHOTS DES ARTICLES
                 * ==========================================================
                 */
                foreach ($orderItemsData as $item) {
                    $order->items()->create($item);
                }

                /*
                 * ==========================================================
                 * 6. SUPPRESSION DU PANIER
                 * ==========================================================
                 */
                CartItem::forOwner(
                    $userId,
                    $sessionId
                )->delete();

                return $order->fresh(['items']);
            }, 3);
        } catch (QueryException $exception) {
            /*
             * Gestion de l'idempotence checkout_token.
             */
            if (
                $checkoutToken
                && $exception->getCode() === '23000'
            ) {
                $existingOrder = $this->orderForCheckoutToken(
                    $checkoutToken,
                    $userId
                );

                if ($existingOrder) {
                    return $existingOrder->fresh(['items']);
                }

                abort(
                    422,
                    "Cette session de commande n'est plus valide."
                );
            }

            throw $exception;
        }
    }

    /**
     * Statuts qui réservent le quota.
     *
     * Une commande Pending réserve déjà le stock.
     * Elle doit donc également réserver le quota.
     *
     * Une commande Cancelled ne réserve plus le quota.
     */
    private function quotaReservedStatuses(): array
    {
        return [
            OrderStatus::Pending->value,
            OrderStatus::Paid->value,
            OrderStatus::Shipped->value,
            OrderStatus::Delivered->value,
        ];
    }

    private function orderForCheckoutToken(
        string $checkoutToken,
        ?int $userId
    ): ?Order {
        return Order::query()
            ->where('checkout_token', $checkoutToken)
            ->where('user_id', $userId)
            ->first();
    }
}