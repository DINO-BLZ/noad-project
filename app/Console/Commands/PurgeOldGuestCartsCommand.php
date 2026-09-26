<?php

namespace App\Console\Commands;

use App\Models\CartItem;
use Illuminate\Console\Command;

class PurgeOldGuestCartsCommand extends Command
{
    protected $signature = 'carts:purge-old-guests';

    protected $description = 'Supprime les paniers invités inactifs depuis plus de 7 jours.';

    public function handle(): int
    {
        $deleted = CartItem::query()
            ->whereNull('user_id')
            ->where('updated_at', '<', now()->subDays(7))
            ->delete();

        $this->info("{$deleted} article(s) de panier invité supprimé(s).");

        return self::SUCCESS;
    }
}