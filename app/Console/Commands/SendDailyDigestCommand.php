<?php

namespace App\Console\Commands;

use App\Enums\OrderStatus;
use App\Mail\DailyDigestMail;
use App\Models\DropWhitelist;
use App\Models\OrderItem;
use App\Models\Variant;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendDailyDigestCommand extends Command
{
    protected $signature = 'orders:send-daily-digest';

    protected $description = 'Envoie le rapport quotidien des ventes, commandes, whitelists et stocks.';

    public function handle(): int
    {
        $recipients = array_values(array_filter(
            array_map('trim', explode(',', (string) config('mail.digest_recipients')))
        ));

        if ($recipients === []) {
            $this->error('Aucun destinataire configuré dans DIGEST_MAIL_RECIPIENTS.');

            return self::FAILURE;
        }

        $date = now()->subDay();

        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        $confirmedStatuses = [
            OrderStatus::Paid->value,
            OrderStatus::Shipped->value,
            OrderStatus::Delivered->value,
        ];

        /*
        |--------------------------------------------------------------------------
        | VENTES
        |--------------------------------------------------------------------------
        */

        $confirmedOrderQuery = DB::table('orders')
            ->whereIn('status', $confirmedStatuses)
            ->whereBetween('created_at', [$startOfDay, $endOfDay]);

        $confirmedOrders = (clone $confirmedOrderQuery)->count();

        $revenue = (float) (
            OrderItem::query()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereHas(
                    'order',
                    fn ($query) => $query->whereIn('status', $confirmedStatuses)
                )
                ->selectRaw('SUM(quantity * price) as total')
                ->value('total') ?? 0
        );

        $itemsSold = (int) (
            OrderItem::query()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->whereHas(
                    'order',
                    fn ($query) => $query->whereIn('status', $confirmedStatuses)
                )
                ->sum('quantity')
        );

        $topProducts = DB::table('order_items')
            ->join(
                'orders',
                'orders.id',
                '=',
                'order_items.order_id'
            )
            ->whereIn('orders.status', $confirmedStatuses)
            ->whereBetween('orders.created_at', [$startOfDay, $endOfDay])
            ->select(
                'order_items.product_name',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('order_items.product_name')
            ->orderByDesc('total_sold')
            ->take(5)
            ->get()
            ->map(fn ($product) => [
                'name' => $product->product_name,
                'quantity' => (int) $product->total_sold,
            ])
            ->all();

        /*
        |--------------------------------------------------------------------------
        | COMMANDES
        |--------------------------------------------------------------------------
        */

        $orders = [
            'confirmed' => $confirmedOrders,
            'pending' => DB::table('orders')
                ->where('status', OrderStatus::Pending->value)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count(),
            'cancelled' => DB::table('orders')
                ->where('status', OrderStatus::Cancelled->value)
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | WHITELIST
        |--------------------------------------------------------------------------
        */

        $whitelist = [
            'new' => DropWhitelist::query()
                ->whereBetween('created_at', [$startOfDay, $endOfDay])
                ->count(),

            'approved' => DropWhitelist::query()
                ->where('status', 'approved')
                ->whereBetween('updated_at', [$startOfDay, $endOfDay])
                ->count(),

            'rejected' => DropWhitelist::query()
                ->where('status', 'rejected')
                ->whereBetween('updated_at', [$startOfDay, $endOfDay])
                ->count(),
        ];

        /*
        |--------------------------------------------------------------------------
        | STOCK
        |--------------------------------------------------------------------------
        */

        $criticalVariants = Variant::query()
            ->whereBetween('stock', [0, 5])
            ->with('product')
            ->orderBy('stock')
            ->orderBy('id')
            ->get()
            ->map(fn (Variant $variant) => [
                'product_name' => $variant->product?->name ?? 'PRODUIT INCONNU',
                'sku' => $variant->sku,
                'size' => $variant->size,
                'color' => $variant->color,
                'stock' => (int) $variant->stock,
            ])
            ->all();

        $stock = [
            'critical_count' => count($criticalVariants),
            'variants' => $criticalVariants,
        ];

        /*
        |--------------------------------------------------------------------------
        | ENVOI
        |--------------------------------------------------------------------------
        */

        Mail::to($recipients)->send(
            new DailyDigestMail(
                date: $date,
                sales: [
                    'confirmed_orders' => $confirmedOrders,
                    'revenue' => $revenue,
                    'items_sold' => $itemsSold,
                    'top_products' => $topProducts,
                ],
                orders: $orders,
                whitelist: $whitelist,
                stock: $stock,
            )
        );

        $this->info(
            'Rapport quotidien envoyé à '
            .count($recipients)
            .' destinataire(s).'
        );

        return self::SUCCESS;
    }
}