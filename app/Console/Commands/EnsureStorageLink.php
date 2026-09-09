<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class EnsureStorageLink extends Command
{
    protected $signature = 'storage:relink';

    protected $description = 'Recrée le lien public/storage, en supprimant proprement un lien, dossier ou jonction Windows existant au préalable.';

    public function handle(): int
    {
        $link = public_path('storage');

        if (file_exists($link)) {
            if (! @rmdir($link)) {
                @unlink($link);
            }
        }

        $this->call('storage:link');

        return self::SUCCESS;
    }
}