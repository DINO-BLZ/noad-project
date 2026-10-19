<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MergeGuestCartTest extends TestCase
{
    use RefreshDatabase;

    private function createVariant(string $suffix, int $stock): Variant
    {
        $category = Category::create([
            'name' => "Category {$suffix}",
            'slug' => "category-{$suffix}",
        ]);

        $product = Product::create([
            'name' => "Product {$suffix}",
            'slug' => "product-{$suffix}",
            'price' => 2500,
            'category_id' => $category->id,
        ]);

        return Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => $stock,
            'sku' => "SKU-{$suffix}",
            'color' => 'Black',
        ]);
    }

    private function createCartItem(
        ?int $userId,
        ?string $sessionId,
        Variant $variant,
        int $quantity
    ): CartItem {
        return CartItem::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);
    }

    public function test_guest_cart_is_transferred_to_user_when_user_cart_is_empty(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('guest-only', 10);

        $this->createCartItem(null, 'guest-session-1', $variant, 2);

        CartItem::mergeGuestCartIntoUser('guest-session-1', $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => 'guest-session-1',
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_quantities_are_added_when_both_carts_contain_the_same_variant(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('same-variant', 10);

        $this->createCartItem($user->id, null, $variant, 2);
        $this->createCartItem(null, 'guest-session-1', $variant, 3);

        CartItem::mergeGuestCartIntoUser('guest-session-1', $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => 'guest-session-1',
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_merged_quantity_is_capped_at_available_stock(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('stock-cap', 5);

        $this->createCartItem($user->id, null, $variant, 4);
        $this->createCartItem(null, 'guest-session-1', $variant, 4);

        CartItem::mergeGuestCartIntoUser('guest-session-1', $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => 'guest-session-1',
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_items_are_removed_from_both_carts_when_variant_stock_is_zero(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('zero-stock', 0);

        $this->createCartItem($user->id, null, $variant, 2);
        $this->createCartItem(null, 'guest-session-1', $variant, 3);

        CartItem::mergeGuestCartIntoUser('guest-session-1', $user->id);

        $this->assertDatabaseMissing('cart_items', [
            'variant_id' => $variant->id,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => 'guest-session-1',
        ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_variant_is_transferred_without_changing_other_user_cart_items(): void
    {
        $user = User::factory()->create();

        $userVariant = $this->createVariant('user-existing', 10);
        $guestVariant = $this->createVariant('guest-additional', 10);

        $this->createCartItem($user->id, null, $userVariant, 2);
        $this->createCartItem(null, 'guest-session-1', $guestVariant, 3);

        CartItem::mergeGuestCartIntoUser('guest-session-1', $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $userVariant->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $guestVariant->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => 'guest-session-1',
        ]);

        $this->assertDatabaseCount('cart_items', 2);
    }
}
