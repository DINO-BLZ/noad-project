<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;

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

    /*
    |--------------------------------------------------------------------------
    | Scout / Elasticsearch
    |--------------------------------------------------------------------------
    */

    /**
     * Nom de l'index Elasticsearch utilisé pour ce modèle.
     */
    public function searchableAs(): string
    {
        return 'products_index';
    }

    /**
     * Données envoyées à Elasticsearch à chaque indexation.
     * On inclut le nom de catégorie pour permettre une recherche
     * du type "chaussures nike" sans jointure côté ES.
     */
    public function toSearchableArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'category' => $this->category?->name,
            'price' => (float) $this->price,
        ];
    }

    /**
     * N'indexe que les produits rattachés à une catégorie valide
     * (évite d'indexer un produit en cours de création incomplète).
     */
    public function shouldBeSearchable(): bool
    {
        return $this->category_id !== null;
    }

    /*
    |--------------------------------------------------------------------------
    | Nettoyage des fichiers
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::deleting(function (Product $product) {
            $product->images->each->delete();

            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
        });
    }
}
