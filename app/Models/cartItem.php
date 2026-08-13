<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['user_id', 'session_id', 'variant_id', 'quantity'];

    public function variant()
    {
        return $this->belongsTo(Variant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Restreint la requête au panier de l'utilisateur connecté,
     * ou à celui de l'invité (via session_id) sinon.
     */
    public function scopeForOwner($query, ?int $userId, ?string $sessionId)
    {
        if ($userId) {
            return $query->where('user_id', $userId);
        }

        return $query->whereNull('user_id')->where('session_id', $sessionId);
    }
}