<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function add(Request $request, Product $product)
    {
        $request->validate([
            'variant_id' => 'required|exists:variants,id',
        ]);

        $variant = Variant::findOrFail($request->variant_id);

        if ($variant->product_id !== $product->id) {
            return response()->json(['message' => 'Cette variante ne correspond pas au produit sélectionné.'], 422);
        }

        if ($variant->stock <= 0) {
            return response()->json(['message' => 'Cette taille est épuisée.'], 422);
        }

        $activeDrop = $product->drops()->active()->first();

        if ($activeDrop) {
            if (Auth::guest()) {
                return response()->json(['message' => 'Connectez-vous pour acheter un produit de drop.'], 422);
            }

            $user = Auth::user();

            if (! $user instanceof \App\Models\User || ! $user->isWhitelistedForDrop($activeDrop)) {
                return response()->json(['message' => "Ce produit fait partie d'un drop privé. Faites une demande de whitelist pour y accéder."], 422);
            }
        }

        $upcomingDrop = $product->drops()->upcoming()->first();

        if ($upcomingDrop) {
            return response()->json(['message' => "Ce produit fait partie d'un drop à venir et n'est pas encore disponible à l'achat."], 422);
        }

        [$userId, $sessionId] = $this->owner();

        $cartItem = CartItem::forOwner($userId, $sessionId)
            ->where('variant_id', $variant->id)
            ->first();

        $quantity = ($cartItem->quantity ?? 0) + 1;

        if ($quantity > $variant->stock) {
            return response()->json(['message' => 'Quantité demandée indisponible.'], 422);
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

        return response()->json($this->cartSummary());
    }

    public function update(Request $request, $variantId)
    {
        $variant = Variant::findOrFail($variantId);

        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $variant->stock,
        ]);

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