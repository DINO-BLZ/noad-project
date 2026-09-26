<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Drop;
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
        |--------------------------------------------------------------------------
        | STATUTS CONSIDÉRÉS COMME DU CA CONFIRMÉ
        |--------------------------------------------------------------------------
        */

        $confirmedStatuses = [
            OrderStatus::Paid->value,
            OrderStatus::Shipped->value,
            OrderStatus::Delivered->value,
        ];

        /*
        |--------------------------------------------------------------------------
        | CHIFFRE D'AFFAIRES
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | COMMANDES
        |--------------------------------------------------------------------------
        */

        $totalOrders = Order::count();

        $pendingOrders = Order::where(
            'status',
            OrderStatus::Pending->value
        )->count();

        $confirmedOrders = Order::whereIn(
            'status',
            $confirmedStatuses
        )->count();

        /*
        |--------------------------------------------------------------------------
        | STOCK TOTAL
        |--------------------------------------------------------------------------
        */

        $totalStock = Variant::totalStock();

        /*
        |--------------------------------------------------------------------------
        | STOCKS CRITIQUES
        |--------------------------------------------------------------------------
        |
        | Règle métier du dashboard :
        |
        | 0       => ÉPUISÉE
        | 1 à 5   => STOCK CRITIQUE
        | > 5     => aucune alerte
        |
        | Le compteur est volontairement calculé séparément de la liste
        | affichée afin que la limitation à 3 lignes ne fausse jamais
        | le nombre réel d'alertes.
        |
        */

        $criticalStockAlertsCount = Variant::query()
            ->whereBetween('stock', [0, 5])
            ->count();

        $lowStockVariants = Variant::query()
            ->whereBetween('stock', [0, 5])
            ->with('product')
            ->orderBy('stock')
            ->orderBy(
                Product::select('name')
                    ->whereColumn(
                        'products.id',
                        'variants.product_id'
                    )
            )
            ->orderBy('id')
            ->take(3)
            ->get()
            ->map(function (Variant $variant) {
                $isOutOfStock = (int) $variant->stock === 0;

                return [
                    'variant' => $variant,
                    'product_name' => $variant->product?->name ?? 'PRODUIT INCONNU',
                    'color' => $variant->color,
                    'size' => $variant->size,
                    'stock' => (int) $variant->stock,
                    'label' => $isOutOfStock
                        ? 'ÉPUISÉE'
                        : ((int) $variant->stock === 1
                            ? '1 RESTANT'
                            : $variant->stock.' RESTANTS'),
                    'status_label' => $isOutOfStock
                        ? 'ÉPUISÉE'
                        : 'STOCK CRITIQUE',
                    'status_class' => $isOutOfStock
                        ? 'exhausted'
                        : 'critical',
                ];
            });

        /*
        |--------------------------------------------------------------------------
        | MONITORING DU DROP ACTIF
        |--------------------------------------------------------------------------
        |
        | S'il existe plusieurs drops actifs, on prend celui dont
        | la date de début est la plus récente.
        |
        | Le calcul du quota / vendu / restant / pourcentage est centralisé
        | dans Drop::quotaMonitoring().
        |
        */

        $activeDrop = Drop::current()->first();

        $dropMonitoring = $activeDrop?->quotaMonitoring();

        /*
         * Aucun drop actif :
         * on transmet un état neutre afin que la vue puisse afficher
         * proprement son état vide sans valeurs fictives.
         */
        if ($dropMonitoring === null) {
            $dropMonitoring = [
                'quota_defined' => false,
                'quota' => 0,
                'sold' => 0,
                'remaining' => 0,
                'percentage' => 0,
            ];
        }

        /*
         * Compte à rebours basé directement sur end_date.
         *
         * %a = nombre total de jours restants
         * %h = heures restantes après les jours
         * %i = minutes restantes après les heures
         *
         * Exemple :
         * 2 jours, 14 heures, 32 minutes
         * => 02 / 14 / 32
         */
        $dropCountdown = null;

        if ($activeDrop !== null) {
            $interval = now()->diff($activeDrop->end_date);

            $dropCountdown = [
                'days' => str_pad(
                    (string) $interval->days,
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
                'hours' => str_pad(
                    (string) $interval->h,
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
                'minutes' => str_pad(
                    (string) $interval->i,
                    2,
                    '0',
                    STR_PAD_LEFT
                ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | WHITELIST
        |--------------------------------------------------------------------------
        */

        $whitelistApplications = DropWhitelist::count();

        $pendingReviews = DropWhitelist::where(
            'status',
            'pending'
        )->count();

        /*
        |--------------------------------------------------------------------------
        | UTILISATEURS / PRODUITS
        |--------------------------------------------------------------------------
        */

        $totalUsers = User::count();

        $totalProducts = Product::count();

        /*
        |--------------------------------------------------------------------------
        | TOP PRODUITS
        |--------------------------------------------------------------------------
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
        |--------------------------------------------------------------------------
        | COMMANDES RÉCENTES
        |--------------------------------------------------------------------------
        */

        $recentOrders = Order::query()
            ->with('user')
            ->withCount('items')
            ->latest()
            ->take(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | VENTES DES 7 DERNIERS JOURS
        |--------------------------------------------------------------------------
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

        /*
        |--------------------------------------------------------------------------
        | VUE
        |--------------------------------------------------------------------------
        */

        return view(
            'admin.dashboard',
            compact(
                'totalRevenue',
                'totalOrders',
                'pendingOrders',
                'confirmedOrders',
                'totalStock',
                'criticalStockAlertsCount',
                'lowStockVariants',
                'whitelistApplications',
                'pendingReviews',
                'totalUsers',
                'totalProducts',
                'topProducts',
                'recentOrders',
                'salesByDay',
                'activeDrop',
                'dropMonitoring',
                'dropCountdown'
            )
        );
    }
}
