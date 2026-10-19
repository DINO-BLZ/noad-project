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
        $sessionId = 'guest-session-1';

        $this->createCartItem(null, $sessionId, $variant, 2);

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $sessionId,
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_quantities_are_added_when_both_carts_contain_the_same_variant(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('same-variant', 10);
        $sessionId = 'guest-session-1';

        $this->createCartItem($user->id, null, $variant, 2);
        $this->createCartItem(null, $sessionId, $variant, 3);

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $sessionId,
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_merged_quantity_is_capped_at_available_stock(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('stock-cap', 5);
        $sessionId = 'guest-session-1';

        $this->createCartItem($user->id, null, $variant, 4);
        $this->createCartItem(null, $sessionId, $variant, 4);

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 5,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $sessionId,
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_items_are_removed_from_both_carts_when_variant_stock_is_zero(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('zero-stock', 0);
        $sessionId = 'guest-session-1';

        $this->createCartItem($user->id, null, $variant, 2);
        $this->createCartItem(null, $sessionId, $variant, 3);

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertDatabaseMissing('cart_items', [
            'variant_id' => $variant->id,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $sessionId,
        ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_guest_variant_is_transferred_without_changing_other_user_cart_items(): void
    {
        $user = User::factory()->create();

        $userVariant = $this->createVariant('user-existing', 10);
        $guestVariant = $this->createVariant('guest-additional', 10);
        $sessionId = 'guest-session-1';

        $this->createCartItem($user->id, null, $userVariant, 2);
        $this->createCartItem(null, $sessionId, $guestVariant, 3);

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

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
            'session_id' => $sessionId,
        ]);

        $this->assertDatabaseCount('cart_items', 2);
    }

    public function test_empty_guest_cart_does_nothing_to_the_user_cart(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('empty-guest', 10);

        $this->createCartItem($user->id, null, $variant, 4);

        CartItem::mergeGuestCartIntoUser('empty-guest-session', $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 4,
        ]);

        $this->assertDatabaseCount('cart_items', 1);
    }

    public function test_multiple_guest_items_are_transferred_with_quantities_capped_at_stock(): void
    {
        $user = User::factory()->create();
        $sessionId = 'guest-session-multiple';

        $variant1 = $this->createVariant('multiple-1', 10);
        $variant2 = $this->createVariant('multiple-2', 6);
        $variant3 = $this->createVariant('multiple-3', 2);

        $this->createCartItem(null, $sessionId, $variant1, 2);
        $this->createCartItem(null, $sessionId, $variant2, 4);
        $this->createCartItem(null, $sessionId, $variant3, 5);

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant2->id,
            'quantity' => 4,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant3->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $sessionId,
        ]);

        $this->assertDatabaseCount('cart_items', 3);
    }

    public function test_items_from_another_guest_session_are_left_untouched(): void
    {
        $user = User::factory()->create();

        $targetVariant = $this->createVariant('target-session', 10);
        $otherVariant = $this->createVariant('other-session', 10);

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
        $variant = $this->createVariant('deleted-variant', 10);
        $sessionId = 'guest-session-deleted';

        $this->createCartItem(null, $sessionId, $variant, 2);

        $variant->delete();

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $sessionId,
            'variant_id' => $variant->id,
        ]);

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'variant_id' => $variant->id,
        ]);

        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_merge_failure_is_logged_without_exposing_session_id(): void
    {
        $user = User::factory()->create();
        $variant = $this->createVariant('forced-failure', 10);
        $sessionId = 'guest-session-sensitive-12345';

        $this->createCartItem(null, $sessionId, $variant, 2);

        $failureInjected = false;

        DB::connection()->beforeExecuting(
            function (string $query) use (
                &$failureInjected,
                $sessionId
            ): void {
                if (
                    $failureInjected
                    || ! str_contains(strtolower($query), 'variants')
                ) {
                    return;
                }

                $failureInjected = true;

                throw new RuntimeException(
                    "Forced failure for session {$sessionId}"
                );
            }
        );

        $expectedCause = sprintf(
            'RuntimeException: Forced failure for session %s',
            '[session-id-redacted]'
        );

        Log::shouldReceive('warning')
            ->once()
            ->with(
                'Guest cart merge failed; authentication may continue.',
                [
                    'user_id' => $user->id,
                    'cause' => $expectedCause,
                ]
            );

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertTrue(
            $failureInjected,
            'The simulated failure must occur before the variants query executes.'
        );

        $this->assertDatabaseHas('cart_items', [
            'user_id' => null,
            'session_id' => $sessionId,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'variant_id' => $variant->id,
        ]);
    }

    public function test_merge_retries_when_a_new_guest_variant_appears(): void
    {
        $user = User::factory()->create();
        $sessionId = 'guest-session-retry';

        $variant1 = $this->createVariant('retry-1', 10);
        $variant2 = $this->createVariant('retry-2', 10);

        $this->createCartItem(null, $sessionId, $variant1, 2);

        $connection = DB::connection();
        $baselineTransactionLevel = $connection->transactionLevel();
        $injected = false;

        DB::listen(function ($query) use (
            &$injected,
            $baselineTransactionLevel,
            $connection,
            $sessionId,
            $variant2
        ): void {
            $sql = strtolower(ltrim($query->sql));

            if (
                $injected
                || $connection->transactionLevel() !== $baselineTransactionLevel
                || ! str_starts_with($sql, 'select')
                || ! str_contains($sql, 'distinct')
                || ! str_contains($sql, 'variant_id')
                || ! str_contains($sql, 'cart_items')
                || ! str_contains($sql, 'session_id')
            ) {
                return;
            }

            // This is the initial variant-set read, outside the merge transaction.
            // The new row therefore survives the rollback of the first attempt.
            $injected = true;

            CartItem::create([
                'user_id' => null,
                'session_id' => $sessionId,
                'variant_id' => $variant2->id,
                'quantity' => 3,
            ]);
        });

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertTrue($injected);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant2->id,
            'quantity' => 3,
        ]);

        $this->assertDatabaseMissing('cart_items', [
            'session_id' => $sessionId,
        ]);

        $this->assertDatabaseCount('cart_items', 2);
    }

    public function test_merge_abandons_after_three_variant_set_changes(): void
    {
        $user = User::factory()->create();
        $sessionId = 'guest-session-abandon';

        $variant1 = $this->createVariant('abandon-1', 10);
        $variant2 = $this->createVariant('abandon-2', 10);
        $variant3 = $this->createVariant('abandon-3', 10);
        $variant4 = $this->createVariant('abandon-4', 10);

        $this->createCartItem(null, $sessionId, $variant1, 1);

        $connection = DB::connection();
        $baselineTransactionLevel = $connection->transactionLevel();

        $variantsToInject = [
            $variant2,
            $variant3,
            $variant4,
        ];

        $injectionCount = 0;

        DB::listen(function ($query) use (
            &$injectionCount,
            $variantsToInject,
            $baselineTransactionLevel,
            $connection,
            $sessionId
        ): void {
            $sql = strtolower(ltrim($query->sql));

            /*
             * Target only the initial distinct variant-ID query.
             *
             * The transaction-level check excludes the later currentVariantIds
             * verification inside the transaction.
             *
             * The SELECT-only condition prevents CartItem::create() from
             * recursively triggering this listener.
             */
            if (
                $connection->transactionLevel() !== $baselineTransactionLevel
                || ! str_starts_with($sql, 'select')
                || ! str_contains($sql, 'distinct')
                || ! str_contains($sql, 'variant_id')
                || ! str_contains($sql, 'cart_items')
                || ! str_contains($sql, 'session_id')
                || $injectionCount >= count($variantsToInject)
            ) {
                return;
            }

            $variant = $variantsToInject[$injectionCount];

            CartItem::create([
                'user_id' => null,
                'session_id' => $sessionId,
                'variant_id' => $variant->id,
                'quantity' => 1,
            ]);

            $injectionCount++;
        });

        Log::shouldReceive('warning')
            ->once()
            ->with(
                'Guest cart merge abandoned after variant-set changes.',
                [
                    'user_id' => $user->id,
                    'cause' => 'A new guest-cart variant appeared during all merge attempts.',
                    'attempts' => 3,
                ]
            );

        CartItem::mergeGuestCartIntoUser($sessionId, $user->id);

        $this->assertSame(
            3,
            $injectionCount,
            'Exactly one new variant must be inserted during each of the three attempts.'
        );

        foreach ([$variant1, $variant2, $variant3, $variant4] as $variant) {
            $this->assertDatabaseHas('cart_items', [
                'user_id' => null,
                'session_id' => $sessionId,
                'variant_id' => $variant->id,
                'quantity' => 1,
            ]);
        }

        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
        ]);

        $this->assertDatabaseCount('cart_items', 4);
    }
}
