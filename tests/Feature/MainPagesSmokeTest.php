<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Drop;
use App\Models\DropWhitelist;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class MainPagesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_real_catalog_and_account_data(): void
    {
        config(['scout.driver' => 'collection']);
        $data = $this->createFixture();

        $this->assertPageContains($this->get(route('home')), 'Smoke Product', 'home');
        $this->assertPageContains($this->get(route('shop.index')), 'Smoke Product', 'shop');
        $this->assertPageContains(
            $this->get(route('products.show', $data['product'])),
            'Smoke Product',
            'product detail'
        );
        $this->assertPageContains(
            $this->get(route('search.index', ['q' => 'Smoke Product'])),
            'Smoke Product',
            'search'
        );

        $this->actingAs($data['user']);
        $this->assertPageContains($this->get(route('cart.index')), 'Smoke Product', 'cart');
        $this->assertPageContains($this->get(route('checkout.index')), 'Smoke Product', 'checkout');
        $this->assertPageContains(
            $this->get(route('checkout.success', $data['order'])),
            'Smoke Product',
            'order confirmation'
        );
        $whitelistPage = $this->get(route('whitelist.index'));
        $this->assertPageContains($whitelistPage, 'Smoke Drop', 'customer whitelist');
        $this->assertFalse(str_contains($whitelistPage->getContent(), 'WL-01-8842-ALG'));
        $this->assertPageContains($this->get(route('drops.index')), 'Smoke Drop', 'drops list');
        $this->assertPageContains(
            $this->get(route('drops.show', $data['drop'])),
            'Smoke Drop',
            'drop detail'
        );

        auth()->logout();
        $this->assertPageContains($this->get(route('login')), 'Connexion', 'login');
        $this->assertPageContains($this->get(route('register')), 'INSCRIPTION', 'registration');
    }

    public function test_admin_pages_render_real_products_categories_drops_orders_and_whitelist(): void
    {
        $data = $this->createFixture();
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin);

        $this->assertPageContains($this->get(route('admin.dashboard')), 'Smoke Product', 'admin dashboard');
        $this->assertPageContains($this->get(route('admin.products.index')), 'Smoke Product', 'admin products');
        $this->assertPageContains(
            $this->get(route('admin.products.edit', $data['product'])),
            'Smoke Product',
            'admin product detail'
        );
        $this->assertPageContains(
            $this->get(route('admin.categories.index')),
            'Smoke Category',
            'admin categories'
        );
        $this->assertPageContains($this->get(route('admin.drops.index')), 'Smoke Drop', 'admin drops');
        $this->assertPageContains(
            $this->get(route('admin.drops.edit', $data['drop'])),
            'Smoke Drop',
            'admin drop detail'
        );
        $this->assertPageContains(
            $this->get(route('admin.orders.index')),
            'Smoke Customer',
            'admin orders'
        );
        $this->assertPageContains(
            $this->get(route('admin.orders.show', $data['order'])),
            'Smoke Product',
            'admin order detail'
        );
        $this->assertPageContains(
            $this->get(route('admin.whitelist.index')),
            $data['user']->email,
            'admin whitelist'
        );
    }

    public function test_admin_product_catalog_is_paginated_and_filtered(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name' => 'Paged Category',
            'slug' => 'paged-category',
        ]);

        foreach (range(1, 21) as $number) {
            Product::create([
                'name' => 'Paged Product '.$number,
                'slug' => 'paged-product-'.$number,
                'price' => 1000,
                'category_id' => $category->id,
            ]);
        }

        $response = $this->actingAs($admin)->get(route('admin.products.index', [
            'category_id' => $category->id,
            'status' => 'out_of_stock',
        ]));

        $response->assertOk()->assertViewHas('products', function ($products): bool {
            return $products->count() === 20 && $products->total() === 21;
        });
        $response->assertViewHas('activeReferences', 21);
        $response->assertSee('Paged Product 1');
        $response->assertDontSee('Paged Product 21');
    }

    private function assertPageContains(TestResponse $response, string $expected, string $page): void
    {
        $this->assertSame(200, $response->status(), $page.' should return HTTP 200.');
        $this->assertTrue(
            str_contains(strtolower($response->getContent()), strtolower($expected)),
            $page.' should render fixture data: '.$expected
        );
    }

    private function createFixture(): array
    {
        $user = User::factory()->create([
            'name' => 'Smoke Customer',
            'email' => 'smoke-customer@example.test',
        ]);
        $category = Category::create([
            'name' => 'Smoke Category',
            'slug' => 'smoke-category',
        ]);
        $drop = Drop::create([
            'name' => 'Smoke Drop',
            'slug' => 'smoke-drop',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
        ]);
        $product = Product::create([
            'name' => 'Smoke Product',
            'slug' => 'smoke-product',
            'price' => 2800,
            'category_id' => $category->id,
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 8,
            'sku' => 'SMOKE-SKU',
            'color' => 'Black',
        ]);

        $drop->products()->attach($product->id, ['quota' => 8]);
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
        $order = Order::create([
            'user_id' => $user->id,
            'full_name' => 'Smoke Customer',
            'phone' => '123456789',
            'address' => '1 Test Street',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => OrderStatus::Paid,
            'total' => 2800,
        ]);
        OrderItem::create([
            'order_id' => $order->id,
            'drop_id' => $drop->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 2800,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'product_name' => $product->name,
        ]);

        return compact('user', 'category', 'drop', 'product', 'variant', 'order');
    }
}
