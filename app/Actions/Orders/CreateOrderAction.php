<?php

namespace App\Actions\Orders;

use App\Concerns\LocksRowsForUpdate;
use App\Enums\OrderStatus;
use App\Exceptions\CartChangedDuringCheckoutException;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Variant;
use App\Services\AlgeriaShippingService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    use LocksRowsForUpdate;

    private const MAX_CHECKOUT_ATTEMPTS = 3;

    public function execute(
        array $data,
        ?int $userId,
        ?string $sessionId,
        ?string $checkoutToken = null
    ): Order {
        for (
            $attempt = 1;
            $attempt <= self::MAX_CHECKOUT_ATTEMPTS;
            $attempt++
        ) {
            try {
                return DB::transaction(function () use (
                    $data,
                    $userId,
                    $sessionId,
                    $checkoutToken
                ): Order {
                    /*
                     * 1. Idempotence du checkout.
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
                     * 2. Lecture initiale du panier sans verrou.
                     */
                    $initialCartItems = CartItem::forOwner(
                        $userId,
                        $sessionId
                    )->get([
                        'id',
                        'variant_id',
                        'quantity',
                    ]);

                    if ($initialCartItems->isEmpty()) {
                        abort(422, 'Votre panier est vide.');
                    }

                    $initialCartItems = $initialCartItems
                        ->sortBy('variant_id')
                        ->values();

                    $initialCartState = $initialCartItems
                        ->map(fn (CartItem $item): array => [
                            'id' => (int) $item->id,
                            'variant_id' => (int) $item->variant_id,
                            'quantity' => (int) $item->quantity,
                        ])
                        ->values()
                        ->all();

                    $variantIds = $initialCartItems
                        ->pluck('variant_id')
                        ->map(fn ($id): int => (int) $id)
                        ->unique()
                        ->sort()
                        ->values();

                    /*
                     * 3. Verrouillage des variantes dans l'ordre croissant.
                     */
                    $lockedVariants = [];

                    foreach ($variantIds as $variantId) {
                        $variant = $this->withRowLock(
                            Variant::query()->with('product')
                        )->find($variantId);

                        if (! $variant || ! $variant->product) {
                            abort(
                                422,
                                "Un article de votre panier n'est plus disponible."
                            );
                        }

                        $lockedVariants[(int) $variant->id] = $variant;
                    }

                    /*
                     * 4. Verrouillage du panier après les variantes.
                     *
                     * Le tri est effectué dans la requête SQL,
                     * avant get(), afin de demander les verrous
                     * dans un ordre déterministe :
                     * variant_id, puis id.
                     */
                    $cartItems = $this->withRowLock(
                        CartItem::forOwner($userId, $sessionId)
                    )
                        ->orderBy('variant_id')
                        ->orderBy('id')
                        ->get()
                        ->values();

                    $lockedCartState = $cartItems
                        ->map(fn (CartItem $item): array => [
                            'id' => (int) $item->id,
                            'variant_id' => (int) $item->variant_id,
                            'quantity' => (int) $item->quantity,
                        ])
                        ->values()
                        ->all();

                    /*
                     * Si le panier a changé, la transaction est annulée
                     * et une nouvelle tentative sera effectuée.
                     */
                    if ($initialCartState !== $lockedCartState) {
                        throw new CartChangedDuringCheckoutException(
                            'Votre panier a été modifié pendant la validation.'
                        );
                    }

                    $total = 0;
                    $orderItemsData = [];
                    $quotaRequests = [];

                    /*
                     * 5. Validation du stock et des drops.
                     */
                    foreach ($cartItems as $cartItem) {
                        $variantId = (int) $cartItem->variant_id;
                        $variant = $lockedVariants[$variantId] ?? null;

                        if (! $variant || ! $variant->product) {
                            abort(
                                422,
                                "Un article de votre panier n'est plus disponible."
                            );
                        }

                        $quantity = (int) $cartItem->quantity;

                        if ($quantity <= 0) {
                            abort(
                                422,
                                'La quantité demandée est invalide.'
                            );
                        }

                        if ($variant->stock < $quantity) {
                            abort(
                                422,
                                "Stock insuffisant pour {$variant->product->name} ({$variant->size})."
                            );
                        }

                        $product = $variant->product;
                        $activeDrop = $product->activeDrop();

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

                            $quotaKey = $activeDrop->id.':'.$product->id;

                            $quotaRequests[$quotaKey] =
                                ($quotaRequests[$quotaKey] ?? 0)
                                + $quantity;
                        }

                        $upcomingDrop = $product->upcomingDrop();

                        if ($upcomingDrop) {
                            abort(
                                403,
                                "Ce produit fait partie d'un drop à venir et n'est pas encore disponible à l'achat."
                            );
                        }

                        $price = $product->price;

                        $total += $price * $quantity;

                        $orderItemsData[] = [
                            'drop_id' => $activeDrop?->id,
                            'product_id' => $product->id,
                            'variant_id' => $variant->id,
                            'quantity' => $quantity,
                            'price' => $price,
                            'variant_sku' => $variant->sku,
                            'variant_size' => $variant->size,
                            'variant_color' => $variant->color,
                            'product_name' => $product->name,
                        ];
                    }

                    /*
                     * 6. Verrouillage et validation des quotas.
                     */
                    ksort($quotaRequests, SORT_NATURAL);

                    foreach ($quotaRequests as $quotaKey => $requestedQuantity) {
                        [$dropId, $productId] = array_map(
                            'intval',
                            explode(':', $quotaKey, 2)
                        );

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

                        if (
                            $dropProduct->quota === null
                            || (int) $dropProduct->quota <= 0
                        ) {
                            continue;
                        }

                        $reservedQuantity = (int) DB::table('order_items')
                            ->join(
                                'orders',
                                'orders.id',
                                '=',
                                'order_items.order_id'
                            )
                            ->where('order_items.drop_id', $dropId)
                            ->where('order_items.product_id', $productId)
                            ->whereIn(
                                'orders.status',
                                $this->quotaReservedStatuses()
                            )
                            ->lockForUpdate()
                            ->get(['order_items.quantity'])
                            ->sum('quantity');

                        $remainingQuantity = max(
                            0,
                            (int) $dropProduct->quota - $reservedQuantity
                        );

                        if ($requestedQuantity > $remainingQuantity) {
                            abort(
                                422,
                                "Le quota disponible pour ce produit est insuffisant. Il reste {$remainingQuantity} article(s)."
                            );
                        }
                    }

                    /*
                     * 7. Décrémentation du stock.
                     */
                    foreach ($cartItems as $cartItem) {
                        $variant = $lockedVariants[
                            (int) $cartItem->variant_id
                        ];

                        $variant->decrement(
                            'stock',
                            (int) $cartItem->quantity
                        );
                    }

                    /*
                     * 8. Création de la commande.
                     */
                    $deliveryType = $data['delivery_type'] ?? 'home';
                    $shippingCost = (new AlgeriaShippingService)
                        ->calculateShippingFee($data['wilaya'] ?? null, $deliveryType);

                    $order = Order::create([
                        'user_id' => $userId,
                        'full_name' => $data['full_name'],
                        'phone' => $data['phone'],
                        'address' => $data['address'],
                        'wilaya' => $data['wilaya'],
                        'commune' => $data['commune'] ?? null,
                        'delivery_type' => $deliveryType,
                        'shipping_cost' => $shippingCost,
                        'payment_method' => $data['payment_method'],
                        'status' => OrderStatus::Pending,
                        'checkout_token' => $checkoutToken,
                        'total' => $total,
                    ]);

                    /*
                     * 9. Création des snapshots des articles.
                     */
                    foreach ($orderItemsData as $item) {
                        $order->items()->create($item);
                    }

                    /*
                     * 10. Suppression du panier.
                     */
                    CartItem::forOwner(
                        $userId,
                        $sessionId
                    )->delete();

                    return $order->fresh(['items']);
                }, 3);
            } catch (CartChangedDuringCheckoutException $exception) {
                if ($attempt === self::MAX_CHECKOUT_ATTEMPTS) {
                    abort(
                        409,
                        'Votre panier a été modifié pendant la validation. Veuillez réessayer.'
                    );
                }
            } catch (QueryException $exception) {
                /*
                 * Gestion d'une collision sur checkout_token.
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

        abort(
            409,
            'Votre panier a été modifié pendant la validation. Veuillez réessayer.'
        );
    }

    /**
     * Statuts qui réservent le quota du drop.
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

    /**
     * Recherche une commande existante associée au token.
     */
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
