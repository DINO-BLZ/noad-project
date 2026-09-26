<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PurgeOldPasswordResetTokensCommand extends Command
{
    protected $signature = 'auth:purge-old-password-reset-tokens';

    protected $description = 'Supprime les tokens de réinitialisation de mot de passe âgés de plus de 24 heures.';

    public function handle(): int
    {
        $deleted = DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subHours(24))
            ->delete();

        $this->info("{$deleted} token(s) de réinitialisation supprimé(s).");

        return self::SUCCESS;
    }
}