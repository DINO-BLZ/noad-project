<?php

namespace App\Console\Commands;

use App\Models\DropWhitelist;
use Illuminate\Console\Command;

class ExpirePendingDropWhitelistsCommand extends Command
{
    protected $signature = 'drops:expire-pending-whitelists';

    protected $description = 'Ferme les demandes de whitelist encore en attente lorsque le drop est terminé.';

    public function handle(): int
    {
        $expired = DropWhitelist::query()
            ->where('status', 'pending')
            ->whereHas('drop', function ($query) {
                $query->where('end_date', '<', now());
            })
            ->update([
                'status' => 'expired',
            ]);

        $this->info("{$expired} demande(s) de whitelist expirée(s).");

        return self::SUCCESS;
    }
}
