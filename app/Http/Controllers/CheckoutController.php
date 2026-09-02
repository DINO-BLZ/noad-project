<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrderAction;
use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderConfirmationMail;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CheckoutController extends Controller
{
    public function index()
    {
        $cartItems = $this->cartItemsQuery()
            ->with('variant.product')
            ->get();

        $items = [];
        $total = 0;

        foreach ($cartItems as $cartItem) {
            if (! $cartItem->variant || ! $cartItem->variant->product) {
                continue;
            }

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

    public function store(CheckoutRequest $request, CreateOrderAction $createOrderAction)
    {
        [$userId, $sessionId] = $this->owner();
        $data = $request->validated();
        $checkoutToken = $data['checkout_token'] ?? null;

        if ($checkoutToken) {
            $existingOrder = Order::where('user_id', $userId)
                ->where('checkout_token', $checkoutToken)
                ->first();

            if ($existingOrder) {
                return redirect()->route('checkout.success', $existingOrder->id);
            }
        }

        if (CartItem::forOwner($userId, $sessionId)->count() === 0) {
            return redirect()->route('cart.index');
        }

        try {
            $checkoutToken ??= (string) Str::uuid();
            $order = $createOrderAction->execute($data, $userId, $sessionId, $checkoutToken);
        } catch (HttpException $e) {
            return back()
                ->withErrors([
                    'checkout' => $e->getMessage(),
                ])
                ->withInput();
        }

        if (Auth::check() && Auth::user()->email) {
            Mail::to(Auth::user()->email)
                ->queue(new OrderConfirmationMail($order));
        }

        return redirect()->route('checkout.success', $order->id);
    }

    public function success(Order $order)
    {
        $this->authorize('view', $order);

        return view('checkout.success', compact('order'));
    }

    private function owner(): array
    {
        if (Auth::check()) {
            return [
                Auth::id(),
                null,
            ];
        }

        return [
            null,
            session()->getId(),
        ];
    }

    private function cartItemsQuery()
    {
        [$userId, $sessionId] = $this->owner();

        return CartItem::forOwner(
            $userId,
            $sessionId
        );
    }
}
