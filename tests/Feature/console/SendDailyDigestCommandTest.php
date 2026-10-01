<?php

namespace Tests\Feature\Console;

use App\Actions\Orders\OrderStatusTransitionService;
use App\Enums\OrderStatus;
use App\Mail\DailyDigestMail;
use App\Models\Category;
use App\Models\Drop;
use App\Models\DropWhitelist;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendDailyDigestCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_digest_contains_sales_orders_whitelist_and_stock_data(): void
    {
        Mail::fake();
        config(['mail.digest_recipients' => 'test@example.com']);

        $now = now();
        $yesterday = $now->copy()->subDay();
        $dayBeforeYesterday = $now->copy()->subDays(2);

        $paidOrder = $this->createOrder(OrderStatus::Delivered, [
            'payment_status' => 'paid',
            'delivered_at' => $yesterday,
            'created_at' => $dayBeforeYesterday,
            'updated_at' => $yesterday,
            'total' => 3000,
        ]);

        OrderItem::create([
            'order_id' => $paidOrder->id,
            'variant_id' => null,
            'quantity' => 2,
            'price' => 1500,
            'variant_sku' => 'SKU-DIGEST',
            'variant_size' => 'M',
            'variant_color' => 'Black',
            'product_name' => 'Digest Test Product',
            'created_at' => $dayBeforeYesterday,
            'updated_at' => $dayBeforeYesterday,
        ]);

        $this->createOrder(OrderStatus::Pending, [
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
            'total' => 2000,
        ]);

        $this->createOrder(OrderStatus::Cancelled, [
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
            'total' => 2500,
        ]);

        $todayPaidOrder = $this->createOrder(OrderStatus::Delivered, [
            'payment_status' => 'paid',
            'delivered_at' => $now,
            'created_at' => $yesterday,
            'updated_at' => $now,
            'total' => 9999,
        ]);

        OrderItem::create([
            'order_id' => $todayPaidOrder->id,
            'variant_id' => null,
            'quantity' => 1,
            'price' => 9999,
            'variant_sku' => 'SKU-TODAY',
            'variant_size' => 'L',
            'variant_color' => 'White',
            'product_name' => 'Today Test Product',
            'created_at' => $yesterday,
            'updated_at' => $now,
        ]);

        $user = User::factory()->create();
        $drop = Drop::create([
            'name' => 'Digest Test Drop',
            'slug' => 'digest-test-drop-'.uniqid(),
            'start_date' => $yesterday,
            'end_date' => $now->copy()->addDays(3),
            'status' => 'active',
        ]);

        DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'pending',
            'created_at' => $yesterday,
            'updated_at' => $yesterday,
        ]);

        $criticalVariantZero = $this->createVariant(0, 'Digest Zero Stock');
        $criticalVariantThree = $this->createVariant(3, 'Digest Three Stock');

        $exitCode = Artisan::call('orders:send-daily-digest');

        $this->assertSame(0, $exitCode);

        Mail::assertQueued(DailyDigestMail::class, function (DailyDigestMail $mail) use (
            $criticalVariantZero,
            $criticalVariantThree,
        ): bool {
            $stockSkus = array_column($mail->stock['variants'], 'sku');

            return $mail->sales['confirmed_orders'] === 1
                && $mail->sales['revenue'] === 3000.0
                && $mail->sales['items_sold'] === 2
                && $mail->sales['top_products'] === [
                    [
                        'name' => 'Digest Test Product',
                        'quantity' => 2,
                    ],
                ]
                && $mail->orders['pending'] === 1
                && $mail->orders['new'] === 3
                && $mail->orders['cancelled'] === 1
                && $mail->whitelist['new'] === 1
                && $mail->stock['critical_count'] === 2
                && in_array($criticalVariantZero->sku, $stockSkus, true)
                && in_array($criticalVariantThree->sku, $stockSkus, true);
        });
    }

    public function test_sales_are_counted_once_on_delivery_day_not_on_later_order_updates(): void
    {
        Mail::fake();
        config(['mail.digest_recipients' => 'test@example.com']);

        $reportDay = now()->startOfDay()->subDay();
        $olderDay = $reportDay->copy()->subDays(2);
        $this->travelTo($olderDay->copy()->setTime(10, 0));

        $variant = $this->createVariant(20, 'Delivered Product');
        $lifecycleOrder = $this->createOrder(OrderStatus::Pending, [
            'created_at' => $olderDay,
            'updated_at' => $olderDay,
            'total' => 200,
        ]);
        $this->createItem($lifecycleOrder, $variant, 2);

        $transitionService = app(OrderStatusTransitionService::class);
        $this->travelTo($olderDay->copy()->addDay()->setTime(11, 0));
        $transitionService->transition($lifecycleOrder, OrderStatus::Paid);

        $this->travelTo($reportDay->copy()->setTime(9, 0));
        $transitionService->transition($lifecycleOrder, OrderStatus::Shipped);
        $transitionService->transition($lifecycleOrder, OrderStatus::Delivered);

        $sameDayOrder = $this->createOrder(OrderStatus::Shipped, [
            'created_at' => now(),
            'updated_at' => now(),
            'total' => 300,
        ]);
        $this->createItem($sameDayOrder, $variant, 3);
        $transitionService->transition($sameDayOrder, OrderStatus::Delivered);

        $pendingOrder = $this->createOrder(OrderStatus::Pending, [
            'created_at' => now(),
            'updated_at' => now(),
            'total' => 900,
        ]);

        $paidNotDelivered = $this->createOrder(OrderStatus::Paid, [
            'created_at' => $olderDay,
            'updated_at' => $olderDay,
            'total' => 400,
        ]);
        $this->createItem($paidNotDelivered, $variant, 4);
        $this->travelTo($reportDay->copy()->setTime(14, 0));
        $transitionService->transition($paidNotDelivered, OrderStatus::Shipped);

        $oldDeliveredOrder = $this->createOrder(OrderStatus::Delivered, [
            'payment_status' => 'paid',
            'delivered_at' => $reportDay->copy()->subDay(),
            'created_at' => $olderDay,
            'updated_at' => now(),
            'total' => 900,
        ]);
        $this->createItem($oldDeliveredOrder, $variant, 9);

        $this->travelTo($reportDay->copy()->addDay()->setTime(8, 0));
        Artisan::call('orders:send-daily-digest');

        Mail::assertQueued(DailyDigestMail::class, function (DailyDigestMail $mail) use ($pendingOrder): bool {
            return $mail->sales['confirmed_orders'] === 2
                && $mail->sales['revenue'] === 500.0
                && $mail->sales['items_sold'] === 5
                && $mail->sales['top_products'] === [[
                    'name' => 'Delivered Product',
                    'quantity' => 5,
                ]]
                && $mail->orders['new'] === 2
                && $mail->orders['pending'] === 1
                && $pendingOrder->fresh()->status === OrderStatus::Pending;
        });

        $this->travelBack();
    }

    private function createOrder(OrderStatus $status, array $overrides = []): Order
    {
        $user = User::factory()->create();

        return Order::create(array_merge([
            'user_id' => $user->id,
            'full_name' => 'Digest Test User',
            'phone' => '0555000000',
            'address' => 'Digest Test Address',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => $status,
            'total' => 1000,
        ], $overrides));
    }

    private function createVariant(int $stock, string $productName): Variant
    {
        $suffix = uniqid();

        $category = Category::create([
            'name' => 'Digest Test Category '.$suffix,
            'slug' => 'digest-test-category-'.$suffix,
        ]);

        $product = Product::create([
            'name' => $productName,
            'slug' => 'digest-test-product-'.$suffix,
            'price' => 100,
            'category_id' => $category->id,
        ]);

        return Variant::create([
            'product_id' => $product->id,
            'sku' => 'DIGEST-'.$stock.'-'.$suffix,
            'size' => 'M',
            'color' => 'Black',
            'stock' => $stock,
        ]);
    }

    private function createItem(Order $order, Variant $variant, int $quantity): OrderItem
    {
        return OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
            'price' => $variant->product->price,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'product_name' => $variant->product->name,
        ]);
    }
}
