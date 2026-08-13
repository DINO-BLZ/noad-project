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
            return back()->withErrors(['variant_id' => 'Cette variante ne correspond pas au produit sélectionné.']);
        }

        if ($variant->stock <= 0) {
            return back()->withErrors(['stock' => 'Cette taille est épuisée.']);
        }

        $activeDrop = $product->drops()->active()->first();

        if ($activeDrop) {
            if (Auth::guest()) {
                return back()->withErrors(['drop' => 'Connectez-vous pour acheter un produit de drop.']);
            }

            $user = Auth::user();

            if (! $user instanceof \App\Models\User || ! $user->isWhitelistedForDrop($activeDrop)) {
                return back()->withErrors(['drop' => 'Ce produit fait partie d\'un drop privé. Faites une demande de whitelist pour y accéder.']);
            }
        }

        $upcomingDrop = $product->drops()->upcoming()->first();

        if ($upcomingDrop) {
            return back()->withErrors(['drop' => 'Ce produit fait partie d\'un drop à venir et n\'est pas encore disponible à l\'achat.']);
        }

        [$userId, $sessionId] = $this->owner();

        $cartItem = CartItem::forOwner($userId, $sessionId)
            ->where('variant_id', $variant->id)
            ->first();

        $quantity = ($cartItem->quantity ?? 0) + 1;

        if ($quantity > $variant->stock) {
            return back()->withErrors(['quantity' => 'Quantité demandée indisponible.']);
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

        return back()->with('success', 'Article ajouté au panier.');
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

        return back();
    }

    public function remove($variantId)
    {
        [$userId, $sessionId] = $this->owner();

        CartItem::forOwner($userId, $sessionId)
            ->where('variant_id', $variantId)
            ->delete();

        return back();
    }

    /**
     * Détermine à qui appartient le panier courant :
     * l'utilisateur connecté, ou l'invité via l'ID de session.
     */
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