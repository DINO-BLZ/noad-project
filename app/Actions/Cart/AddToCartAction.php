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
    public function __construct(private CartService $cartService)
    {
    }

    public function execute(
        Product $product,
        int $variantId,
        int $quantity = 1
    ): array {
        if ($quantity < 1 || $quantity > 99) {
            abort(422, 'La quantité demandée est invalide.');
        }

        [$userId, $sessionId] = $this->cartService->owner();

        return DB::transaction(function () use (
            $product,
            $variantId,
            $userId,
            $sessionId,
            $quantity
        ) {
            /*
             * Verrouille la variante pendant la transaction.
             * Cela évite les problèmes de concurrence sur le stock.
             */
            $variant = $this->cartService
                ->withRowLock(Variant::with('product'))
                ->findOrFail($variantId);

            /*
             * Sécurité : la variante envoyée doit appartenir
             * au produit demandé dans l'URL.
             */
            if ($variant->product_id !== $product->id) {
                abort(
                    422,
                    'Cette variante ne correspond pas au produit sélectionné.'
                );
            }

            if ($variant->stock <= 0) {
                abort(422, 'Cette taille est épuisée.');
            }

            /*
             * Si le produit appartient à un Drop actif,
             * l'utilisateur doit être authentifié et whitelisté.
             */
            $activeDrop = $variant->product
                ->drops()
                ->active()
                ->first();

            if ($activeDrop) {
                if (Auth::guest()) {
                    abort(
                        422,
                        'Connectez-vous pour acheter un produit de drop.'
                    );
                }

                $user = Auth::user();

                if (
                    ! $user instanceof User ||
                    ! $user->isWhitelistedForDrop($activeDrop)
                ) {
                    abort(
                        422,
                        "Ce produit fait partie d'un drop privé. Faites une demande de whitelist pour y accéder."
                    );
                }
            }

            /*
             * Un produit appartenant à un Drop futur
             * ne doit pas être achetable avant son ouverture.
             */
            $upcomingDrop = $variant->product
                ->drops()
                ->upcoming()
                ->first();

            if ($upcomingDrop) {
                abort(
                    422,
                    "Ce produit fait partie d'un drop à venir et n'est pas encore disponible à l'achat."
                );
            }

            /*
             * Verrouille également l'éventuelle ligne panier
             * existante pour éviter une course sur la quantité.
             */
            $cartItem = $this->cartService
                ->withRowLock(
                    CartItem::forOwner($userId, $sessionId)
                        ->where('variant_id', $variant->id)
                )
                ->first();

            $newQuantity = ($cartItem->quantity ?? 0) + $quantity;

            if ($newQuantity > $variant->stock) {
                abort(422, 'Quantité demandée indisponible.');
            }

            if ($cartItem) {
                $cartItem->update([
                    'quantity' => $newQuantity,
                ]);
            } else {
                CartItem::create([
                    'user_id' => $userId,
                    'session_id' => $sessionId,
                    'variant_id' => $variant->id,
                    'quantity' => $newQuantity,
                ]);
            }

            return $this->cartService->summary();
        });
    }
}