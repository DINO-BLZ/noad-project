<?php

namespace Tests\Feature;

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
use Tests\TestCase;

class DropLifecycleAutomationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approved_users_are_notified_once_when_a_drop_starts(): void
    {
        Mail::fake();

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

        Mail::assertQueued(DropOpenedMail::class, 1);
        Mail::assertQueued(DropOpenedMail::class, function (DropOpenedMail $mail) use ($approvedUser, $drop) {
            return $mail->hasTo($approvedUser->email)
                && $mail->whitelist->drop_id === $drop->id;
        });

        $this->assertNotNull($approved->fresh()->drop_opened_notified_at);

        Artisan::call('drops:notify-opened');

        Mail::assertQueued(DropOpenedMail::class, 1);

        $this->travelBack();
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
