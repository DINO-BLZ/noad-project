<?php

namespace Tests\Unit\Models;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Drop;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Variant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropQuotaMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_calculates_quota_and_sold_product_by_product(): void
    {
        $drop = $this->createDrop();

        $productA = $this->createProduct('Product A');
        $productB = $this->createProduct('Product B');

        $variantA = $this->createVariant($productA, 20);
        $variantB = $this->createVariant($productB, 20);

        $drop->products()->attach($productA->id, [
            'quota' => 10,
        ]);

        $drop->products()->attach($productB->id, [
            'quota' => 10,
        ]);

        $this->createOrderItem(
            $variantA,
            OrderStatus::Paid,
            12
        );

        $this->createOrderItem(
            $variantB,
            OrderStatus::Paid,
            2
        );

        $result = $drop->quotaMonitoring();

        $this->assertTrue($result['quota_defined']);
        $this->assertSame(20, $result['quota']);
        $this->assertSame(12, $result['sold']);
        $this->assertSame(8, $result['remaining']);
        $this->assertSame(60, $result['percentage']);
    }

    public function test_pending_orders_are_not_counted_as_sold(): void
    {
        $drop = $this->createDrop();

        $product = $this->createProduct('Product A');
        $variant = $this->createVariant($product, 20);

        $drop->products()->attach($product->id, [
            'quota' => 10,
        ]);

        $this->createOrderItem(
            $variant,
            OrderStatus::Paid,
            4
        );

        $this->createOrderItem(
            $variant,
            OrderStatus::Pending,
            2
        );

        $result = $drop->quotaMonitoring();

        $this->assertSame(10, $result['quota']);
        $this->assertSame(4, $result['sold']);
        $this->assertSame(6, $result['remaining']);
        $this->assertSame(40, $result['percentage']);
    }

    public function test_sales_are_capped_per_product(): void
    {
        $drop = $this->createDrop();

        $productA = $this->createProduct('Product A');
        $productB = $this->createProduct('Product B');

        $variantA = $this->createVariant($productA, 20);
        $variantB = $this->createVariant($productB, 20);

        $drop->products()->attach($productA->id, [
            'quota' => 10,
        ]);

        $drop->products()->attach($productB->id, [
            'quota' => 10,
        ]);

        $this->createOrderItem(
            $variantA,
            OrderStatus::Paid,
            12
        );

        $this->createOrderItem(
            $variantB,
            OrderStatus::Paid,
            2
        );

        $result = $drop->quotaMonitoring();

        /*
         * A = min(12, 10) = 10
         * B = min(2, 10) = 2
         *
         * Total = 12 / 20 = 60 %
         */
        $this->assertSame(20, $result['quota']);
        $this->assertSame(12, $result['sold']);
        $this->assertSame(8, $result['remaining']);
        $this->assertSame(60, $result['percentage']);
    }

    public function test_product_without_quota_is_ignored(): void
    {
        $drop = $this->createDrop();

        $productWithQuota = $this->createProduct('Product A');
        $productWithoutQuota = $this->createProduct('Product C');

        $variantA = $this->createVariant($productWithQuota, 20);
        $variantC = $this->createVariant($productWithoutQuota, 20);

        $drop->products()->attach($productWithQuota->id, [
            'quota' => 10,
        ]);

        $drop->products()->attach($productWithoutQuota->id, [
            'quota' => 0,
        ]);

        $this->createOrderItem(
            $variantA,
            OrderStatus::Paid,
            12
        );

        $this->createOrderItem(
            $variantC,
            OrderStatus::Paid,
            5
        );

        $result = $drop->quotaMonitoring();

        /*
         * Product C n'a aucun quota :
         * ses 5 ventes sont donc exclues.
         *
         * Product A = min(12, 10) = 10.
         */
        $this->assertTrue($result['quota_defined']);
        $this->assertSame(10, $result['quota']);
        $this->assertSame(10, $result['sold']);
        $this->assertSame(0, $result['remaining']);
        $this->assertSame(100, $result['percentage']);
    }

    public function test_zero_quota_returns_neutral_state(): void
    {
        $drop = $this->createDrop();

        $product = $this->createProduct('No Quota Product');
        $variant = $this->createVariant($product, 20);

        $drop->products()->attach($product->id, [
            'quota' => 0,
        ]);

        $this->createOrderItem(
            $variant,
            OrderStatus::Paid,
            5
        );

        $result = $drop->quotaMonitoring();

        $this->assertFalse($result['quota_defined']);
        $this->assertSame(0, $result['quota']);
        $this->assertSame(0, $result['sold']);
        $this->assertSame(0, $result['remaining']);
        $this->assertSame(0, $result['percentage']);
    }

    public function test_fully_sold_drop_returns_exactly_one_hundred_percent(): void
    {
        $drop = $this->createDrop();

        $product = $this->createProduct('Product A');
        $variant = $this->createVariant($product, 20);

        $drop->products()->attach($product->id, [
            'quota' => 10,
        ]);

        $this->createOrderItem(
            $variant,
            OrderStatus::Paid,
            10
        );

        $result = $drop->quotaMonitoring();

        $this->assertSame(10, $result['quota']);
        $this->assertSame(10, $result['sold']);
        $this->assertSame(0, $result['remaining']);
        $this->assertSame(100, $result['percentage']);
    }

    private function createDrop(): Drop
    {
        return Drop::create([
            'name' => 'Test Drop '.uniqid(),
            'slug' => 'test-drop-'.uniqid(),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
        ]);
    }

    private function createProduct(string $name): Product
    {
        $unique = uniqid();

        $category = Category::create([
            'name' => 'Test Category '.$unique,
            'slug' => 'test-category-'.$unique,
        ]);

        return Product::create([
            'name' => $name,
            'slug' => 'product-'.$unique,
            'price' => 25,
            'category_id' => $category->id,
        ]);
    }

    private function createVariant(
        Product $product,
        int $stock
    ): Variant {
        return Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => $stock,
            'sku' => 'SKU-'.uniqid(),
            'color' => 'Black',
        ]);
    }

    private function createOrderItem(
        Variant $variant,
        OrderStatus $status,
        int $quantity
    ): OrderItem {
        $user = User::factory()->create();

        $order = Order::create([
            'user_id' => $user->id,
            'full_name' => 'Test Customer',
            'phone' => '123456789',
            'address' => 'Test Address',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => $status,
            'total' => $quantity * 25,
        ]);

        return OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
            'price' => 25,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'product_name' => $variant->product->name,
        ]);
    }
}