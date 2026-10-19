<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery;
use RuntimeException;
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

    public function test_empty_guest_cart_does_nothing_to_the_user_cart(): void
    {
        $user = User::factory()->create();
        $userVariant = $this->createVariant('empty-guest-user-item', 10);

        $this->createCartItem($user->id, null, $userVariant, 4);

        CartItem::mergeGuestCartIntoUser('empty-guest-session', $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $userVariant->id,
            'quantity' => 4,
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_multiple_guest_items_are_all_transferred_to_the_user(): void
    {
        $user = User::factory()->create();

        $firstVariant = $this->createVariant('multiple-guest-first', 10);
        $secondVariant = $this->createVariant('multiple-guest-second', 6);
        $thirdVariant = $this->createVariant('multiple-guest-third', 2);

        $this->createCartItem(null, 'guest-session-1', $firstVariant, 2);
        $this->createCartItem(null, 'guest-session-1', $secondVariant, 4);
        $this->createCartItem(null, 'guest-session-1', $thirdVariant, 5);

        CartItem::mergeGuestCartIntoUser('guest-session-1', $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $firstVariant->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $secondVariant->id,
            'quantity' => 4,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $thirdVariant->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => 'guest-session-1',
        ]);

        $this->assertDatabaseCount('cart_items', 3);
    }

    public function test_items_from_another_guest_session_are_left_untouched(): void
    {
        $user = User::factory()->create();

        $targetVariant = $this->createVariant('target-guest-session', 10);
        $otherVariant = $this->createVariant('other-guest-session', 10);

        $this->createCartItem(null, 'guest-session-1', $targetVariant, 2);
        $this->createCartItem(null, 'guest-session-2', $otherVariant, 3);

        CartItem::mergeGuestCartIntoUser('guest-session-1', $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $targetVariant->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => null,
            'session_id' => 'guest-session-2',
            'variant_id' => $otherVariant->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => 'guest-session-1',
        ]);

        $this->assertDatabaseCount('cart_items', 2);
    }

    public function test_deleting_a_variant_cascades_to_its_cart_item_before_merge(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('deleted-variant-cascade', 10);

        $this->createCartItem(null, 'guest-session-1', $variant, 2);

        $variant->delete();

        // The FK cascade removes the cart row, so merge has nothing to process.
        $this->assertDatabaseMissing('cart_items', [
            'session_id' => 'guest-session-1',
            'variant_id' => $variant->id,
        ]);

        CartItem::mergeGuestCartIntoUser('guest-session-1', $user->id);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_merge_failure_is_logged_without_exposing_session_id(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('merge-forced-failure', 10);
        $sessionId = 'guest-session-sensitive-12345';

        $this->createCartItem(null, $sessionId, $variant, 2);

        // Register the listener only after preparing the test data.
        // The SQL query has already executed when this callback is invoked.
        // Throwing here makes the query fail from the application's perspective
        // and allows the merge transaction to roll back.
        DB::listen(function ($query) use ($sessionId): void {
            if (str_contains(strtolower($query->sql), 'variants')) {
                throw new RuntimeException(
                    "Forced failure for session {$sessionId}"
                );
            }
        });

        Log::shouldReceive('warning')
            ->once()
            ->with(
                'Guest cart merge failed; authentication may continue.',
                Mockery::on(function (array $context) use ($user, $sessionId): bool {
                    $expectedCause = 'RuntimeException: Forced failure for session '
                        .'[session-id-redacted]';

                    $cause = $context['cause'] ?? '';

                    return ($context['user_id'] ?? null) === $user->id
                        && $cause === $expectedCause
                        && ! str_contains($cause, $sessionId);
                })
            );

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => null,
            'session_id' => $sessionId,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }
}
