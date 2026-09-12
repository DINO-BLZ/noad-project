<?php

namespace App\Http\Controllers;

use App\Actions\Cart\AddToCartAction;
use App\Actions\Cart\RemoveCartItemAction;
use App\Actions\Cart\UpdateCartAction;
use App\Http\Requests\AddToCartRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CartController extends Controller
{
    /**
     * Afficher le panier.
     */
    public function index(CartService $cartService)
    {
        $display = $cartService->displayItems();

        return view('cart.index', [
            'items' => $display['items'],
            'total' => $display['total'],
        ]);
    }

    /**
     * Ajouter un produit au panier.
     */
    public function add(
        AddToCartRequest $request,
        Product $product,
        AddToCartAction $action
    ) {
        if (Auth::check() && Auth::user()->is_admin) {
            return response()->json([
                'message' => 'Les comptes administrateurs ne peuvent pas effectuer d\'achats.',
            ], 403);
        }

        try {
            $summary = $action->execute(
                $product,
                $request->variant_id
            );
        } catch (HttpException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getStatusCode());
        }

        return response()->json($summary);
    }

    /**
     * Modifier la quantité d'un article.
     */
    public function update(
        UpdateCartItemRequest $request,
        int $variantId,
        UpdateCartAction $action
    ) {
        try {
            $summary = $action->execute(
                $variantId,
                (int) $request->quantity
            );
        } catch (HttpException $e) {

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], $e->getStatusCode());
            }

            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage());
        }

        /*
        |--------------------------------------------------------------------------
        | Requête AJAX
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {
            return response()->json($summary);
        }

        /*
        |--------------------------------------------------------------------------
        | Requête navigateur classique
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('cart.index')
            ->with('success', 'Quantité mise à jour.');
    }

    /**
     * Supprimer un article du panier.
     */
    public function remove(
        Request $request,
        int $variantId,
        RemoveCartItemAction $action
    ) {
        try {
            $summary = $action->execute($variantId);
        } catch (HttpException $e) {

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $e->getMessage(),
                ], $e->getStatusCode());
            }

            return redirect()
                ->route('cart.index')
                ->with('error', $e->getMessage());
        }

        /*
        |--------------------------------------------------------------------------
        | Requête AJAX
        |--------------------------------------------------------------------------
        */

        if ($request->expectsJson()) {
            return response()->json($summary);
        }

        /*
        |--------------------------------------------------------------------------
        | Requête navigateur classique
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('cart.index')
            ->with('success', 'Article retiré du panier.');
    }
}