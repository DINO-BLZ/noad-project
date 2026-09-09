<?php

namespace Tests\Feature\Admin;

use App\Actions\Orders\OrderStatusTransitionService;
use App\Enums\OrderStatus;
use App\Mail\OrderStatusUpdatedMail;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use LogicException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class OrderStatusLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_transitions_are_allowed(): void
    {
        $service = app(OrderStatusTransitionService::class);

        foreach ([
            [OrderStatus::Pending, OrderStatus::Paid],
            [OrderStatus::Pending, OrderStatus::Cancelled],
            [OrderStatus::Paid, OrderStatus::Shipped],
            [OrderStatus::Paid, OrderStatus::Cancelled],
            [OrderStatus::Shipped, OrderStatus::Delivered],
        ] as [$from, $to]) {
            $order = $this->createOrder($from);

            if ($to === OrderStatus::Cancelled) {
                continue;
            }

            $service->transition($order, $to);
            $this->assertSame($to, $order->fresh()->status);
        }

        $this->assertTrue(OrderStatus::Pending->canTransitionTo(OrderStatus::Cancelled));
        $this->assertTrue(OrderStatus::Paid->canTransitionTo(OrderStatus::Cancelled));
    }

    #[DataProvider('invalidTransitionProvider')]
    public function test_invalid_transitions_are_rejected(string $from, string $to): void
    {
        $order = $this->createOrder(OrderStatus::from($from));

        $this->expectException(LogicException::class);

        app(OrderStatusTransitionService::class)->transition($order, OrderStatus::from($to));
    }

    public static function invalidTransitionProvider(): array
    {
        return [
            'cancelled to paid' => ['cancelled', 'paid'],
            'cancelled to shipped' => ['cancelled', 'shipped'],
            'cancelled to pending' => ['cancelled', 'pending'],
            'cancelled to cancelled' => ['cancelled', 'cancelled'],
            'shipped to cancelled' => ['shipped', 'cancelled'],
            'delivered to cancelled' => ['delivered', 'cancelled'],
            'delivered to shipped' => ['delivered', 'shipped'],
            'delivered to pending' => ['delivered', 'pending'],
            'paid to pending' => ['paid', 'pending'],
            'shipped to pending' => ['shipped', 'pending'],
        ];
    }

    public function test_pending_cancellation_restores_stock_once(): void
    {
        $variant = $this->createVariant(5);
        $order = $this->createOrderWithItem(OrderStatus::Pending, $variant, 2);

        $this->cancelAsAdmin($order);

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(7, $variant->fresh()->stock);
    }

    public function test_paid_cancellation_restores_stock(): void
    {
        $variant = $this->createVariant(5);
        $order = $this->createOrderWithItem(OrderStatus::Paid, $variant, 2);

        $this->cancelAsAdmin($order);

        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(7, $variant->fresh()->stock);
    }

    public function test_shipped_and_delivered_cannot_be_cancelled(): void
    {
        foreach ([OrderStatus::Shipped, OrderStatus::Delivered] as $status) {
            $variant = $this->createVariant(5);
            $order = $this->createOrderWithItem($status, $variant, 2);

            $response = $this->cancelAsAdmin($order);

            $response->assertRedirectBackWithErrors('status');
            $this->assertSame($status, $order->fresh()->status);
            $this->assertSame(5, $variant->fresh()->stock);
        }
    }

    public function test_cancelled_order_cannot_be_cancelled_again(): void
    {
        $variant = $this->createVariant(5);
        $order = $this->createOrderWithItem(OrderStatus::Cancelled, $variant, 2);

        $response = $this->cancelAsAdmin($order);

        $response->assertRedirectBackWithErrors('status');
        $this->assertSame(OrderStatus::Cancelled, $order->fresh()->status);
        $this->assertSame(5, $variant->fresh()->stock);
    }

    public function test_non_admin_cannot_change_order_status(): void
    {
        $user = User::factory()->create(['is_admin' => false]);
        $order = $this->createOrder(OrderStatus::Pending);

        $response = $this->actingAs($user)->patch(route('admin.orders.updateStatus', $order), [
            'status' => OrderStatus::Paid->value,
        ]);

        $response->assertForbidden();
        $this->assertSame(OrderStatus::Pending, $order->fresh()->status);
    }

    public function test_admin_cannot_bypass_state_machine_with_direct_request(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $this->createOrder(OrderStatus::Shipped);

        $response = $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
            'status' => OrderStatus::Cancelled->value,
        ]);

        $response->assertRedirectBackWithErrors('status');
        $this->assertSame(OrderStatus::Shipped, $order->fresh()->status);
    }

    public function test_admin_can_change_order_status(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $this->createOrder(OrderStatus::Pending);

        $response = $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
            'status' => OrderStatus::Paid->value,
        ]);

        $response->assertRedirect();
        $this->assertSame(OrderStatus::Paid, $order->fresh()->status);
        Mail::assertQueued(OrderStatusUpdatedMail::class);
    }

    private function cancelAsAdmin(Order $order)
    {
        $admin = User::factory()->create(['is_admin' => true]);

        return $this->actingAs($admin)->patch(route('admin.orders.updateStatus', $order), [
            'status' => OrderStatus::Cancelled->value,
        ]);
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

    private function createVariant(int $stock): Variant
    {
        $category = Category::create(['name' => 'Test '.$stock, 'slug' => 'test-'.$stock.'-'.uniqid()]);
        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-'.uniqid(),
            'price' => 25,
            'category_id' => $category->id,
        ]);

        return Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => $stock,
            'sku' => 'SKU-'.uniqid(),
            'color' => 'Blue',
        ]);
    }

    private function createOrderWithItem(OrderStatus $status, Variant $variant, int $quantity): Order
    {
        $order = $this->createOrder($status);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
            'price' => 25,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'product_name' => $variant->product->name,
        ]);

        return $order;
    }
}