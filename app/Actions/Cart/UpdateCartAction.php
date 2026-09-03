<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use App\Models\Variant;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateCartAction
{
    public function __construct(private CartService $cartService) {}

    public function execute(int $variantId, int $quantity): array
    {
        return DB::transaction(function () use ($variantId, $quantity) {
            $variant = $this->cartService->withRowLock(Variant::with('product'))
                ->findOrFail($variantId);

            if (! $variant->product) {
                abort(404, 'Produit introuvable pour cette variante.');
            }

            [$userId, $sessionId] = $this->cartService->owner();

            $cartItem = $this->cartService->withRowLock(
                CartItem::forOwner($userId, $sessionId)->where('variant_id', $variant->id)
            )->first();

            if (! $cartItem) {
                abort(403, 'Vous ne pouvez pas modifier le panier d\'un autre utilisateur.');
            }

            if (Auth::check() && $cartItem->user_id !== Auth::id()) {
                abort(403, 'Vous ne pouvez pas modifier le panier d\'un autre utilisateur.');
            }

            if ($quantity > $variant->stock) {
                abort(422, 'Quantité demandée indisponible.');
            }

            $cartItem->update(['quantity' => $quantity]);

            $subtotal = $variant->product->price * $quantity;
            $summary = $this->cartService->summary();

            return [
                'subtotal' => number_format($subtotal, 0).' DA',
                'total' => $summary['total'],
                'count' => $summary['count'],
            ];
        });
    }
}