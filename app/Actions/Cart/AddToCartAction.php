<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AddToCartAction
{
    public function __construct(private CartService $cartService) {}

    public function execute(Product $product, int $variantId): array
    {
        [$userId, $sessionId] = $this->cartService->owner();

        return DB::transaction(function () use ($product, $variantId, $userId, $sessionId) {
            // Verrou sur la variante : bloque toute autre requête concurrente
            // sur ce même article jusqu'à la fin de cette transaction.
            $variant = $this->cartService->withRowLock(Variant::with('product'))
                ->findOrFail($variantId);

            if ($variant->product_id !== $product->id) {
                abort(422, 'Cette variante ne correspond pas au produit sélectionné.');
            }

            if ($variant->stock <= 0) {
                abort(422, 'Cette taille est épuisée.');
            }

            $activeDrop = $variant->product->drops()->active()->first();

            if ($activeDrop) {
                if (Auth::guest()) {
                    abort(422, 'Connectez-vous pour acheter un produit de drop.');
                }

                $user = Auth::user();

                if (! $user instanceof User || ! $user->isWhitelistedForDrop($activeDrop)) {
                    abort(422, "Ce produit fait partie d'un drop privé. Faites une demande de whitelist pour y accéder.");
                }
            }

            $upcomingDrop = $variant->product->drops()->upcoming()->first();

            if ($upcomingDrop) {
                abort(422, "Ce produit fait partie d'un drop à venir et n'est pas encore disponible à l'achat.");
            }

            // Verrou aussi sur la ligne de panier existante, si elle existe déjà
            $cartItem = $this->cartService->withRowLock(
                CartItem::forOwner($userId, $sessionId)->where('variant_id', $variant->id)
            )->first();

            $quantity = ($cartItem->quantity ?? 0) + 1;

            if ($quantity > $variant->stock) {
                abort(422, 'Quantité demandée indisponible.');
            }

            if ($cartItem) {
                $cartItem->update(['quantity' => $quantity]);
            } else {
                CartItem::create([
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'variant_id' => $variant->id,
                    'quantity' => $quantity,
                ]);
            }

            return $this->cartService->summary();
        });
    }
}
