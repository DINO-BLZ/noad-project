<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'image'];

    // Laravel utilisera automatiquement le champ "slug" pour le route model binding
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

    public function drops()
    {
        return $this->belongsToMany(Drop::class);
    }

    public function activeDrop()
    {
        return $this->drops()->active()->first();
    }
}