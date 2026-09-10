<?php

namespace App\Console\Commands;

use App\Mail\DropOpenedMail;
use App\Models\DropWhitelist;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class NotifyDropOpenedCommand extends Command
{
    protected $signature = 'drops:notify-opened';

    protected $description = 'Email les utilisateurs whitelistés lorsque leur drop vient de commencer.';

    public function handle(): int
    {
        $whitelists = DropWhitelist::query()
            ->where('status', 'approved')
            ->whereNull('drop_opened_notified_at')
            ->whereHas('drop', function ($query) {
                $query
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            })
            ->with(['user', 'drop'])
            ->get();

        $sent = 0;

        foreach ($whitelists as $whitelist) {
            if ($whitelist->user?->email) {
                Mail::to($whitelist->user->email)->queue(new DropOpenedMail($whitelist));
                $sent++;
            }

            $whitelist->update([
                'drop_opened_notified_at' => now(),
            ]);
        }

        $this->info("{$sent} notification(s) de drop ouvert envoyée(s).");

        return self::SUCCESS;
    }
}
