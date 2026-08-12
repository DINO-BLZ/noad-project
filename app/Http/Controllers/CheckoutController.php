<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $items = [];
        $total = 0;

        foreach ($cart as $variantId => $quantity) {
            $variant = Variant::with('product')->find($variantId);
            if (!$variant) continue;

            $subtotal = $variant->product->price * $quantity;
            $total += $subtotal;

            $items[] = [
                'variant' => $variant,
                'quantity' => $quantity,
                'subtotal' => $subtotal,
            ];
        }

        if (empty($items)) {
            return redirect()->route('cart.index');
        }

        return view('checkout.index', compact('items', 'total'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'wilaya' => 'required|string|max:100',
            'payment_method' => 'required|in:cod,cib',
        ]);

        $cart = session('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        // Basic validation of quantities stored in the session
        foreach ($cart as $variantId => $quantity) {
            if (!is_numeric($quantity) || (int)$quantity < 1) {
                abort(422, "Quantité invalide pour l'article $variantId.");
            }
        }

        $order = DB::transaction(function () use ($request, $cart) {
            $total = 0;
            $orderItemsData = [];

            foreach ($cart as $variantId => $quantity) {
                // Verrou réel : la ligne est verrouillée jusqu'à la fin de la transaction
                $variant = Variant::with('product')->lockForUpdate()->find($variantId);

                if (!$variant) continue;

                // Revérification du stock APRÈS le verrou (source de vérité à cet instant)
                if ($variant->stock < $quantity) {
                    abort(422, "Stock insuffisant pour {$variant->product->name} ({$variant->size}).");
                }

                // Vérification whitelist : bloque au checkout, pas seulement au panier
                $activeDrop = $variant->product->activeDrop();
                if ($activeDrop && (!auth()->check() || !auth()->user()->isWhitelistedForDrop($activeDrop))) {
                    abort(403, "Vous n'êtes pas autorisé à acheter ce produit de drop.");
                }

                $subtotal = $variant->product->price * $quantity;
                $total += $subtotal;

                $orderItemsData[] = [
                    'variant_id' => $variant->id,
                    'quantity' => $quantity,
                    'price' => $variant->product->price,
                    'variant_sku' => $variant->sku,
                    'variant_size' => $variant->size,
                    'variant_color' => $variant->color,
                    'product_name' => $variant->product->name,
                ];

                // Décrément immédiat, toujours dans le verrou
                $variant->decrement('stock', $quantity);
            }

            $order = Order::create([
                'user_id' => auth()->id(),
                'full_name' => $request->full_name,
                'phone' => $request->phone,
                'address' => $request->address,
                'wilaya' => $request->wilaya,
                'payment_method' => $request->payment_method,
                'status' => 'pending',
                'total' => $total,
            ]);

            foreach ($orderItemsData as $item) {
                $order->items()->create($item);
            }

            return $order;
        });

        session()->forget('cart');

        return redirect()->route('checkout.success', $order->id);
    }

    public function success(Order $order)
    {
        // Propriété : seul le propriétaire (ou un admin) peut voir cette commande
        if ($order->user_id !== auth()->id() && !(auth()->check() && auth()->user()->is_admin)) {
            abort(403);
        }

        return view('checkout.success', compact('order'));
    }
}