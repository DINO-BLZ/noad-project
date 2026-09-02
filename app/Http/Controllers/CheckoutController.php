<?php

namespace App\Http\Controllers;

use App\Actions\Orders\CreateOrderAction;
use App\Http\Requests\CheckoutRequest;
use App\Mail\OrderConfirmationMail;
use App\Models\CartItem;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
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

        if (CartItem::forOwner($userId, $sessionId)->count() === 0) {
            return redirect()->route('cart.index');
        }

        try {
            $order = $createOrderAction->execute($request->validated(), $userId, $sessionId);
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
        /*
        |--------------------------------------------------------------------------
        | Protection de la commande
        |--------------------------------------------------------------------------
        |
        | Seul le propriétaire ou un administrateur
        | peut consulter la page de succès.
        |
        */

        if (
            $order->user_id !== Auth::id() &&
            (! Auth::check() || ! Auth::user()->is_admin)
        ) {
            abort(403);
        }

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
