<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Variant extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'stock',
    ];

    protected $casts = [
        'stock' => 'integer',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function isInStock(): bool
    {
        return $this->stock > 0;
    }

    public function decreaseStock(int $quantity): void
    {
        if ($quantity > $this->stock) {
            throw new \RuntimeException(
                'Stock insuffisant.'
            );
        }

        $this->decrement('stock', $quantity);
    }
}