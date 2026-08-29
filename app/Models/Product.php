<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'slug',
        'price',
        'description',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(Variant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)
            ->where('is_primary', true);
    }

    public function drops(): BelongsToMany
    {
        return $this->belongsToMany(Drop::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Drop helpers
    |--------------------------------------------------------------------------
    */

    public function activeDrop(): ?Drop
    {
        return $this->drops()
            ->active()
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->latest('start_date')
            ->first();
    }

    public function upcomingDrop(): ?Drop
    {
        return $this->drops()
            ->upcoming()
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->first();
    }
}