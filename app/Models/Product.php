<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'image'];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(Variant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function drops()
    {
        return $this->belongsToMany(Drop::class);
    }

    public function activeDrop()
    {
        if ($this->relationLoaded('drops')) {
            return $this->drops->first(fn ($drop) => $drop->isActive());
        }

        return $this->drops()->active()->first();
    }
}