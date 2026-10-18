<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id', 'full_name', 'phone', 'address', 'wilaya',
        'payment_method', 'status', 'payment_status', 'total', 'checkout_token',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'payment_status' => PaymentStatus::class,
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * Référence de commande normalisée : ND-000012.
     *
     * Format unique utilisé dans toutes les vues et emails.
     * Remplace les 4 formats ad-hoc qui existaient avant (#12,
     * ND-2026-12, ND-000012 avec l'année codée en dur).
     */
    public function getFormattedReferenceAttribute(): string
    {
        return 'ND-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
