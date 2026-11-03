<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use App\Models\Variant;
use App\Services\CartService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UpdateCartAction
{
    private const MAX_TRANSACTION_ATTEMPTS = 3;

    public function __construct(private CartService $cartService) {}

    public function execute(int $variantId, int $quantity): array
    {
        if (Auth::check() && Auth::user()->is_admin) {
            abort(403, 'Les comptes administrateurs ne peuvent pas modifier un panier.');
        }

        if ($quantity < 1) {
            abort(422, 'La quantité demandée est invalide.');
        }

        [$userId, $sessionId] = $this->cartService->owner();

        try {
            return DB::transaction(function () use (
                $variantId,
                $quantity,
                $userId,
                $sessionId
            ) {
                /*
                 * 1. Verrouillage de la variante.
                 */
                $variant = $this->cartService
                    ->withRowLock(Variant::with('product'))
                    ->findOrFail($variantId);

                if (! $variant->product) {
                    abort(404, 'Produit introuvable pour cette variante.');
                }

                /*
                 * 2. Le propriétaire du panier a été identifié
                 * avant la transaction.
                 */

                /*
                 * 3. Verrouillage de la ligne du panier après la variante.
                 */
                $cartItem = $this->cartService->withRowLock(
                    CartItem::forOwner($userId, $sessionId)
                        ->where('variant_id', $variant->id)
                )->first();

                if (! $cartItem) {
                    abort(
                        403,
                        'Vous ne pouvez pas modifier le panier d\'un autre utilisateur.'
                    );
                }

                if (Auth::check() && $cartItem->user_id !== Auth::id()) {
                    abort(
                        403,
                        'Vous ne pouvez pas modifier le panier d\'un autre utilisateur.'
                    );
                }

                /*
                 * 4. Vérification du stock.
                 */
                if ($quantity > $variant->stock) {
                    abort(422, 'Quantité demandée indisponible.');
                }

                /*
                 * 5. Mise à jour de la quantité.
                 */
                $cartItem->update([
                    'quantity' => $quantity,
                ]);

                $subtotal = $variant->product->price * $quantity;
                $summary = $this->cartService->summary();

                return [
                    'subtotal' => number_format($subtotal, 0).' DA',
                    'total' => $summary['total'],
                    'count' => $summary['count'],
                ];
            }, self::MAX_TRANSACTION_ATTEMPTS);
        } catch (QueryException $exception) {
            $sqlState = $exception->errorInfo[0] ?? $exception->getCode();
            $sqlErrorCode = (int) ($exception->errorInfo[1] ?? 0);

            if (
                $sqlState === '40001'
                || in_array($sqlErrorCode, [1205, 1213], true)
            ) {
                abort(
                    409,
                    'Votre panier est en cours de modification. Veuillez réessayer.'
                );
            }

            throw $exception;
        }
    }
}
