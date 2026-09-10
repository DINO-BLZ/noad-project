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

    public function execute(array $data, ?int $userId, ?string $sessionId, ?string $checkoutToken = null): Order
    {
        try {
            return DB::transaction(function () use ($data, $userId, $sessionId, $checkoutToken) {
                if ($checkoutToken) {
                    $existingOrder = $this->orderForCheckoutToken($checkoutToken, $userId);

                    if ($existingOrder) {
                        return $existingOrder->fresh(['items']);
                    }
                }

                $cartItems = $this->withRowLock(
                    CartItem::forOwner($userId, $sessionId)->with('variant.product')
                )->get();

                if ($cartItems->isEmpty()) {
                    abort(422, 'Votre panier est vide.');
                }

                $total = 0;
                $orderItemsData = [];

                foreach ($cartItems as $cartItem) {
                    $variant = $this->withRowLock(Variant::with('product'))
                        ->find($cartItem->variant_id);

                    if (! $variant || ! $variant->product) {
                        abort(422, "Un article de votre panier n'est plus disponible.");
                    }

                    if ($variant->stock < $cartItem->quantity) {
                        abort(422, "Stock insuffisant pour {$variant->product->name} ({$variant->size}).");
                    }

                    $activeDrop = $variant->product->activeDrop();

                    if ($activeDrop) {
                        if (! Auth::check() || ! Auth::user()->isWhitelistedForDrop($activeDrop)) {
                            abort(403, "Vous n'êtes pas autorisé à acheter ce produit de drop.");
                        }
                    }

                    $upcomingDrop = $variant->product->upcomingDrop();

                    if ($upcomingDrop) {
                        abort(403, "Ce produit fait partie d'un drop à venir et n'est pas encore disponible à l'achat.");
                    }

                    $price = $variant->product->price;
                    $subtotal = $price * $cartItem->quantity;
                    $total += $subtotal;

                    $orderItemsData[] = [
                        'variant_id' => $variant->id,
                        'quantity' => $cartItem->quantity,
                        'price' => $price,
                        'variant_sku' => $variant->sku,
                        'variant_size' => $variant->size,
                        'variant_color' => $variant->color,
                        'product_name' => $variant->product->name,
                    ];

                    $variant->decrement('stock', $cartItem->quantity);
                }

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

                foreach ($orderItemsData as $item) {
                    $order->items()->create($item);
                }

                CartItem::forOwner($userId, $sessionId)->delete();

                return $order->fresh(['items']);
            });
        } catch (QueryException $exception) {
            if ($checkoutToken && $exception->getCode() === '23000') {
                $existingOrder = $this->orderForCheckoutToken($checkoutToken, $userId);

                if ($existingOrder) {
                    return $existingOrder->fresh(['items']);
                }

                abort(422, "Cette session de commande n'est plus valide.");
            }

            throw $exception;
        }
    }

    private function orderForCheckoutToken(string $checkoutToken, ?int $userId): ?Order
    {
        return Order::query()
            ->where('checkout_token', $checkoutToken)
            ->where('user_id', $userId)
            ->first();
    }
}
