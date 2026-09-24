<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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
        $productsWithQuota = $this->products()
            ->wherePivot('quota', '>', 0)
            ->get();

        if ($productsWithQuota->isEmpty()) {
            return [
                'quota_defined' => false,
                'quota' => 0,
                'sold' => 0,
                'remaining' => 0,
                'percentage' => 0,
            ];
        }

        $quotas = $productsWithQuota
            ->mapWithKeys(function (Product $product) {
                return [
                    $product->id => (int) $product->pivot->quota,
                ];
            });

        $productIds = $quotas->keys()
            ->map(fn ($id) => (int) $id)
            ->all();

        /*
         * Une seule requête pour toutes les quantités vendues.
         *
         * Limite connue :
         * order_items.variant_id est nullable et peut devenir NULL
         * si la variante est supprimée. Dans ce cas, la vente ne peut
         * plus être rattachée à son produit et sort du calcul.
         * C'est également la limite du calcul actuellement utilisé
         * dans Admin\DropController.
         */
        $soldByProduct = DB::table('order_items')
            ->join(
                'variants',
                'variants.id',
                '=',
                'order_items.variant_id'
            )
            ->join(
                'orders',
                'orders.id',
                '=',
                'order_items.order_id'
            )
            ->whereIn('variants.product_id', $productIds)
            ->whereIn('orders.status', [
                OrderStatus::Paid->value,
                OrderStatus::Shipped->value,
                OrderStatus::Delivered->value,
            ])
            ->select(
                'variants.product_id',
                DB::raw('SUM(order_items.quantity) as total_sold')
            )
            ->groupBy('variants.product_id')
            ->pluck(
                'total_sold',
                'variants.product_id'
            );

        $totalQuota = 0;
        $totalSold = 0;

        foreach ($quotas as $productId => $quota) {
            $sold = (int) ($soldByProduct[$productId] ?? 0);

            /*
             * Le plafond est appliqué produit par produit.
             *
             * Exemple :
             * Produit A : quota 10 / vendu 12 => 10 retenus
             * Produit B : quota 10 / vendu 2  => 2 retenus
             *
             * Total : 12 / 20, et non 14 / 20.
             */
            $cappedSold = min($sold, $quota);

            $totalQuota += $quota;
            $totalSold += $cappedSold;
        }

        $remaining = max(
            0,
            $totalQuota - $totalSold
        );

        /*
         * 100 % uniquement lorsqu'il ne reste réellement
         * plus aucune pièce.
         *
         * Sinon on utilise floor() afin d'éviter qu'un résultat
         * comme 99,6 % soit affiché à tort comme 100 %.
         */
        $percentage = $remaining === 0
            ? 100
            : (int) floor(
                ($totalSold / $totalQuota) * 100
            );

        return [
            'quota_defined' => true,
            'quota' => $totalQuota,
            'sold' => $totalSold,
            'remaining' => $remaining,
            'percentage' => $percentage,
        ];
    }

    public function whitelists()
    {
        return $this->hasMany(DropWhitelist::class);
    }

    public function approvedWhitelists()
    {
        return $this->hasMany(DropWhitelist::class)
            ->where('status', 'approved');
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
                if (
                    $this->end_date->isPast()
                    || $this->isSoldOut()
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

    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>', now());
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