<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $items = [];
        $total = 0;

        if (empty($cart)) {
            return view('cart.index', compact('items', 'total'));
        }

        $variants = Variant::with('product')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        foreach ($cart as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (!$variant) {
                unset($cart[$variantId]);
                continue;
            }

            $subtotal = $variant->product->price * $quantity;
            $total += $subtotal;

            $items[] = [
                'variant' => $variant,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        if (count($cart) !== count($items)) {
            session(['cart' => $cart]);
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

        $cart = session('cart', []);
        $quantity = ($cart[$variant->id] ?? 0) + 1;

        if ($quantity > $variant->stock) {
            return back()->withErrors(['quantity' => 'Quantité demandée indisponible.']);
        }

        $cart[$variant->id] = $quantity;
        session(['cart' => $cart]);

        return back()->with('success', 'Article ajouté au panier.');
    }

    public function update(Request $request, $variantId)
    {
        $variant = Variant::findOrFail($variantId);

        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $variant->stock,
        ]);

        $cart = session('cart', []);

        if (isset($cart[$variantId])) {
            $cart[$variantId] = $request->quantity;
            session(['cart' => $cart]);
        }

        return back();
    }

    public function remove($variantId)
    {
        $cart = session('cart', []);
        unset($cart[$variantId]);
        session(['cart' => $cart]);

        return back();
    }
}