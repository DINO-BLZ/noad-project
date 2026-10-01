<?php

namespace Tests\Feature\Console;

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

        $paidOrder = $this->createOrder(OrderStatus::Paid, [
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

        $todayPaidOrder = $this->createOrder(OrderStatus::Paid, [
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
                && $mail->orders['cancelled'] === 1
                && $mail->whitelist['new'] === 1
                && $mail->stock['critical_count'] === 2
                && in_array($criticalVariantZero->sku, $stockSkus, true)
                && in_array($criticalVariantThree->sku, $stockSkus, true);
        });
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
}
