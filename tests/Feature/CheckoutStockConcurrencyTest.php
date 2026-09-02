<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckoutStockConcurrencyTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_rejects_order_when_quantity_exceeds_stock()
    {
        $user = User::factory()->create();

        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-stock-test',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 1,
            'sku' => 'SKU-STOCK-1',
            'color' => 'Blue',
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'full_name' => 'John Doe',
            'phone' => '123456789',
            'address' => '123 Main',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
        ]);

        $response->assertStatus(422);

        $variant->refresh();
        $this->assertEquals(1, $variant->stock);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_uses_pessimistic_locking_on_variant()
    {
        $user = User::factory()->create();

        $category = Category::create(['name' => 'Test', 'slug' => 'test-lock']);

        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-lock-test',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 5,
            'sku' => 'SKU-LOCK-1',
            'color' => 'Blue',
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $capturedQueries = [];
        DB::listen(function ($query) use (&$capturedQueries) {
            $capturedQueries[] = strtolower($query->sql);
        });

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'full_name' => 'John Doe',
            'phone' => '123456789',
            'address' => '123 Main',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();

        $hasLockingQuery = collect($capturedQueries)
            ->contains(fn ($sql) => str_contains($sql, 'for update'));

        $this->assertTrue(
            $hasLockingQuery,
            "Aucune requête verrouillée (FOR UPDATE) n'a été détectée pendant le checkout."
        );
    }
}
