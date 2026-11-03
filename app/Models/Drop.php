<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\WhitelistStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Drop extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'start_date',
        'end_date',
        'max_whitelist_slots',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(
            Product::class,
            'drop_product'
        )
            ->withTimestamps()
            ->withPivot('quota');
    }

    /**
     * Calcule les statistiques de quota du drop.
     */
    public function quotaMonitoring(): array
    {
        return self::quotaMonitoringFor([$this])[$this->id];
    }

    /**
     * Calcule les quotas, ventes et revenus de plusieurs drops.
     *
     * Les ventes sont calculées par produit et plafonnées
     * individuellement au quota correspondant.
     */
    public static function quotaMonitoringFor(iterable $drops): Collection
    {
        $dropIds = collect($drops)
            ->map(fn (Drop $drop) => (int) $drop->id)
            ->filter()
            ->unique()
            ->values();

        if ($dropIds->isEmpty()) {
            return collect();
        }

        $quotas = DB::table('drop_product')
            ->whereIn('drop_id', $dropIds)
            ->where('quota', '>', 0)
            ->select('drop_id', 'product_id')
            ->selectRaw('SUM(quota) as quota')
            ->groupBy('drop_id', 'product_id')
            ->get()
            ->groupBy('drop_id');

        $salesByProduct = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('order_items.drop_id', $dropIds)
            ->whereIn('orders.status', [
                OrderStatus::Paid->value,
                OrderStatus::Shipped->value,
                OrderStatus::Delivered->value,
            ])
            ->select(
                'order_items.drop_id',
                'order_items.product_id'
            )
            ->selectRaw('SUM(order_items.quantity) as sold')
            ->groupBy(
                'order_items.drop_id',
                'order_items.product_id'
            )
            ->get()
            ->groupBy('drop_id');

        $financials = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('order_items.drop_id', $dropIds)
            ->whereIn('orders.status', [
                OrderStatus::Paid->value,
                OrderStatus::Shipped->value,
                OrderStatus::Delivered->value,
            ])
            ->select('order_items.drop_id')
            ->selectRaw(
                'SUM(order_items.quantity) as actual_sold'
            )
            ->selectRaw(
                'SUM(
                    CASE
                        WHEN orders.payment_status = ?
                        THEN order_items.quantity * order_items.price
                        ELSE 0
                    END
                ) as revenue',
                [PaymentStatus::Paid->value]
            )
            ->selectRaw('COUNT(DISTINCT orders.id) as orders')
            ->groupBy('order_items.drop_id')
            ->get()
            ->keyBy('drop_id');

        return $dropIds->mapWithKeys(
            function (int $dropId) use (
                $quotas,
                $salesByProduct,
                $financials
            ) {
                $dropQuotas = $quotas->get($dropId, collect());

                $productSales = $salesByProduct
                    ->get($dropId, collect())
                    ->keyBy('product_id');

                $totalQuota = (int) $dropQuotas->sum('quota');

                $totalSold = (int) $dropQuotas->sum(
                    function ($quota) use ($productSales) {
                        $unitsSold = (int) (
                            $productSales->get(
                                $quota->product_id
                            )->sold ?? 0
                        );

                        return min(
                            $unitsSold,
                            (int) $quota->quota
                        );
                    }
                );

                $dropFinancials = $financials->get($dropId);

                $remaining = max(0, $totalQuota - $totalSold);
                $quotaDefined = $totalQuota > 0;

                return [
                    $dropId => [
                        'quota_defined' => $quotaDefined,
                        'quota' => $totalQuota,
                        'sold' => $totalSold,
                        'actual_sold' => (int) (
                            $dropFinancials->actual_sold ?? 0
                        ),
                        'available' => $remaining,
                        'remaining' => $remaining,
                        'percentage' => $quotaDefined
                            ? min(
                                100,
                                (int) floor(
                                    ($totalSold / $totalQuota) * 100
                                )
                            )
                            : 0,
                        'revenue' => (float) (
                            $dropFinancials->revenue ?? 0
                        ),
                        'orders' => (int) (
                            $dropFinancials->orders ?? 0
                        ),
                        'quota_status' => ! $quotaDefined
                            ? 'undefined'
                            : (
                                $remaining === 0
                                    ? 'reached'
                                    : 'available'
                            ),
                    ],
                ];
            }
        );
    }

    public function whitelists()
    {
        return $this->hasMany(DropWhitelist::class);
    }

    public function approvedWhitelists()
    {
        return $this->hasMany(DropWhitelist::class)
            ->where('status', WhitelistStatus::Approved->value);
    }

    /**
     * Statut calculé du drop :
     *
     * - ended : date de fin dépassée ;
     * - upcoming : date de début dans le futur ;
     * - ended : drop commencé mais sans stock disponible ;
     * - active : drop ouvert avec du stock disponible.
     *
     * Le statut n'est pas enregistré en base de données.
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                $hasAvailableStock = array_key_exists(
                    'has_available_stock',
                    $this->attributes
                )
                    ? (bool) $this->attributes['has_available_stock']
                    : ! $this->isSoldOut();

                if ($this->end_date->isPast()) {
                    return 'ended';
                }

                if ($this->start_date->isFuture()) {
                    return 'upcoming';
                }

                if (! $hasAvailableStock) {
                    return 'ended';
                }

                return 'active';
            }
        );
    }

    public function scopeActive($query)
    {
        return $query
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->whereHas('products.variants', function ($query) {
                $query->where('stock', '>', 0);
            });
    }

    /**
     * Retourne les drops actifs du plus récent au plus ancien.
     */
    public function scopeCurrent($query)
    {
        return $query
            ->active()
            ->orderByDesc('start_date');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeEnded($query)
    {
        return $query->where(function ($query) {
            $query->where('end_date', '<', now())
                ->orWhere(function ($soldOut) {
                    $soldOut
                        ->where('start_date', '<=', now())
                        ->whereDoesntHave(
                            'products.variants',
                            fn ($variants) => $variants->where(
                                'stock',
                                '>',
                                0
                            )
                        );
                });
        });
    }

    /**
     * Charge le statut du stock dans la requête principale
     * pour éviter une requête supplémentaire par drop.
     */
    public function scopeWithStockStatus($query)
    {
        $availableStock = DB::table('drop_product')
            ->join(
                'variants',
                'variants.product_id',
                '=',
                'drop_product.product_id'
            )
            ->whereColumn('drop_product.drop_id', 'drops.id')
            ->where('variants.stock', '>', 0)
            ->selectRaw('1')
            ->limit(1);

        return $query->addSelect([
            'has_available_stock' => $availableStock,
        ]);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isUpcoming(): bool
    {
        return $this->status === 'upcoming';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function isSoldOut(): bool
    {
        $productIds = $this->products()->pluck('products.id');

        return (int) Variant::whereIn(
            'product_id',
            $productIds
        )->sum('stock') === 0;
    }

    public function hasWhitelistSlotsAvailable(): bool
    {
        if (is_null($this->max_whitelist_slots)) {
            return true;
        }

        return $this->approvedWhitelists()->count()
            < $this->max_whitelist_slots;
    }
}
