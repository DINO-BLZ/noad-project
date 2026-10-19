<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

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
     * Fusionne le panier invité dans le panier utilisateur.
     *
     * Ordre des verrous : variantes (V) par ID croissant, puis lignes
     * invitées (C) par ID croissant, puis lignes utilisateur (C) par ID
     * croissant. Seules les lignes utilisateur des variantes invitées sont
     * verrouillées.
     *
     * Une nouvelle variante apparue depuis la lecture initiale déclenche une
     * nouvelle tentative. Les suppressions intervenues entre-temps sont
     * inoffensives : une variante verrouillée en trop n'empêche pas la fusion.
     *
     * L'échec du merge ne doit jamais interrompre l'authentification : après
     * épuisement des tentatives ou une exception, l'échec est journalisé et
     * la méthode retourne normalement.
     */
    public static function mergeGuestCartIntoUser(string $sessionId, int $userId): void
    {
        $maxSetAttempts = 3;

        try {
            for ($attempt = 1; $attempt <= $maxSetAttempts; $attempt++) {
                // Cette lecture prépare uniquement la liste des verrous à prendre.
                $initialVariantIds = static::query()
                    ->whereNull('user_id')
                    ->where('session_id', $sessionId)
                    ->distinct()
                    ->orderBy('variant_id')
                    ->pluck('variant_id')
                    ->map(static fn ($id): int => (int) $id)
                    ->all();

                if ($initialVariantIds === []) {
                    return;
                }

                $setUnchanged = DB::transaction(function () use (
                    $sessionId,
                    $userId,
                    $initialVariantIds
                ): bool {
                    // V : verrouiller les variantes existantes par ID croissant.
                    $variants = Variant::query()
                        ->whereIn('id', $initialVariantIds)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get(['id', 'stock']);

                    $lockedVariantIds = $variants
                        ->pluck('id')
                        ->map(static fn ($id): int => (int) $id)
                        ->all();

                    // C invité : uniquement les lignes correspondant aux V verrouillées,
                    // dans l'ordre déterministe de leur clé primaire.
                    $guestItems = static::query()
                        ->whereNull('user_id')
                        ->where('session_id', $sessionId)
                        ->whereIn('variant_id', $lockedVariantIds)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get();

                    // C utilisateur : seulement les variantes du panier invité,
                    // et toujours par ID croissant.
                    $userItems = static::query()
                        ->where('user_id', $userId)
                        ->whereIn('variant_id', $lockedVariantIds)
                        ->orderBy('id')
                        ->lockForUpdate()
                        ->get()
                        ->keyBy('variant_id');

                    // Vérification après les verrous : seule une variante ajoutée
                    // exige une reprise. Une variante retirée, y compris supprimée
                    // en base avec cascade, ne rend pas cette tentative dangereuse.
                    $currentVariantIds = static::query()
                        ->whereNull('user_id')
                        ->where('session_id', $sessionId)
                        ->distinct()
                        ->orderBy('variant_id')
                        ->pluck('variant_id')
                        ->map(static fn ($id): int => (int) $id)
                        ->all();

                    $addedVariantIds = array_diff($currentVariantIds, $initialVariantIds);

                    if ($addedVariantIds !== []) {
                        return false;
                    }

                    $variantsById = $variants->keyBy('id');

                    foreach ($guestItems as $guestItem) {
                        $variant = $variantsById->get($guestItem->variant_id);

                        // Défense supplémentaire : la FK cascade devrait normalement
                        // avoir supprimé cette ligne si la variante n'existe plus.
                        if (! $variant) {
                            $guestItem->delete();

                            continue;
                        }

                        $existing = $userItems->get($guestItem->variant_id);
                        $desiredQuantity = ($existing?->quantity ?? 0) + $guestItem->quantity;
                        $finalQuantity = min($desiredQuantity, (int) $variant->stock);

                        if ($finalQuantity <= 0) {
                            $guestItem->delete();
                            $existing?->delete();
                            $userItems->forget($guestItem->variant_id);

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

                            // La ligne transférée devient désormais la ligne utilisateur
                            // de cette variante pour toute éventuelle ligne suivante.
                            $userItems->put($guestItem->variant_id, $guestItem);
                        }
                    }

                    return true;
                }, 3); // Laravel réessaie lui-même les deadlocks jusqu'à 3 fois.

                if ($setUnchanged) {
                    return;
                }
            }

            Log::warning('Guest cart merge abandoned after variant-set changes.', [
                'user_id' => $userId,
                'cause' => 'A new guest-cart variant appeared during all merge attempts.',
                'attempts' => $maxSetAttempts,
            ]);
        } catch (Throwable $exception) {
            // Ne jamais inclure l'identifiant complet de session dans le journal.
            $cause = $exception::class.': '.$exception->getMessage();

            if ($sessionId !== '') {
                $cause = str_replace($sessionId, '[session-id-redacted]', $cause);
            }

            Log::warning('Guest cart merge failed; authentication may continue.', [
                'user_id' => $userId,
                'cause' => $cause,
            ]);
        }
    }
}
