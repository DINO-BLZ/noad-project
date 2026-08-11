<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Variant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = session('cart', []);
        $items = [];
        $total = 0;

        if (empty($cart)) {
            return redirect()->route('cart.index');
        }

        $variants = Variant::with('product')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        foreach ($cart as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (!$variant) {
                return redirect()->route('cart.index')->withErrors(['cart' => 'Un article de votre panier est introuvable.']);
            }

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

        $variantIds = array_keys($cart);
        $variants = Variant::with('product')
            ->whereIn('id', $variantIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $orderItemsData = [];
        $total = 0;

        foreach ($cart as $variantId => $quantity) {
            $variant = $variants->get($variantId);

            if (!$variant || $variant->stock < $quantity) {
                return back()->withErrors(['cart' => 'Un article de votre panier est indisponible ou en quantité insuffisante.']);
            }

            $subtotal = $variant->product->price * $quantity;
            $total += $subtotal;

            $orderItemsData[] = [
                'variant_id' => $variant->id,
                'quantity' => $quantity,
                'price' => $variant->product->price,
            ];
        }

        if ($total <= 0 || empty($orderItemsData)) {
            return back()->withErrors(['cart' => 'Impossible de passer commande avec un panier vide.']);
        }

        DB::transaction(function () use ($request, $orderItemsData, $total) {
            $order = Order::create([
                'user_id' => Auth::id(),
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
                Variant::where('id', $item['variant_id'])->decrement('stock', $item['quantity']);
            }

            session()->forget('cart');

            request()->session()->flash('order_id', $order->id);
        });

        $order = Order::latest()->firstWhere('total', $total);

        return redirect()->route('checkout.success', $order->id);
    }

    public function success(Order $order)
    {
        return view('checkout.success', compact('order'));
    }
}