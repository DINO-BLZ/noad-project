<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'drop_id', 'product_id', 'variant_id', 'quantity', 'price', 'variant_sku', 'variant_size', 'variant_color', 'product_name', 'created_at', 'updated_at'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function drop()
    {
        return $this->belongsTo(Drop::class);
    }
}
