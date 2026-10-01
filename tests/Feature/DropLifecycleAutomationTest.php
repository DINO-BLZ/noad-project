<?php

namespace Tests\Feature;

use App\Jobs\SendDropOpenedNotification;
use App\Mail\DropOpenedMail;
use App\Models\Category;
use App\Models\Drop;
use App\Models\DropWhitelist;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DropLifecycleAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_users_are_notified_once_when_a_drop_starts(): void
    {
        Queue::fake();

        $this->travelTo(now()->startOfHour());

        $drop = $this->makeDrop([
            'start_date' => now()->subMinute(),
            'end_date' => now()->addDays(2),
        ]);

        $approvedUser = User::factory()->create();
        $pendingUser = User::factory()->create();
        $otherDropUser = User::factory()->create();

        $approved = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $approvedUser->id,
            'status' => 'approved',
        ]);

        DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $pendingUser->id,
            'status' => 'pending',
        ]);

        $upcomingDrop = $this->makeDrop([
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(3),
        ]);

        DropWhitelist::create([
            'drop_id' => $upcomingDrop->id,
            'user_id' => $otherDropUser->id,
            'status' => 'approved',
        ]);

        Artisan::call('drops:notify-opened');

        Queue::assertPushed(SendDropOpenedNotification::class, 1);
        Queue::assertPushed(SendDropOpenedNotification::class, function (SendDropOpenedNotification $job) use ($approved) {
            return $job->whitelistId === $approved->id;
        });

        $this->assertNull($approved->fresh()->drop_opened_notified_at);
        $this->assertSame('queued', $approved->fresh()->drop_opened_notification_status);

        Artisan::call('drops:notify-opened');

        Queue::assertPushed(SendDropOpenedNotification::class, 1);

        $this->travelBack();
    }

    public function test_notification_is_marked_sent_only_after_mail_send_succeeds(): void
    {
        Mail::fake();

        $drop = $this->makeDrop();
        $user = User::factory()->create();
        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'drop_opened_notification_status' => 'queued',
        ]);

        (new SendDropOpenedNotification($whitelist->id))->handle();

        Mail::assertSent(DropOpenedMail::class, fn (DropOpenedMail $mail) => $mail->hasTo($user->email));
        $this->assertSame('sent', $whitelist->fresh()->drop_opened_notification_status);
        $this->assertNotNull($whitelist->fresh()->drop_opened_notified_at);
    }

    public function test_terminal_failure_is_not_requeued_by_scheduler_but_can_be_retried_manually(): void
    {
        Queue::fake();

        $drop = $this->makeDrop();
        $user = User::factory()->create();
        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'drop_opened_notification_status' => 'processing',
        ]);

        (new SendDropOpenedNotification($whitelist->id))->failed(new \RuntimeException('Mail delivery failed'));

        $this->assertSame('failed', $whitelist->fresh()->drop_opened_notification_status);

        Artisan::call('drops:notify-opened');

        $this->assertSame('failed', $whitelist->fresh()->drop_opened_notification_status);
        Queue::assertNothingPushed();

        Mail::fake();
        (new SendDropOpenedNotification($whitelist->id))->handle();

        Mail::assertSent(DropOpenedMail::class);
        $this->assertSame('sent', $whitelist->fresh()->drop_opened_notification_status);
    }

    public function test_synchronous_dispatch_failure_does_not_leave_notification_processing(): void
    {
        config(['queue.default' => 'sync']);

        $drop = $this->makeDrop();
        $user = User::factory()->create();
        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'approved',
            'drop_opened_notification_status' => 'pending',
        ]);

        Mail::shouldReceive('to')
            ->once()
            ->with($user->email)
            ->andThrow(new \RuntimeException('Mail transport unavailable'));

        Artisan::call('drops:notify-opened');

        $this->assertSame('failed', $whitelist->fresh()->drop_opened_notification_status);
    }

    public function test_pending_whitelist_requests_expire_when_a_drop_ends(): void
    {
        $endedDrop = $this->makeDrop([
            'start_date' => now()->subDays(3),
            'end_date' => now()->subMinute(),
        ]);

        $activeDrop = $this->makeDrop([
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
        ]);

        $pendingEnded = DropWhitelist::create([
            'drop_id' => $endedDrop->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'pending',
        ]);

        $approvedEnded = DropWhitelist::create([
            'drop_id' => $endedDrop->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'approved',
        ]);

        $pendingActive = DropWhitelist::create([
            'drop_id' => $activeDrop->id,
            'user_id' => User::factory()->create()->id,
            'status' => 'pending',
        ]);

        Artisan::call('drops:expire-pending-whitelists');

        $this->assertSame('expired', $pendingEnded->fresh()->status);
        $this->assertSame('approved', $approvedEnded->fresh()->status);
        $this->assertSame('pending', $pendingActive->fresh()->status);
    }

    public function test_product_reindex_is_skipped_when_scout_is_disabled(): void
    {
        $exitCode = Artisan::call('search:reindex-products');

        $this->assertSame(0, $exitCode);
        $this->assertStringContainsString('Scout est désactivé', Artisan::output());
    }

    public function test_drop_and_search_jobs_are_registered_on_the_scheduler(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('drops:notify-opened')
            ->expectsOutputToContain('drops:expire-pending-whitelists')
            ->expectsOutputToContain('search:reindex-products')
            ->assertSuccessful();
    }

    public function test_application_and_scheduler_use_algeria_timezone(): void
    {
        $this->assertSame('Africa/Algiers', config('app.timezone'));
        $this->assertSame('Africa/Algiers', now()->timezoneName);

        $this->artisan('schedule:list --json')
            ->expectsOutputToContain('"timezone":"Africa\/Algiers"')
            ->assertSuccessful();
    }

    private function makeDrop(array $overrides = []): Drop
    {
        $drop = Drop::create(array_merge([
            'name' => 'Test Drop',
            'slug' => 'test-drop-'.uniqid(),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(3),
        ], $overrides));

        $category = Category::create([
            'name' => 'Test',
            'slug' => 'cat-'.uniqid(),
        ]);

        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-'.uniqid(),
            'price' => 25,
            'category_id' => $category->id,
        ]);

        Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 5,
            'sku' => 'SKU-'.uniqid(),
            'color' => 'Blue',
        ]);

        $drop->products()->sync([$product->id]);

        return $drop;
    }
}
