<?php

namespace App\Http\Controllers;

use App\Actions\Cart\AddToCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartAction;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CartController extends Controller
{
    public function index(CartService $cartService)
    {
        $display = $cartService->displayItems();

        return view('cart.index', [
            'items' => $display['items'],
            'total' => $display['total'],
        ]);
    }

    public function add(AddToCartRequest $request, Product $product, AddToCartAction $action)
    {
        if (Auth::check() && Auth::user()->is_admin) {
            return response()->json(['message' => 'Les comptes administrateurs ne peuvent pas effectuer d\'achats.'], 403);
        }

        try {
            $summary = $action->execute($product, $request->variant_id);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json($summary);
    }

    public function update(UpdateCartItemRequest $request, $variantId, UpdateCartAction $action)
    {
        try {
            $summary = $action->execute((int) $variantId, $request->quantity);
        } catch (HttpException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getStatusCode());
        }

        return response()->json($summary);
    }

    public function remove($variantId, RemoveCartItemAction $action)
    {
        return response()->json($action->execute((int) $variantId));
    }
}