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
    /**
     * Fusionne le panier invité (par session) dans le panier de l'utilisateur
     * qui vient de se connecter ou de s'inscrire. Si l'utilisateur avait déjà
     * la même variante dans son panier, les quantités s'additionnent.
     */
    public static function mergeGuestCartIntoUser(string $sessionId, int $userId): void
    {
        $guestItems = static::where('session_id', $sessionId)->whereNull('user_id')->get();

        foreach ($guestItems as $guestItem) {
            $existing = static::where('user_id', $userId)
                ->where('variant_id', $guestItem->variant_id)
                ->first();

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $guestItem->quantity]);
                $guestItem->delete();
            } else {
                $guestItem->update(['user_id' => $userId, 'session_id' => null]);
            }
        }
    }
}