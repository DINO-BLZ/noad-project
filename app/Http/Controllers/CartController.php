<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
                $variant = $this->withRowLock(Variant::with('product'))
                    ->findOrFail($request->variant_id);

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
                $cartItem = $this->withRowLock(
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

                return $this->cartSummary();
            });
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json($summary);
    }

    public function update(UpdateCartItemRequest $request, $variantId)
    {
        try {
            $summary = DB::transaction(function () use ($request, $variantId) {
                $variant = $this->withRowLock(Variant::with('product'))
                    ->findOrFail($variantId);

                if (! $variant->product) {
                    abort(404, 'Produit introuvable pour cette variante.');
                }

                [$userId, $sessionId] = $this->owner();

                $cartItem = $this->withRowLock(
                    CartItem::forOwner($userId, $sessionId)->where('variant_id', $variant->id)
                )->first();

                if (! $cartItem) {
                    abort(403, 'Vous ne pouvez pas modifier le panier d\'un autre utilisateur.');
                }

                if (Auth::check() && $cartItem->user_id !== Auth::id()) {
                    abort(403, 'Vous ne pouvez pas modifier le panier d\'un autre utilisateur.');
                }

                if ($request->quantity > $variant->stock) {
                    abort(422, 'Quantité demandée indisponible.');
                }

                $cartItem->update(['quantity' => $request->quantity]);

                $subtotal = $variant->product->price * $request->quantity;
                $summary = $this->cartSummary();

                return [
                    'subtotal' => number_format($subtotal, 0).' DA',
                    'total' => $summary['total'],
                    'count' => $summary['count'],
                ];
            });
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json($summary);
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

    private function withRowLock($query)
    {
        if (config('database.default') === 'sqlite') {
            return $query;
        }

        return $query->lockForUpdate();
    }
}
