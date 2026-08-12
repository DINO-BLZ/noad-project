<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'variant_id', 'quantity', 'price', 'variant_sku', 'variant_size', 'variant_color', 'product_name'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }
}
