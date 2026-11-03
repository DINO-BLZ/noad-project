<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;
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
            ->current()
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
        return 'products';
    }

    /**
     * Données envoyées à Elasticsearch à chaque indexation.
     * On inclut le nom de catégorie pour permettre une recherche
     * du type "chaussures nike" sans jointure côté ES.
     */
    public function toSearchableArray(): array
    {
        $this->loadMissing('category');

        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
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

    /**
     * Charge les relations nécessaires lors de l'import massif.
     */
    public function makeAllSearchableUsing($query)
    {
        return $query->with('category');
    }

    /**
     * Rend la synchronisation Scout tolérante aux pannes si le cluster
     * de recherche (Elasticsearch) est indisponible ou hors-ligne,
     * tout en consignant et rapportant l'erreur.
     */
    public function syncMakeSearchable($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        try {
            return $models
                ->first()
                ->makeSearchableUsing($models)
                ->first()
                ->searchableUsing()
                ->update($models);
        } catch (\Throwable $exception) {
            report($exception);
            Log::error('Erreur lors de la synchronisation de l\'index Scout (makeSearchable) : '.$exception->getMessage(), [
                'exception' => $exception,
                'product_ids' => $models->pluck('id')->all(),
            ]);
        }
    }

    public function syncRemoveFromSearch($models)
    {
        if ($models->isEmpty()) {
            return;
        }

        try {
            return $models->first()->searchableUsing()->delete($models);
        } catch (\Throwable $exception) {
            report($exception);
            Log::error('Erreur lors de la suppression de l\'index Scout (removeFromSearch) : '.$exception->getMessage(), [
                'exception' => $exception,
                'product_ids' => $models->pluck('id')->all(),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Nettoyage des fichiers
    |--------------------------------------------------------------------------
    |
    | La suppression physique des fichiers est gérée par DeleteProductAction
    | APRÈS le commit de la transaction SQL. Le model event se contente de
    | supprimer les lignes en base (images galerie) sans toucher au disque,
    | afin qu'un rollback ne laisse pas de lignes orphelines pointant vers
    | des fichiers déjà supprimés.
    |
    | ProductImage possède un hook deleting qui supprime le fichier physique.
    | On le désactive ici car la suppression physique est différée au
    | post-commit dans DeleteProductAction.
    */

    protected static function booted()
    {
        static::deleting(function (Product $product) {
            ProductImage::withoutEvents(function () use ($product) {
                $product->images()->delete();
            });
        });
    }
}
