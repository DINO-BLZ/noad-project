<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;

    protected $fillable = ['category_id', 'name', 'slug', 'description', 'price', 'image'];

    /**
     * Les champs envoyés à Elasticsearch pour ce produit.
     * Seuls "name" et "description" sont utilisés pour la recherche pour l'instant.
     */
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
        ];
    }

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
        if ($this->relationLoaded('drops')) {
            return $this->drops->first(fn ($drop) => $drop->isActive());
        }

        return $this->drops()->active()->first();
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('position');
    }

    public function primaryImage()
    {
        return $this->images()->where('is_primary', true)->first();
    }
 public function upcomingDrop()
{
    if ($this->relationLoaded('drops')) {
        return $this->drops->first(fn ($drop) => $drop->isUpcoming());
    }

    return $this->drops()->upcoming()->first();
}
}