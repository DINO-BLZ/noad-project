<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Drop extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'start_date', 'end_date', 'status', 'max_whitelist_slots'];

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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeUpcoming($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'upcoming')
                ->orWhere('start_date', '>', now());
        });
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->start_date->isPast() && $this->end_date->isFuture();
    }

    public function isUpcoming(): bool
    {
        return $this->status === 'upcoming' || $this->start_date->isFuture();
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended' || $this->end_date->isPast();
    }

    public function isSoldOut(): bool
    {
        $productIds = $this->products()->pluck('products.id');

        return Variant::whereIn('product_id', $productIds)->sum('stock') === 0;
    }

    public function hasWhitelistSlotsAvailable(): bool
    {
        if (is_null($this->max_whitelist_slots)) {
            return true; // pas de limite définie
        }

        return $this->approvedWhitelists()->count() < $this->max_whitelist_slots;
    }
}
