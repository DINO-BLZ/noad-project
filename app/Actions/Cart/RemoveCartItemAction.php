<?php

namespace App\Actions\Cart;

use App\Models\CartItem;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

class RemoveCartItemAction
{
    public function __construct(private CartService $cartService) {}

    public function execute(int $variantId): array
    {
        if (Auth::check() && Auth::user()->is_admin) {
            abort(403, 'Les comptes administrateurs ne peuvent pas modifier un panier.');
        }

        [$userId, $sessionId] = $this->cartService->owner();

        CartItem::forOwner($userId, $sessionId)
            ->where('variant_id', $variantId)
            ->delete();

        $summary = $this->cartService->summary();

        return [
            'total' => $summary['total'],
            'count' => $summary['count'],
            'empty' => count($summary['items']) === 0,
        ];
    }
}
