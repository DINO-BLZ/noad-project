<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
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

        $response->assertRedirectBackWithErrors('checkout');

        $variant->refresh();
        $this->assertEquals(1, $variant->stock);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_cart_update_accepts_valid_quantity()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'cart-valid']);
        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-valid',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
            'sku' => 'SKU-VALID',
            'color' => 'Blue',
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->patch(route('cart.update', $variant->id), [
            'quantity' => 3,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);
    }

    public function test_cart_update_rejects_quantity_above_stock()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'cart-stock']);
        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-stock',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 5,
            'sku' => 'SKU-STOCK',
            'color' => 'Blue',
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->patch(route('cart.update', $variant->id), [
            'quantity' => 6,
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);
    }

    public function test_cart_update_allows_quantity_equal_to_stock()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'cart-equal']);
        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-equal',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 4,
            'sku' => 'SKU-EQUAL',
            'color' => 'Blue',
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->patch(route('cart.update', $variant->id), [
            'quantity' => 4,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'quantity' => 4,
        ]);
    }

    public function test_cart_update_rejects_unknown_variant()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch('/panier/999999', [
            'quantity' => 2,
        ]);

        $response->assertStatus(404);
    }

    public function test_cart_update_rejects_other_users_cart()
    {
        $owner = User::factory()->create();
        $currentUser = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'cart-other-user']);
        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-other-user',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
            'sku' => 'SKU-FOREIGN',
            'color' => 'Blue',
        ]);

        CartItem::create([
            'user_id' => $owner->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($currentUser)->patch(route('cart.update', $variant->id), [
            'quantity' => 3,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $owner->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
    }

    public function test_checkout_with_valid_cart_creates_order_and_order_items_and_clears_cart()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'checkout-valid']);
        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-checkout-valid',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
            'sku' => 'SKU-CHECKOUT-VALID',
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

        $response->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'full_name' => 'John Doe',
            'payment_method' => 'cod',
            'status' => 'pending',
        ]);

        $order = Order::first();
        $this->assertNotNull($order);
        $this->assertEquals(50.00, (float) $order->total);
        $this->assertCount(1, $order->items);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 25.00,
            'variant_sku' => 'SKU-CHECKOUT-VALID',
            'variant_size' => 'M',
            'variant_color' => 'Blue',
            'product_name' => 'T-shirt',
        ]);
        $this->assertDatabaseMissing('cart_items', [
            'user_id' => $user->id,
            'variant_id' => $variant->id,
        ]);
        $variant->refresh();
        $this->assertEquals(8, $variant->stock);
    }

    public function test_checkout_with_empty_cart_redirects_without_creating_order()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'full_name' => 'John Doe',
            'phone' => '123456789',
            'address' => '123 Main',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect(route('cart.index'));
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_keeps_cart_when_stock_is_insufficient_and_rollback_occurs()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'checkout-rollback']);
        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-checkout-rollback',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 1,
            'sku' => 'SKU-CHECKOUT-ROLLBACK',
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

        $response->assertSessionHasErrors('checkout');
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $user->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
        ]);
        $variant->refresh();
        $this->assertEquals(1, $variant->stock);
    }

    public function test_checkout_rejects_when_quantity_equals_stock()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Test', 'slug' => 'checkout-equal']);
        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-checkout-equal',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 3,
            'sku' => 'SKU-CHECKOUT-EQUAL',
            'color' => 'Blue',
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 3,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'full_name' => 'John Doe',
            'phone' => '123456789',
            'address' => '123 Main',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();
        $variant->refresh();
        $this->assertEquals(0, $variant->stock);
        $this->assertDatabaseHas('orders', ['user_id' => $user->id]);
    }
}
