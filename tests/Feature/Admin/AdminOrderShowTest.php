<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_order_show_page_renders_for_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $this->createOrderWithItem(OrderStatus::Pending);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk();
    }

    public function test_paid_order_show_page_renders_for_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $this->createOrderWithItem(OrderStatus::Paid);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk();
    }

    public function test_delivered_order_show_page_renders_for_admin(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $this->createOrderWithItem(OrderStatus::Delivered);

        $this->actingAs($admin)
            ->get(route('admin.orders.show', $order))
            ->assertOk();
    }

    private function createOrderWithItem(OrderStatus $status): Order
    {
        $variant = $this->createVariant();
        $order = $this->createOrder($status);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 25,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'product_name' => $variant->product->name,
        ]);

        return $order;
    }

    private function createOrder(OrderStatus $status): Order
    {
        $user = User::factory()->create();

        return Order::create([
            'user_id' => $user->id,
            'full_name' => 'John Doe',
            'phone' => '123456789',
            'address' => '123 Main',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => $status,
            'total' => 50,
        ]);
    }

    private function createVariant(): Variant
    {
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category-'.uniqid()]);
        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-'.uniqid(),
            'price' => 25,
            'category_id' => $category->id,
        ]);

        return Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 5,
            'sku' => 'SKU-'.uniqid(),
            'color' => 'Blue',
        ]);
    }
}
