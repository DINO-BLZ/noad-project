<?php

namespace Tests\Feature;

use App\Actions\Orders\CancelOrderAction;
use App\Actions\Products\UpdateProductAction;
use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductConcurrencyAuditTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_product_preserves_deterministic_variant_locks(): void
    {
        $category = Category::create(['name' => 'Tops', 'slug' => 'tops']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Original Top',
            'slug' => 'original-top',
            'price' => 3000,
        ]);

        $v1 = $product->variants()->create(['size' => 'S', 'stock' => 10, 'color' => '']);
        $v2 = $product->variants()->create(['size' => 'M', 'stock' => 20, 'color' => '']);
        $v3 = $product->variants()->create(['size' => 'L', 'stock' => 30, 'color' => '']);

        $action = app(UpdateProductAction::class);

        // Update sizes in arbitrary order
        $updated = $action->execute($product, [
            'name' => 'Updated Top',
            'price' => 3500,
            'category_id' => $category->id,
            'sizes' => [
                'L' => ['stock' => 35],
                'S' => ['stock' => 15],
                'M' => ['stock' => 25],
            ],
        ]);

        $this->assertSame('Updated Top', $updated->name);
        $this->assertSame(15, (int) $v1->fresh()->stock);
        $this->assertSame(25, (int) $v2->fresh()->stock);
        $this->assertSame(35, (int) $v3->fresh()->stock);
    }

    public function test_variant_cannot_be_removed_if_associated_with_active_order(): void
    {
        $category = Category::create(['name' => 'Tops', 'slug' => 'tops']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Top With Active Order',
            'slug' => 'top-active-order',
            'price' => 2500,
        ]);

        $variant = $product->variants()->create(['size' => 'M', 'stock' => 5, 'color' => '']);

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => 2500,
            'full_name' => 'Client Test',
            'phone' => '0555000000',
            'wilaya' => '16 - Alger',
            'commune' => 'Alger',
            'address' => 'Rue Test',
            'delivery_type' => 'home',
            'payment_method' => 'cod',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'variant_size' => $variant->size,
            'quantity' => 1,
            'price' => 2500,
        ]);

        $action = app(UpdateProductAction::class);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Impossible de supprimer une variante associée à une commande active');

        $action->execute($product, [
            'name' => 'Top With Active Order',
            'price' => 2500,
            'category_id' => $category->id,
            'remove_variants' => [$variant->id],
        ]);
    }

    public function test_cancelling_order_restores_variant_stock(): void
    {
        $category = Category::create(['name' => 'Pants', 'slug' => 'pants']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Jeans',
            'slug' => 'jeans',
            'price' => 5000,
        ]);

        $variant = $product->variants()->create(['size' => '32', 'stock' => 8, 'color' => '']);

        $user = User::factory()->create();
        $order = Order::create([
            'user_id' => $user->id,
            'status' => OrderStatus::Pending,
            'total' => 10000,
            'full_name' => 'Client Test',
            'phone' => '0555000000',
            'wilaya' => '16 - Alger',
            'commune' => 'Alger',
            'address' => 'Rue Test',
            'delivery_type' => 'home',
            'payment_method' => 'cod',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'variant_size' => $variant->size,
            'quantity' => 2,
            'price' => 5000,
        ]);

        // Cancel order using CancelOrderAction
        $cancelAction = app(CancelOrderAction::class);
        $cancelledOrder = $cancelAction->execute($order);

        $this->assertSame(OrderStatus::Cancelled, $cancelledOrder->status);
        $this->assertSame(10, (int) $variant->fresh()->stock);
    }
}
