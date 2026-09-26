<?php

namespace Tests\Feature\Console;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PurgeOldDataCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_guest_cart_is_deleted(): void
    {
        $variant = $this->createVariant();

        $cartItem = CartItem::create([
            'user_id' => null,
            'session_id' => 'guest-old-session',
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $cartItem->forceFill([
            'updated_at' => now()->subDays(8),
        ])->save();

        Artisan::call('carts:purge-old-guests');

        $this->assertDatabaseMissing('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_recent_guest_cart_is_not_deleted(): void
    {
        $variant = $this->createVariant();

        $cartItem = CartItem::create([
            'user_id' => null,
            'session_id' => 'guest-recent-session',
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $cartItem->forceFill([
            'updated_at' => now()->subDays(3),
        ])->save();

        Artisan::call('carts:purge-old-guests');

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
        ]);
    }

    public function test_authenticated_user_cart_is_never_deleted(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant();

        $cartItem = CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $cartItem->forceFill([
            'updated_at' => now()->subDays(30),
        ])->save();

        Artisan::call('carts:purge-old-guests');

        $this->assertDatabaseHas('cart_items', [
            'id' => $cartItem->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_old_password_reset_token_is_deleted_and_recent_token_remains(): void
    {
        DB::table('password_reset_tokens')->insert([
            'email' => 'old@example.com',
            'token' => 'old-token',
            'created_at' => now()->subHours(25),
        ]);

        DB::table('password_reset_tokens')->insert([
            'email' => 'recent@example.com',
            'token' => 'recent-token',
            'created_at' => now()->subHour(),
        ]);

        Artisan::call('auth:purge-old-password-reset-tokens');

        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'old@example.com',
        ]);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'recent@example.com',
            'token' => 'recent-token',
        ]);
    }

    private function createVariant(): Variant
    {
        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category-' . uniqid(),
        ]);

        $product = Product::create([
            'name' => 'Test Product',
            'slug' => 'test-product-' . uniqid(),
            'price' => 100,
            'category_id' => $category->id,
        ]);

        return Variant::create([
            'product_id' => $product->id,
            'sku' => 'SKU-' . uniqid(),
            'size' => 'M',
            'color' => 'Black',
            'stock' => 10,
        ]);
    }
}