<?php

namespace App\Console\Commands;

use App\Enums\WhitelistStatus;
use App\Jobs\SendDropOpenedNotification;
use App\Models\DropWhitelist;
use Illuminate\Console\Command;
use Throwable;

class NotifyDropOpenedCommand extends Command
{
    protected $signature = 'drops:notify-opened';

    protected $description = 'Email les utilisateurs whitelistés lorsque leur drop vient de commencer.';

    public function handle(): int
    {
        $whitelists = DropWhitelist::query()
            ->where('status', WhitelistStatus::Approved->value)
            ->whereNull('drop_opened_notified_at')
            ->where('drop_opened_notification_status', 'pending')
            ->whereHas('drop', function ($query) {
                $query
                    ->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
            })
            ->with(['user', 'drop'])
            ->get();

        $queued = 0;

        foreach ($whitelists as $whitelist) {
            $claimed = DropWhitelist::query()
                ->whereKey($whitelist->id)
                ->where('drop_opened_notification_status', 'pending')
                ->update(['drop_opened_notification_status' => 'queued']);

            if ($claimed !== 1) {
                continue;
            }

            try {
                SendDropOpenedNotification::dispatch($whitelist->id);
                $queued++;
            } catch (Throwable $exception) {
                DropWhitelist::query()
                    ->whereKey($whitelist->id)
                    ->whereIn('drop_opened_notification_status', ['queued', 'processing'])
                    ->update(['drop_opened_notification_status' => 'failed']);

                report($exception);
            }
        }

        $this->info("{$queued} notification(s) de drop ouvert mise(s) en queue.");

        return self::SUCCESS;
    }
}
