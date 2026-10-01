<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardLowStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_zero_and_low_stock_variants_only(): void
    {
        $this->actingAs($this->createAdmin());

        $outOfStock = $this->createVariant(
            stock: 0,
            productName: 'Alpha Jacket',
            color: 'Black',
            size: 'M'
        );

        $critical = $this->createVariant(
            stock: 2,
            productName: 'Beta Hoodie',
            color: 'White',
            size: 'L'
        );

        $normal = $this->createVariant(
            stock: 10,
            productName: 'Gamma T-Shirt',
            color: 'Blue',
            size: 'S'
        );

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();

        $response->assertViewHas('lowStockVariants');
        $response->assertViewHas('criticalStockAlertsCount', 2);

        $lowStockVariants = $response->viewData('lowStockVariants');

        $this->assertCount(2, $lowStockVariants);

        $this->assertSame(
            $outOfStock->id,
            $lowStockVariants[0]['variant']->id
        );

        $this->assertSame(
            $critical->id,
            $lowStockVariants[1]['variant']->id
        );

        $this->assertFalse(
            collect($lowStockVariants)
                ->contains(
                    fn (array $alert) => $alert['variant']->id === $normal->id
                )
        );

        $this->assertSame('ÉPUISÉE', $lowStockVariants[0]['status_label']);
        $this->assertSame('exhausted', $lowStockVariants[0]['status_class']);
        $this->assertSame('ÉPUISÉE', $lowStockVariants[0]['label']);

        $this->assertSame('STOCK CRITIQUE', $lowStockVariants[1]['status_label']);
        $this->assertSame('critical', $lowStockVariants[1]['status_class']);
        $this->assertSame('2 RESTANTS', $lowStockVariants[1]['label']);
    }

    public function test_dashboard_uses_one_restant_for_stock_of_one(): void
    {
        $this->actingAs($this->createAdmin());

        $variant = $this->createVariant(
            stock: 1,
            productName: 'Single Stock Product',
            color: 'Black',
            size: 'M'
        );

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewHas('criticalStockAlertsCount', 1);

        $lowStockVariants = $response->viewData('lowStockVariants');

        $this->assertCount(1, $lowStockVariants);

        $this->assertSame(
            $variant->id,
            $lowStockVariants[0]['variant']->id
        );

        $this->assertSame('1 RESTANT', $lowStockVariants[0]['label']);
        $this->assertSame('STOCK CRITIQUE', $lowStockVariants[0]['status_label']);
        $this->assertSame('critical', $lowStockVariants[0]['status_class']);
    }

    public function test_dashboard_alert_count_includes_alerts_beyond_display_limit(): void
    {
        $this->actingAs($this->createAdmin());

        for ($stock = 0; $stock < 5; $stock++) {
            $this->createVariant(
                stock: $stock,
                productName: 'Alert Product '.$stock,
                color: 'Black',
                size: 'M'
            );
        }

        $this->createVariant(
            stock: 10,
            productName: 'Normal Product',
            color: 'White',
            size: 'L'
        );

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk();

        /*
         * 5 variants are in alert:
         * 0, 1, 2, 3 and 4.
         *
         * The dashboard displays only 3 rows,
         * but the badge must show the real total: 5.
         */
        $response->assertViewHas('criticalStockAlertsCount', 5);

        $lowStockVariants = $response->viewData('lowStockVariants');

        $this->assertCount(3, $lowStockVariants);

        $this->assertSame(0, $lowStockVariants[0]['stock']);
        $this->assertSame(1, $lowStockVariants[1]['stock']);
        $this->assertSame(2, $lowStockVariants[2]['stock']);
    }

    public function test_dashboard_revenue_only_counts_cod_payments_received(): void
    {
        $this->actingAs($this->createAdmin());

        $unpaidOrder = $this->createRevenueOrder(OrderStatus::Shipped, PaymentStatus::Pending, 9000);
        $paidOrder = $this->createRevenueOrder(OrderStatus::Delivered, PaymentStatus::Paid, 2500);

        OrderItem::create([
            'order_id' => $unpaidOrder->id,
            'quantity' => 1,
            'price' => 9000,
            'product_name' => 'Uncollected COD',
        ]);
        OrderItem::create([
            'order_id' => $paidOrder->id,
            'quantity' => 1,
            'price' => 2500,
            'product_name' => 'Collected COD',
        ]);

        $response = $this->get(route('admin.dashboard'));

        $response->assertOk()->assertViewHas('totalRevenue', 2500.0);
        $response->assertViewHas('salesByDay');
        $this->assertEquals(2500.0, (float) $response->viewData('salesByDay')->sum('total'));
    }

    private function createRevenueOrder(
        OrderStatus $status,
        PaymentStatus $paymentStatus,
        float $total
    ): Order {
        return Order::create([
            'user_id' => $this->createAdmin()->id,
            'full_name' => 'Dashboard Customer',
            'phone' => '0555000000',
            'address' => 'Dashboard Address',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => $status,
            'payment_status' => $paymentStatus,
            'delivered_at' => $paymentStatus === PaymentStatus::Paid ? now() : null,
            'total' => $total,
        ]);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
        ]);
    }

    private function createVariant(
        int $stock,
        string $productName,
        string $color,
        string $size
    ): Variant {
        $unique = uniqid();

        $category = Category::create([
            'name' => 'Test Category '.$unique,
            'slug' => 'test-category-'.$unique,
        ]);

        $product = Product::create([
            'name' => $productName,
            'slug' => 'product-'.$unique,
            'price' => 25,
            'category_id' => $category->id,
        ]);

        return Variant::create([
            'product_id' => $product->id,
            'size' => $size,
            'stock' => $stock,
            'sku' => 'SKU-'.$unique,
            'color' => $color,
        ]);
    }
}
