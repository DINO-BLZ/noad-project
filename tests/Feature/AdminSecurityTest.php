<?php

namespace Tests\Feature;

use App\Actions\Orders\CreateOrderAction;
use App\Http\Middleware\RedirectAdminFromShop;
use App\Models\Category;
use App\Models\Drop;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use App\Policies\DropPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_admin_routes(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.products.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.categories.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.drops.index'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.orders.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_non_admin_user_cannot_access_admin_routes(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($customer)->get(route('admin.dashboard'));
        $response->assertForbidden();

        $response = $this->actingAs($customer)->get(route('admin.products.index'));
        $response->assertForbidden();

        $response = $this->actingAs($customer)->get(route('admin.categories.index'));
        $response->assertForbidden();

        $response = $this->actingAs($customer)->get(route('admin.drops.index'));
        $response->assertForbidden();

        $response = $this->actingAs($customer)->get(route('admin.orders.index'));
        $response->assertForbidden();
    }

    public function test_non_admin_user_cannot_perform_admin_mutations(): void
    {
        $customer = User::factory()->create(['is_admin' => false]);

        $category = Category::create([
            'name' => 'Test Cat',
            'slug' => 'test-cat',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Prod',
            'slug' => 'test-prod',
            'price' => 1000,
            'description' => 'Desc',
        ]);

        $this->actingAs($customer)
            ->post(route('admin.categories.store'), ['name' => 'Hacked Cat', 'slug' => 'hacked-cat'])
            ->assertForbidden();

        $this->actingAs($customer)
            ->post(route('admin.products.store'), [
                'name' => 'Hacked Prod',
                'category_id' => $category->id,
                'price' => 500,
            ])
            ->assertForbidden();

        $this->actingAs($customer)
            ->delete(route('admin.products.destroy', $product))
            ->assertForbidden();
    }

    public function test_admin_is_redirected_away_from_shop_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get(route('shop.index'));
        $response->assertRedirect(route('admin.dashboard'));

        $response = $this->actingAs($admin)->get(route('cart.index'));
        $response->assertRedirect(route('admin.dashboard'));

        $response = $this->actingAs($admin)->get(route('checkout.index'));
        $response->assertRedirect(route('admin.dashboard'));

        $response = $this->actingAs($admin)->get(route('whitelist.index'));
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_cannot_add_products_to_cart(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Product 1',
            'slug' => 'product-1',
            'price' => 2000,
            'description' => 'Test',
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('cart.add', $product), [
                'variant_id' => $variant->id,
            ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_admin_cannot_update_or_remove_cart_items(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $category = Category::create(['name' => 'Cat', 'slug' => 'cat']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Product 1',
            'slug' => 'product-1',
            'price' => 2000,
            'description' => 'Test',
        ]);
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
        ]);

        $response = $this->actingAs($admin)
            ->patchJson(route('cart.update', $variant->id), [
                'quantity' => 2,
            ]);

        $response->assertForbidden();

        $response = $this->actingAs($admin)
            ->deleteJson(route('cart.remove', $variant->id));

        $response->assertForbidden();
    }

    public function test_admin_cannot_request_drop_whitelist(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $drop = Drop::create([
            'name' => 'Summer Drop',
            'slug' => 'summer-drop',
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(2),
        ]);

        // Browser request is redirected to admin dashboard
        $response = $this->actingAs($admin)
            ->post(route('drops.request-whitelist', $drop));
        $response->assertRedirect(route('admin.dashboard'));

        // JSON / API request is forbidden
        $responseJson = $this->actingAs($admin)
            ->postJson(route('drops.request-whitelist', $drop));
        $responseJson->assertForbidden();

        // Policy directly forbids admin
        $policy = new DropPolicy;
        $this->assertFalse($policy->request($admin, $drop));

        $this->assertDatabaseCount('drop_whitelists', 0);
    }

    public function test_admin_cannot_place_order(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $payload = [
            'full_name' => 'Admin User',
            'phone' => '0555123456',
            'wilaya' => '16 - Alger',
            'commune' => 'Alger Centre',
            'address' => '123 Rue de la Paix',
            'delivery_type' => 'home',
            'payment_method' => 'cod',
        ];

        // 1. In browser, shop middleware redirects admin
        $response = $this->actingAs($admin)
            ->post(route('checkout.store'), $payload);

        $response->assertRedirect(route('admin.dashboard'));

        // 2. Direct controller execution (bypassing shop redirect) rejects admin with session error
        $responseDirect = $this->actingAs($admin)
            ->withoutMiddleware(RedirectAdminFromShop::class)
            ->post(route('checkout.store'), $payload);

        $responseDirect->assertSessionHasErrors('checkout');

        // 3. Action execution directly aborts with 403
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('Les comptes administrateurs ne peuvent pas passer de commande.');

        app(CreateOrderAction::class)->execute([], $admin->id, null);
    }
}
