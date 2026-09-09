<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Drop extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'start_date', 'end_date', 'max_whitelist_slots'];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }

    public function whitelists()
    {
        return $this->hasMany(DropWhitelist::class);
    }

    public function approvedWhitelists()
    {
        return $this->hasMany(DropWhitelist::class)->where('status', 'approved');
    }

    /**
     * Statut calculé du drop, jamais stocké en base :
     * dépend des dates ET du stock des produits associés.
     * Toutes les vues Blade qui lisent $drop->status continuent
     * de fonctionner sans aucune modification.
     */
    protected function status(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->end_date->isPast() || $this->isSoldOut()) {
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

        return (int) Variant::whereIn('product_id', $productIds)->sum('stock') === 0;
    }

    public function hasWhitelistSlotsAvailable(): bool
    {
        if (is_null($this->max_whitelist_slots)) {
            return true; // pas de limite définie
        }

        return $this->approvedWhitelists()->count() < $this->max_whitelist_slots;
    }
}