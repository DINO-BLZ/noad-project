<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Variant;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = $this->cartItemsQuery()->with('variant.product')->get();

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

        return view('cart.index', compact('items', 'total'));
    }

    public function add(AddToCartRequest $request, Product $product)
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return response()->json(['message' => 'Les comptes administrateurs ne peuvent pas effectuer d\'achats.'], 403);
        }

        [$userId, $sessionId] = $this->owner();

        try {
            $summary = DB::transaction(function () use ($request, $product, $userId, $sessionId) {
                // Verrou sur la variante : bloque toute autre requête concurrente
                // sur ce même article jusqu'à la fin de cette transaction.
                $variant = Variant::with('product')->lockForUpdate()->findOrFail($request->variant_id);

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

                    if (! $user instanceof \App\Models\User || ! $user->isWhitelistedForDrop($activeDrop)) {
                        abort(422, "Ce produit fait partie d'un drop privé. Faites une demande de whitelist pour y accéder.");
                    }
                }

                $upcomingDrop = $variant->product->drops()->upcoming()->first();

                if ($upcomingDrop) {
                    abort(422, "Ce produit fait partie d'un drop à venir et n'est pas encore disponible à l'achat.");
                }

                // Verrou aussi sur la ligne de panier existante, si elle existe déjà
                $cartItem = CartItem::forOwner($userId, $sessionId)
                    ->where('variant_id', $variant->id)
                    ->lockForUpdate()
                    ->first();

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

                return $this->cartSummary();
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json($summary);
    }

    public function update(UpdateCartItemRequest $request, $variantId)
    {
        $variant = Variant::findOrFail($variantId);

        [$userId, $sessionId] = $this->owner();

        CartItem::forOwner($userId, $sessionId)
            ->where('variant_id', $variantId)
            ->update(['quantity' => $request->quantity]);

        $subtotal = $variant->product->price * $request->quantity;
        $summary = $this->cartSummary();

        return response()->json([
            'subtotal' => number_format($subtotal, 0) . ' DA',
            'total' => $summary['total'],
            'count' => $summary['count'],
        ]);
    }

    public function remove($variantId)
    {
        [$userId, $sessionId] = $this->owner();

        CartItem::forOwner($userId, $sessionId)
            ->where('variant_id', $variantId)
            ->delete();

        $summary = $this->cartSummary();

        return response()->json([
            'total' => $summary['total'],
            'count' => $summary['count'],
            'empty' => count($summary['items']) === 0,
        ]);
    }

    /**
     * Construit le résumé complet du panier courant : articles (avec image,
     * nom, taille, quantité, sous-total), total général, et nombre total
     * d'articles (utilisé pour le badge de notification).
     */
    private function cartSummary(): array
    {
        $cartItems = $this->cartItemsQuery()->with('variant.product')->get();

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
                'subtotal' => number_format($subtotal, 0) . ' DA',
                'image' => $cartItem->variant->product->image
                    ? asset('storage/' . $cartItem->variant->product->image)
                    : asset('images/placeholder.png'),
            ];
        }

        return [
            'items' => $items,
            'total' => number_format($total, 0) . ' DA',
            'count' => $count,
        ];
    }

    private function owner(): array
    {
        if (Auth::check()) {
            return [Auth::id(), null];
        }

        return [null, session()->getId()];
    }

    private function cartItemsQuery()
    {
        [$userId, $sessionId] = $this->owner();

        return CartItem::forOwner($userId, $sessionId);
    }
}