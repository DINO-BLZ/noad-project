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
        $cartItems = $this->cartItemsQuery()
            ->with('variant.product')
            ->get();

        $items = [];
        $total = 0;

        foreach ($cartItems as $cartItem) {
            // Le variant ou le produit peut avoir été supprimé entre-temps
            if (!$cartItem->variant || !$cartItem->variant->product) {
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

        try {
            $order = DB::transaction(function () use (
                $request,
                $cartItems,
                $userId,
                $sessionId
            ) {
                $total = 0;
                $orderItemsData = [];

                foreach ($cartItems as $cartItem) {

                    /*
                    |--------------------------------------------------------------------------
                    | Verrouillage du variant
                    |--------------------------------------------------------------------------
                    */

                    $variant = Variant::with('product')
                        ->lockForUpdate()
                        ->find($cartItem->variant_id);

                    if (!$variant || !$variant->product) {
                        abort(
                            422,
                            "Un article de votre panier n'est plus disponible."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Vérification du stock APRÈS verrouillage
                    |--------------------------------------------------------------------------
                    */

                    if ($variant->stock < $cartItem->quantity) {
                        abort(
                            422,
                            "Stock insuffisant pour {$variant->product->name} ({$variant->size})."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Vérification du drop actif / whitelist
                    |--------------------------------------------------------------------------
                    */

                    $activeDrop = $variant->product->activeDrop();

                    if ($activeDrop) {
                        if (
                            !Auth::check() ||
                            !Auth::user()->isWhitelistedForDrop($activeDrop)
                        ) {
                            abort(
                                403,
                                "Vous n'êtes pas autorisé à acheter ce produit de drop."
                            );
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Vérification d'un drop à venir
                    |--------------------------------------------------------------------------
                    */

                    $upcomingDrop = $variant->product->upcomingDrop();

                    if ($upcomingDrop) {
                        abort(
                            403,
                            "Ce produit fait partie d'un drop à venir et n'est pas encore disponible à l'achat."
                        );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Calcul du prix
                    |--------------------------------------------------------------------------
                    */

                    $price = $variant->product->price;
                    $subtotal = $price * $cartItem->quantity;

                    $total += $subtotal;

                    /*
                    |--------------------------------------------------------------------------
                    | Préparation de la ligne de commande
                    |--------------------------------------------------------------------------
                    */

                    $orderItemsData[] = [
                        'variant_id' => $variant->id,
                        'quantity' => $cartItem->quantity,

                        // Prix figé au moment de la commande
                        'price' => $price,

                        // Snapshot des informations du variant
                        'variant_sku' => $variant->sku,
                        'variant_size' => $variant->size,
                        'variant_color' => $variant->color,

                        // Snapshot du nom du produit
                        'product_name' => $variant->product->name,
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | Décrément du stock
                    |--------------------------------------------------------------------------
                    |
                    | Le variant est verrouillé par lockForUpdate().
                    | Le stock reste donc protégé contre les achats concurrents.
                    |
                    */

                    $variant->decrement(
                        'stock',
                        $cartItem->quantity
                    );
                }

                /*
                |--------------------------------------------------------------------------
                | Création de la commande
                |--------------------------------------------------------------------------
                */

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

                /*
                |--------------------------------------------------------------------------
                | Création des lignes de commande
                |--------------------------------------------------------------------------
                */

                foreach ($orderItemsData as $item) {
                    $order->items()->create($item);
                }

                /*
                |--------------------------------------------------------------------------
                | Suppression du panier
                |--------------------------------------------------------------------------
                |
                | Tout est encore dans la transaction.
                | Si une erreur survient avant le COMMIT,
                | le stock, la commande et le panier sont rollbackés.
                |
                */

                CartItem::forOwner($userId, $sessionId)->delete();

                return $order;
            });
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            /*
            |--------------------------------------------------------------------------
            | Erreurs métier : stock, whitelist, drop à venir...
            |--------------------------------------------------------------------------
            */

            return back()
                ->withErrors([
                    'checkout' => $e->getMessage(),
                ])
                ->withInput();
        }

        return redirect()->route(
            'checkout.success',
            $order->id
        );
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
    (!Auth::check() || !Auth::user()->is_admin)
) {

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