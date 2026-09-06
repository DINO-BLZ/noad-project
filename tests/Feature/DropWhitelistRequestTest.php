<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Drop;
use App\Models\DropWhitelist;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropWhitelistRequestTest extends TestCase
{
    use RefreshDatabase;

    private function makeDrop(array $overrides = []): Drop
    {
        return Drop::create(array_merge([
            'name' => 'Test Drop',
            'slug' => 'test-drop-'.uniqid(),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(3),
            'status' => 'active',
        ], $overrides));
    }

    private function makeProductWithStock(Drop $drop, int $stock): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'cat-'.uniqid()]);

        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-'.uniqid(),
            'price' => 25.00,
            'category_id' => $category->id,
        ]);

        Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => $stock,
            'sku' => 'SKU-'.uniqid(),
            'color' => 'Blue',
        ]);

        $drop->products()->sync([$product->id]);
    }

    public function test_user_can_request_whitelist_for_an_active_drop_with_stock(): void
    {
        $user = User::factory()->create();
        $drop = $this->makeDrop();
        $this->makeProductWithStock($drop, 5);

        $response = $this->actingAs($user)->post(route('drops.request-whitelist', $drop->slug));

        $response->assertRedirect();
        $this->assertDatabaseHas('drop_whitelists', [
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_request_is_refused_for_an_ended_drop(): void
    {
        $user = User::factory()->create();
        $drop = $this->makeDrop([
            'status' => 'ended',
            'start_date' => now()->subDays(5),
            'end_date' => now()->subDay(),
        ]);
        $this->makeProductWithStock($drop, 5);

        $response = $this->actingAs($user)->post(route('drops.request-whitelist', $drop->slug));

        $response->assertForbidden();
        $this->assertDatabaseMissing('drop_whitelists', [
            'drop_id' => $drop->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_request_is_refused_when_drop_is_sold_out(): void
    {
        $user = User::factory()->create();
        $drop = $this->makeDrop();
        $this->makeProductWithStock($drop, 0);

        $response = $this->actingAs($user)->post(route('drops.request-whitelist', $drop->slug));

        $response->assertForbidden();
        $this->assertDatabaseMissing('drop_whitelists', [
            'drop_id' => $drop->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_duplicate_pending_request_does_not_create_a_second_row(): void
    {
        $user = User::factory()->create();
        $drop = $this->makeDrop();
        $this->makeProductWithStock($drop, 5);

        DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $this->actingAs($user)->post(route('drops.request-whitelist', $drop->slug));

        $this->assertDatabaseCount('drop_whitelists', 1);
    }

    public function test_request_after_a_rejection_resets_status_to_pending(): void
    {
        $user = User::factory()->create();
        $drop = $this->makeDrop();
        $this->makeProductWithStock($drop, 5);

        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'rejected',
        ]);

        $this->actingAs($user)->post(route('drops.request-whitelist', $drop->slug));

        $this->assertDatabaseCount('drop_whitelists', 1);
        $this->assertEquals('pending', $whitelist->fresh()->status);
    }

    public function test_request_when_already_approved_does_not_change_status(): void
    {
        $user = User::factory()->create();
        $drop = $this->makeDrop();
        $this->makeProductWithStock($drop, 5);

        $whitelist = DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $this->actingAs($user)->post(route('drops.request-whitelist', $drop->slug));

        $this->assertEquals('approved', $whitelist->fresh()->status);
    }

    public function test_guest_cannot_request_whitelist(): void
    {
        $drop = $this->makeDrop();
        $this->makeProductWithStock($drop, 5);

        $response = $this->post(route('drops.request-whitelist', $drop->slug));

        $response->assertRedirect(route('login'));
        $this->assertDatabaseCount('drop_whitelists', 0);
    }
}
