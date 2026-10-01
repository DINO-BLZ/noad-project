<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Drop;
use App\Models\DropWhitelist;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutSnapshotsTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_items_contain_variant_snapshots_after_checkout()
    {
        $user = User::factory()->create();

        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-test',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
            'sku' => 'SKU-123',
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

        $this->assertDatabaseHas('order_items', [
            'variant_sku' => 'SKU-123',
            'variant_size' => 'M',
            'variant_color' => 'Blue',
            'product_name' => 'T-shirt',
        ]);
    }

    public function test_checkout_snapshots_the_active_drop_on_order_items()
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Drop Category', 'slug' => 'drop-category']);
        $drop = Drop::create([
            'name' => 'Historical Drop',
            'slug' => 'historical-drop',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $product = Product::create([
            'name' => 'Drop T-shirt',
            'slug' => 'drop-tshirt',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
            'sku' => 'DROP-SKU-123',
            'color' => 'Blue',
        ]);

        $drop->products()->attach($product->id);
        DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);
        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->post(route('checkout.store'), [
            'full_name' => 'John Doe',
            'phone' => '123456789',
            'address' => '123 Main',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
        ])->assertRedirect();

        $this->assertDatabaseHas('order_items', [
            'drop_id' => $drop->id,
            'variant_sku' => 'DROP-SKU-123',
        ]);
    }
}
