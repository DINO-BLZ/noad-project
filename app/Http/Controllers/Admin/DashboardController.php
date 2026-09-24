<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\DropWhitelist;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        /*
         * ============================================================
         * STATUTS CONSIDÉRÉS COMME DES VENTES CONFIRMÉES
         * ============================================================
         *
         * Même règle que dans Admin/DropController :
         *
         * paid      = commande payée
         * shipped   = commande expédiée
         * delivered = commande livrée
         *
         * pending et cancelled ne sont donc pas comptés dans le CA.
         */
        $confirmedStatuses = [
            OrderStatus::Paid->value,
            OrderStatus::Shipped->value,
            OrderStatus::Delivered->value,
        ];

        /*
         * ============================================================
         * KPI 1 — CHIFFRE D'AFFAIRES GLOBAL
         * ============================================================
         *
         * On utilise OrderItem.price afin de conserver le prix
         * réellement enregistré au moment de la commande.
         */
        $totalRevenue = (float) (
            OrderItem::query()
                ->whereHas(
                    'order',
                    fn ($query) => $query->whereIn(
                        'status',
                        $confirmedStatuses
                    )
                )
                ->selectRaw('SUM(quantity * price) as total')
                ->value('total') ?? 0
        );

        /*
         * ============================================================
         * KPI 2 — COMMANDES
         * ============================================================
         */

        // Toutes les commandes existantes.
        $totalOrders = Order::count();

        // Commandes encore en attente.
        $pendingOrders = Order::where(
            'status',
            OrderStatus::Pending->value
        )->count();

        // Commandes effectivement confirmées.
        $confirmedOrders = Order::whereIn(
            'status',
            $confirmedStatuses
        )->count();

        /*
         * ============================================================
         * KPI 3 — STOCK GLOBAL
         * ============================================================
         *
         * Il n'existe actuellement pas de stock initial permettant
         * de calculer honnêtement un pourcentage de stock restant.
         *
         * On expose donc le stock réel disponible en unités.
         */
        $totalStock = (int) Variant::sum('stock');

        /*
         * Variantes avec stock faible.
         */
        $lowStockVariants = Variant::where('stock', '<=', 5)
            ->where('stock', '>', 0)
            ->with('product')
            ->get();

        /*
         * ============================================================
         * KPI 4 — WHITELIST
         * ============================================================
         */

        // Toutes les candidatures existantes.
        $whitelistApplications = DropWhitelist::count();

        // Candidatures qui nécessitent encore une décision.
        $pendingReviews = DropWhitelist::where(
            'status',
            'pending'
        )->count();

        /*
         * ============================================================
         * AUTRES DONNÉES DU DASHBOARD
         * ============================================================
         */

        $totalUsers = User::count();

        $totalProducts = Product::count();

        /*
         * Produits les plus vendus.
         *
         * On applique la même définition de vente confirmée
         * que pour le CA.
         */
        $topProducts = DB::table('order_items')
            ->join(
                'orders',
                'orders.id',
                '=',
                'order_items.order_id'
            )
            ->whereIn(
                'orders.status',
                $confirmedStatuses
            )
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get();

        /*
         * Dernières commandes.
         */
        $recentOrders = Order::latest()
            ->take(5)
            ->get();

        /*
         * Ventes des 7 derniers jours.
         *
         * Même règle : uniquement les commandes confirmées.
         */
        $salesByDay = Order::where(
            'created_at',
            '>=',
            now()->subDays(7)
        )
            ->whereIn(
                'status',
                $confirmedStatuses
            )
            ->select(
                DB::raw('date(created_at) as day'),
                DB::raw('sum(total) as total')
            )
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        return view(
            'admin.dashboard',
            compact(
                'totalRevenue',
                'totalOrders',
                'pendingOrders',
                'confirmedOrders',
                'totalStock',
                'whitelistApplications',
                'pendingReviews',
                'totalUsers',
                'totalProducts',
                'lowStockVariants',
                'topProducts',
                'recentOrders',
                'salesByDay'
            )
        );
    }
}