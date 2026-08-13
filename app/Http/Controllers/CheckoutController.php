<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\Order;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
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

        [$userId, $sessionId] = $this->owner();

        $cartItems = CartItem::forOwner($userId, $sessionId)->get();

        if ($cartItems->isEmpty()) {
            return redirect()->route('cart.index');
        }

        $order = DB::transaction(function () use ($request, $cartItems, $userId, $sessionId) {
            $total = 0;
            $orderItemsData = [];

            foreach ($cartItems as $cartItem) {
                // Verrou réel : la ligne est verrouillée jusqu'à la fin de la transaction
                $variant = Variant::with('product')->lockForUpdate()->find($cartItem->variant_id);

                if (!$variant) continue;

                // Revérification du stock APRÈS le verrou (source de vérité à cet instant)
                if ($variant->stock < $cartItem->quantity) {
                    abort(422, "Stock insuffisant pour {$variant->product->name} ({$variant->size}).");
                }

                // Vérification whitelist : bloque au checkout, pas seulement au panier
                $activeDrop = $variant->product->activeDrop();
                if ($activeDrop && (!auth()->check() || !auth()->user()->isWhitelistedForDrop($activeDrop))) {
                    abort(403, "Vous n'êtes pas autorisé à acheter ce produit de drop.");
                }

                // Un produit rattaché à un drop à venir n'est pas encore en vente
                $upcomingDrop = $variant->product->upcomingDrop();
                if ($upcomingDrop) {
                    abort(403, "Ce produit fait partie d'un drop à venir et n'est pas encore disponible à l'achat.");
                }

                $subtotal = $variant->product->price * $cartItem->quantity;
                $total += $subtotal;

                $orderItemsData[] = [
                    'variant_id' => $variant->id,
                    'quantity' => $cartItem->quantity,
                    'price' => $variant->product->price,
                    'variant_sku' => $variant->sku,
                    'variant_size' => $variant->size,
                    'variant_color' => $variant->color,
                    'product_name' => $variant->product->name,
                ];

                // Décrément immédiat, toujours dans le verrou
                $variant->decrement('stock', $cartItem->quantity);
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

            // Panier vidé une fois la commande créée, toujours dans la transaction
            CartItem::forOwner($userId, $sessionId)->delete();

            return $order;
        });

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