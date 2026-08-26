<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
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
}