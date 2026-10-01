<?php

namespace App\Jobs;

use App\Enums\WhitelistStatus;
use App\Mail\DropOpenedMail;
use App\Models\DropWhitelist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendDropOpenedNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [60, 300];

    public function __construct(public int $whitelistId) {}

    public function handle(): void
    {
        $whitelist = DropWhitelist::with(['drop', 'user'])->find($this->whitelistId);

        if (! $whitelist || $whitelist->drop_opened_notification_status !== 'queued') {
            return;
        }

        if (
            $whitelist->status !== WhitelistStatus::Approved->value
            || $whitelist->drop_opened_notified_at !== null
        ) {
            $whitelist->update(['drop_opened_notification_status' => 'pending']);

            return;
        }

        if (! $whitelist->user?->email) {
            throw new \RuntimeException('Le candidat whitelist ne possède pas d’adresse e-mail.');
        }

        Mail::to($whitelist->user->email)->send(new DropOpenedMail($whitelist));

        $whitelist->update([
            'drop_opened_notified_at' => now(),
            'drop_opened_notification_status' => 'sent',
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        DropWhitelist::query()
            ->whereKey($this->whitelistId)
            ->where('drop_opened_notification_status', 'queued')
            ->update(['drop_opened_notification_status' => 'failed']);
    }
}
