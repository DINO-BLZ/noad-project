<?php

namespace App\Models;

use App\Enums\OrderStatus;
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
     *
     * Le quota est défini produit par produit.
     *
     * Seuls les produits dont le quota est strictement supérieur
     * à zéro participent au calcul.
     *
     * Les ventes sont comptabilisées uniquement pour les commandes
     * confirmées : paid, shipped et delivered.
     *
     * Chaque produit est plafonné individuellement à son propre quota
     * avant que les totaux du drop soient calculés.
     */
    public function quotaMonitoring(): array
    {
        return self::quotaMonitoringFor([$this])[$this->id];
    }

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
            ->select('drop_id')
            ->selectRaw('SUM(quota) as quota')
            ->groupBy('drop_id')
            ->pluck('quota', 'drop_id');

        $sales = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('order_items.drop_id', $dropIds)
            ->whereIn('orders.status', [
                OrderStatus::Paid->value,
                OrderStatus::Shipped->value,
                OrderStatus::Delivered->value,
            ])
            ->select('order_items.drop_id')
            ->selectRaw('SUM(order_items.quantity) as sold')
            ->selectRaw('SUM(order_items.quantity * order_items.price) as revenue')
            ->selectRaw('COUNT(DISTINCT orders.id) as orders')
            ->groupBy('order_items.drop_id')
            ->get()
            ->keyBy('drop_id');

        return $dropIds->mapWithKeys(function (int $dropId) use ($quotas, $sales) {
            $totalQuota = (int) ($quotas[$dropId] ?? 0);
            $dropSales = $sales[$dropId] ?? null;
            $totalSold = (int) ($dropSales->sold ?? 0);
            $remaining = max(0, $totalQuota - $totalSold);
            $quotaDefined = $totalQuota > 0;

            return [$dropId => [
                'quota_defined' => $quotaDefined,
                'quota' => $totalQuota,
                'sold' => $totalSold,
                'available' => $remaining,
                'remaining' => $remaining,
                'percentage' => $quotaDefined
                    ? min(100, (int) floor(($totalSold / $totalQuota) * 100))
                    : 0,
                'revenue' => (float) ($dropSales->revenue ?? 0),
                'orders' => (int) ($dropSales->orders ?? 0),
                'quota_status' => ! $quotaDefined
                    ? 'undefined'
                    : ($remaining === 0 ? 'reached' : 'available'),
            ]];
        });
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
     * Statut calculé du drop, jamais stocké en base :
     * dépend des dates ET du stock des produits associés.
     *
     * Toutes les vues Blade qui lisent $drop->status continuent
     * de fonctionner sans modification.
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                $hasAvailableStock = array_key_exists('has_available_stock', $this->attributes)
                    ? (bool) $this->attributes['has_available_stock']
                    : ! $this->isSoldOut();

                if (
                    $this->end_date->isPast()
                    || ! $hasAvailableStock
                ) {
                    return 'ended';
                }

                if ($this->start_date->isFuture()) {
                    return 'upcoming';
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
            ->whereHas('products.variants', function ($q) {
                $q->where('stock', '>', 0);
            });
    }

    /**
     * Retourne les drops actifs dans l'ordre du plus récent
     * au plus ancien.
     *
     * Utilisé lorsqu'il peut exister plusieurs drops actifs
     * simultanément et qu'il faut déterminer le drop courant.
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
                ->orWhereDoesntHave('products.variants', fn ($variants) => $variants->where('stock', '>', 0));
        });
    }

    public function scopeWithStockStatus($query)
    {
        $availableStock = DB::table('drop_product')
            ->join('variants', 'variants.product_id', '=', 'drop_product.product_id')
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
