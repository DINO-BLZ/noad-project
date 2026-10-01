<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Drop;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoMetadataTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_pages_render_canonical_and_descriptive_metadata(): void
    {
        $this->get(route('home'))
            ->assertOk()
            ->assertSee('<meta name="description" content="Découvrez les pièces NOAD et les prochains drops disponibles en Algérie.">', false)
            ->assertSee('<link rel="canonical" href="'.route('home').'">', false);

        $category = Category::create(['name' => 'SEO Category', 'slug' => 'seo-category']);
        Product::withoutSyncingToSearch(function () use ($category): void {
            Product::create([
                'name' => 'SEO Product',
                'slug' => 'seo-product',
                'price' => 1000,
                'category_id' => $category->id,
                'description' => 'Description SEO réelle.',
            ]);
        });

        $this->get(route('products.show', 'seo-product'))
            ->assertOk()
            ->assertSee('<title>SEO Product — NOAD</title>', false)
            ->assertSee('Description SEO réelle.');
    }

    public function test_sitemap_contains_only_public_static_product_and_drop_urls(): void
    {
        $category = Category::create(['name' => 'Sitemap Category', 'slug' => 'sitemap-category']);
        Product::withoutSyncingToSearch(function () use ($category): void {
            Product::create([
                'name' => 'Sitemap Product',
                'slug' => 'sitemap-product',
                'price' => 1000,
                'category_id' => $category->id,
            ]);
        });
        Drop::create([
            'name' => 'Sitemap Drop',
            'slug' => 'sitemap-drop',
            'start_date' => now()->addDay(),
            'end_date' => now()->addDays(2),
        ]);

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('products.show', 'sitemap-product'), false)
            ->assertSee(route('drops.show', 'sitemap-drop'), false)
            ->assertDontSee('/admin/', false);
    }

    public function test_dynamic_metadata_escapes_product_controlled_content(): void
    {
        $category = Category::create(['name' => 'Escaping Category', 'slug' => 'escaping-category']);
        Product::withoutSyncingToSearch(function () use ($category): void {
            Product::create([
                'name' => '<script>NOAD</script>',
                'slug' => 'script-product',
                'price' => 1000,
                'category_id' => $category->id,
            ]);
        });

        $response = $this->get(route('products.show', 'script-product'));
        $response->assertOk();

        $head = strstr($response->getContent(), '</head>', true);

        $this->assertIsString($head);
        $this->assertFalse(str_contains($head, '<script>NOAD</script>'));
        $this->assertTrue(
            str_contains($head, '&lt;script&gt;NOAD&lt;/script&gt;'),
            'Metadata head: '.substr($head, 0, 700)
        );
    }

    public function test_admin_pages_are_marked_noindex(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_customer_and_authentication_pages_are_marked_noindex(): void
    {
        $this->get(route('cart.index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get(route('register'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_checkout_success_and_whitelist_pages_are_marked_noindex(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Private Category', 'slug' => 'private-category']);
        $product = Product::withoutSyncingToSearch(function () use ($category): Product {
            return Product::create([
                'name' => 'Private Product',
                'slug' => 'private-product',
                'price' => 1000,
                'category_id' => $category->id,
            ]);
        });
        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 2,
            'sku' => 'PRIVATE-SKU',
            'color' => 'Black',
        ]);
        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);
        $order = Order::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'phone' => '0555000000',
            'address' => 'Private Address',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => OrderStatus::Pending,
            'total' => 1000,
        ]);

        $this->actingAs($user)
            ->get(route('checkout.index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get(route('whitelist.index'))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get(route('checkout.success', $order))
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }
}
