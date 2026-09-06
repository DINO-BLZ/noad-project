<?php

namespace App\Services;

use App\Concerns\LocksRowsForUpdate;
use App\Models\CartItem;
use Illuminate\Support\Facades\Auth;

class CartService
{
    use LocksRowsForUpdate;

    /**
     * Retourne [userId, sessionId] : userId si connecté, sinon
     * sessionId pour identifier le panier invité.
     */
    public function owner(): array
    {
        if (Auth::check()) {
            return [Auth::id(), null];
        }

        return [null, session()->getId()];
    }

    public function itemsQuery()
    {
        [$userId, $sessionId] = $this->owner();

        return CartItem::forOwner($userId, $sessionId);
    }

    /**
     * Résumé formaté du panier (utilisé pour les réponses JSON des
     * actions add/update/remove et pour le badge de notification).
     */
    public function summary(): array
    {
        $cartItems = $this->itemsQuery()->with('variant.product')->get();

        $items = [];
        $total = 0;
        $count = 0;

        foreach ($cartItems as $cartItem) {
            $subtotal = $cartItem->variant->product->price * $cartItem->quantity;
            $total += $subtotal;
            $count += $cartItem->quantity;

            $items[] = [
                'name' => $cartItem->variant->product->name,
                'size' => $cartItem->variant->size,
                'quantity' => $cartItem->quantity,
                'subtotal' => number_format($subtotal, 0).' DA',
                'image' => $cartItem->variant->product->image
                    ? asset('storage/'.$cartItem->variant->product->image)
                    : asset('images/placeholder.png'),
            ];
        }

        return [
            'items' => $items,
            'total' => number_format($total, 0).' DA',
            'count' => $count,
        ];
    }

    /**
     * Articles bruts (avec l'objet variant complet) pour l'affichage
     * de la page panier (resources/views/cart/index.blade.php).
     */
    public function displayItems(): array
    {
        $cartItems = $this->itemsQuery()->with('variant.product')->get();

        $items = [];
        $total = 0;

        foreach ($cartItems as $cartItem) {
            $subtotal = $cartItem->variant->product->price * $cartItem->quantity;
            $total += $subtotal;

            $items[] = [
                'variant' => $cartItem->variant,
                'quantity' => $cartItem->quantity,
                'subtotal' => $subtotal,
            ];
        }

        return ['items' => $items, 'total' => $total];
    }
}
