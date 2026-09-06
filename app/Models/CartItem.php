<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
     * la même variante dans son panier, les quantités s'additionnent, plafonnées
     * au stock réellement disponible.
     */
    public static function mergeGuestCartIntoUser(string $sessionId, int $userId): void
    {
        DB::transaction(function () use ($sessionId, $userId) {
            $guestItems = static::where('session_id', $sessionId)
                ->whereNull('user_id')
                ->lockForUpdate()
                ->get();

            foreach ($guestItems as $guestItem) {
                $variant = Variant::lockForUpdate()->find($guestItem->variant_id);

                if (! $variant) {
                    // La variante a été supprimée entre-temps : on abandonne cet item.
                    $guestItem->delete();

                    continue;
                }

                $existing = static::where('user_id', $userId)
                    ->where('variant_id', $guestItem->variant_id)
                    ->lockForUpdate()
                    ->first();

                $desiredQuantity = ($existing?->quantity ?? 0) + $guestItem->quantity;
                $finalQuantity = min($desiredQuantity, $variant->stock);

                if ($finalQuantity <= 0) {
                    $guestItem->delete();
                    $existing?->delete();

                    continue;
                }

                if ($existing) {
                    $existing->update(['quantity' => $finalQuantity]);
                    $guestItem->delete();
                } else {
                    $guestItem->update([
                        'user_id' => $userId,
                        'session_id' => null,
                        'quantity' => $finalQuantity,
                    ]);
                }
            }
        });
    }
}
