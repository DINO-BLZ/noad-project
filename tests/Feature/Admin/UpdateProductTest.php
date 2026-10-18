<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UpdateProductTest extends TestCase
{
    use RefreshDatabase;

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    private function createAdmin(): User
    {
        return User::factory()->create([
            'is_admin' => true,
        ]);
    }

    private function createCategory(): Category
    {
        return Category::create([
            'name' => 'Vêtements',
            'slug' => 'vetements-'.uniqid(),
        ]);
    }

    private function createProduct(Category $category): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => 'T-shirt',
            'slug' => 't-shirt-'.uniqid(),
            'price' => 5000,
            'description' => 'Description du produit',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Product information
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_update_product_information(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => 'Nouveau produit',
                'category_id' => $category->id,
                'price' => 7500,
                'description' => 'Nouvelle description',
                'variants' => [],
            ]
        );

        $response->assertRedirect(
            route('admin.products.index')
        );

        $product->refresh();

        $this->assertSame(
            'Nouveau produit',
            $product->name
        );

        $this->assertEquals(
            7500,
            $product->price
        );

        $this->assertSame(
            'Nouvelle description',
            $product->description
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Variants
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_update_existing_variant(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'M',
            'stock' => 5,
            'sku' => 'TS-M-001',
            'color' => 'Noir',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,

                'variants' => [
                    [
                        'id' => $variant->id,
                        'size' => 'L',
                        'stock' => 20,
                        'sku' => 'TS-L-001',
                        'color' => 'Blanc',
                    ],
                ],
            ]
        );

        $response->assertRedirect();

        $variant->refresh();

        $this->assertSame('L', $variant->size);
        $this->assertEquals(20, $variant->stock);
        $this->assertSame('TS-L-001', $variant->sku);
        $this->assertSame('Blanc', $variant->color);
    }

    public function test_admin_can_add_new_variant(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,

                'variants' => [
                    [
                        'size' => 'XL',
                        'stock' => 10,
                        'sku' => 'TS-XL-001',
                        'color' => 'Noir',
                    ],
                ],
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('variants', [
            'product_id' => $product->id,
            'size' => 'XL',
            'stock' => 10,
            'sku' => 'TS-XL-001',
            'color' => 'Noir',
        ]);
    }

    public function test_admin_can_remove_existing_variant(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'M',
            'stock' => 5,
            'sku' => 'TS-M-001',
            'color' => 'Noir',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,
                'remove_variants' => [$variant->id],
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('variants', [
            'id' => $variant->id,
        ]);
    }

    public function test_updating_product_information_preserves_existing_variants_and_stocks(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variantM = $product->variants()->create([
            'size' => 'M',
            'stock' => 10,
            'sku' => 'TS-M-100',
            'color' => 'Noir',
        ]);

        $variantL = $product->variants()->create([
            'size' => 'L',
            'stock' => 20,
            'sku' => 'TS-L-200',
            'color' => 'Blanc',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => 'Nom Mis À Jour',
                'category_id' => $category->id,
                'price' => 9999,
                'description' => 'Nouvelle description',
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('variants', [
            'id' => $variantM->id,
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
            'sku' => 'TS-M-100',
        ]);

        $this->assertDatabaseHas('variants', [
            'id' => $variantL->id,
            'product_id' => $product->id,
            'size' => 'L',
            'stock' => 20,
            'sku' => 'TS-L-200',
        ]);
    }

    public function test_empty_variants_array_does_not_delete_variants(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'S',
            'stock' => 7,
            'sku' => 'TS-S-001',
            'color' => 'Bleu',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,
                'variants' => [],
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('variants', [
            'id' => $variant->id,
            'stock' => 7,
        ]);
    }

    public function test_admin_can_update_stocks_using_blade_sizes_format(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variantM = $product->variants()->create([
            'size' => 'M',
            'stock' => 5,
            'sku' => 'TS-M-ORIG',
            'color' => 'Noir',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,
                'sizes' => [
                    'M' => ['stock' => 15],
                    'L' => ['stock' => 8],
                ],
            ]
        );

        $response->assertRedirect();

        $variantM->refresh();
        $this->assertSame(15, $variantM->stock);
        $this->assertSame('TS-M-ORIG', $variantM->sku);
        $this->assertSame('Noir', $variantM->color);

        $this->assertDatabaseHas('variants', [
            'product_id' => $product->id,
            'size' => 'L',
            'stock' => 8,
        ]);
    }

    public function test_zero_stock_sizes_do_not_create_new_variants(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $product->variants()->create([
            'size' => 'M',
            'stock' => 5,
            'sku' => 'TS-M-ZERO',
            'color' => 'Noir',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,
                'sizes' => [
                    'XS' => ['stock' => 0],
                    'S' => ['stock' => 0],
                    'M' => ['stock' => 5],
                    'L' => ['stock' => 0],
                    'XL' => ['stock' => 0],
                    'XXL' => ['stock' => 0],
                ],
            ]
        );

        $response->assertRedirect();

        $this->assertCount(1, $product->variants()->get());
    }

    public function test_cannot_remove_variant_belonging_to_another_product(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $productA = $this->createProduct($category);
        $productB = $this->createProduct($category);

        $variantB = $productB->variants()->create([
            'size' => 'M',
            'stock' => 5,
            'sku' => 'TS-B-001',
            'color' => 'Noir',
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $productA),
            [
                'name' => $productA->name,
                'category_id' => $category->id,
                'price' => $productA->price,
                'description' => $productA->description,
                'remove_variants' => [$variantB->id],
            ]
        );

        $response->assertSessionHasErrors('remove_variants');

        $this->assertDatabaseHas('variants', [
            'id' => $variantB->id,
            'product_id' => $productB->id,
        ]);
    }

    public function test_admin_can_remove_variant_with_inactive_order(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'M',
            'stock' => 5,
            'sku' => 'TS-INACTIVE-001',
            'color' => 'Noir',
        ]);

        $order = Order::create([
            'full_name' => 'Jane Doe',
            'phone' => '123456789',
            'address' => '456 Street',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => OrderStatus::Delivered,
            'total' => 25,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 25,
            'product_name' => $product->name,
            'variant_size' => $variant->size,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,
                'remove_variants' => [$variant->id],
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('variants', [
            'id' => $variant->id,
        ]);

        $this->assertNull($orderItem->fresh()->variant_id);
    }

    public function test_admin_cannot_remove_variant_linked_to_pending_order(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'M',
            'stock' => 5,
            'sku' => 'TS-PENDING-001',
            'color' => 'Noir',
        ]);

        $order = Order::create([
            'full_name' => 'John Doe',
            'phone' => '123456789',
            'address' => '123 Main',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => OrderStatus::Pending,
            'total' => 25,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 25,
            'product_name' => $product->name,
            'variant_size' => $variant->size,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,
                'remove_variants' => [$variant->id],
            ]
        );

        $response->assertSessionHasErrors('remove_variants');

        $this->assertDatabaseHas('variants', [
            'id' => $variant->id,
            'stock' => 5,
        ]);
    }

    public function test_admin_cannot_remove_variant_linked_to_paid_order(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'L',
            'stock' => 8,
            'sku' => 'TS-PAID-001',
            'color' => 'Blanc',
        ]);

        $order = Order::create([
            'full_name' => 'John Doe',
            'phone' => '123456789',
            'address' => '123 Main',
            'wilaya' => 'Algiers',
            'payment_method' => 'cib',
            'status' => OrderStatus::Paid,
            'total' => 50,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 25,
            'product_name' => $product->name,
            'variant_size' => $variant->size,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,
                'remove_variants' => [$variant->id],
            ]
        );

        $response->assertSessionHasErrors('remove_variants');

        $this->assertDatabaseHas('variants', [
            'id' => $variant->id,
            'stock' => 8,
        ]);
    }

    public function test_protected_variant_and_its_stock_remain_unchanged_after_rejection(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'XL',
            'stock' => 12,
            'sku' => 'TS-XL-PROTECT',
            'color' => 'Bleu',
        ]);

        $order = Order::create([
            'full_name' => 'John Doe',
            'phone' => '123456789',
            'address' => '123 Main',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => OrderStatus::Pending,
            'total' => 50,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 50,
            'product_name' => $product->name,
            'variant_size' => $variant->size,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => 'Tentative Modification',
                'category_id' => $category->id,
                'price' => 9999,
                'description' => $product->description,
                'remove_variants' => [$variant->id],
            ]
        );

        $response->assertSessionHasErrors('remove_variants');

        // Vérification de non-altération (rollback transactionnel)
        $variant->refresh();
        $this->assertSame(12, $variant->stock);
        $this->assertSame('TS-XL-PROTECT', $variant->sku);
        $this->assertSame('Bleu', $variant->color);
        $this->assertSame('XL', $variant->size);

        $product->refresh();
        $this->assertSame('T-shirt', $product->name);
    }

    /*
    |--------------------------------------------------------------------------
    | Cover image
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_replace_product_cover_image(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $category = $this->createCategory();

        $oldImage = 'products/old-cover.jpg';

        Storage::disk('public')->put(
            $oldImage,
            'old image'
        );

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'T-shirt',
            'slug' => 't-shirt-'.uniqid(),
            'price' => 5000,
            'description' => 'Description du produit',
            'image' => $oldImage,
        ]);

        $newImage = UploadedFile::fake()->image(
            'new-cover.jpg'
        );

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'category_id' => $category->id,
                'price' => $product->price,
                'description' => $product->description,

                'variants' => [],

                'image' => $newImage,
            ]
        );

        $response->assertRedirect();

        $product->refresh();

        $this->assertNotSame(
            $oldImage,
            $product->image
        );

        Storage::disk('public')->assertExists(
            $product->image
        );

        Storage::disk('public')->assertMissing(
            $oldImage
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Gallery
    |--------------------------------------------------------------------------
    */

    public function test_admin_can_add_gallery_images(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $image1 = UploadedFile::fake()->image(
            'gallery-1.jpg'
        );

        $image2 = UploadedFile::fake()->image(
            'gallery-2.jpg'
        );

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'price' => $product->price,
                'category_id' => $product->category_id,
                'description' => $product->description,

                'variants' => [],

                'images' => [
                    $image1,
                    $image2,
                ],
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseCount(
            'product_images',
            2
        );

        $images = ProductImage::where(
            'product_id',
            $product->id
        )->get();

        $this->assertCount(
            2,
            $images
        );

        foreach ($images as $image) {
            Storage::disk('public')->assertExists(
                $image->path
            );
        }
    }

    public function test_admin_can_change_primary_gallery_image(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $first = ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/gallery/first.jpg',
            'position' => 0,
            'is_primary' => true,
        ]);

        $second = ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/gallery/second.jpg',
            'position' => 1,
            'is_primary' => false,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'price' => $product->price,
                'category_id' => $product->category_id,
                'description' => $product->description,
                'variants' => [],
                'primary_image' => (string) $second->id,
            ]
        );

        $response->assertRedirect();

        $this->assertFalse($first->fresh()->is_primary);
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertSame(1, $product->images()->where('is_primary', true)->count());
    }

    public function test_saving_product_keeps_existing_primary_gallery_image(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/gallery/first.jpg',
            'position' => 0,
            'is_primary' => false,
        ]);

        $primary = ProductImage::create([
            'product_id' => $product->id,
            'path' => 'products/gallery/second.jpg',
            'position' => 1,
            'is_primary' => true,
        ]);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => 'Nom mis à jour',
                'price' => $product->price,
                'category_id' => $product->category_id,
                'description' => $product->description,
                'variants' => [],
                'primary_image' => (string) $primary->id,
            ]
        );

        $response->assertRedirect();

        $this->assertTrue($primary->fresh()->is_primary);
        $this->assertSame(1, $product->images()->where('is_primary', true)->count());
        $this->assertSame($primary->id, $product->images()->where('is_primary', true)->value('id'));
    }

    public function test_empty_skus_are_stored_as_null_for_multiple_variants(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $response = $this->actingAs($admin)->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'price' => $product->price,
                'category_id' => $product->category_id,
                'description' => $product->description,
                'variants' => [
                    [
                        'size' => 'M',
                        'stock' => 2,
                        'sku' => '',
                        'color' => 'Noir',
                    ],
                    [
                        'size' => 'L',
                        'stock' => 3,
                        'sku' => '',
                        'color' => 'Noir',
                    ],
                ],
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseCount('variants', 2);
        $this->assertDatabaseHas('variants', [
            'product_id' => $product->id,
            'size' => 'M',
            'sku' => null,
        ]);
        $this->assertDatabaseHas('variants', [
            'product_id' => $product->id,
            'size' => 'L',
            'sku' => null,
        ]);
    }

    public function test_removed_gallery_file_is_kept_when_product_update_fails(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $path = 'products/gallery/keep-me.jpg';
        Storage::disk('public')->put($path, 'contents');

        $image = ProductImage::create([
            'product_id' => $product->id,
            'path' => $path,
            'position' => 0,
            'is_primary' => true,
        ]);

        $response = $this->actingAs($admin)->from(
            route('admin.products.edit', $product)
        )->put(
            route('admin.products.update', $product),
            [
                'name' => $product->name,
                'price' => $product->price,
                'category_id' => $product->category_id,
                'description' => $product->description,
                'variants' => [],
                'remove_images' => [$image->id],
                'primary_image' => '999999',
            ]
        );

        $response->assertRedirect(route('admin.products.edit', $product));
        $response->assertSessionHasErrors('primary_image');

        $this->assertDatabaseHas('product_images', [
            'id' => $image->id,
            'path' => $path,
        ]);

        Storage::disk('public')->assertExists($path);
    }

    /*
    |--------------------------------------------------------------------------
    | Security
    |--------------------------------------------------------------------------
    */

    public function test_non_admin_cannot_update_product(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $response = $this->actingAs($user)->put(
            route('admin.products.update', $product),
            [
                'name' => 'Produit piraté',
                'category_id' => $category->id,
                'price' => 1,
                'description' => 'Modification interdite',
                'variants' => [],
            ]
        );

        $response->assertForbidden();

        $product->refresh();

        $this->assertSame(
            'T-shirt',
            $product->name
        );

        $this->assertEquals(
            5000,
            $product->price
        );
    }

    public function test_guest_cannot_update_product(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $response = $this->put(
            route('admin.products.update', $product),
            [
                'name' => 'Produit piraté',
                'category_id' => $category->id,
                'price' => 1,
                'description' => 'Modification interdite',
                'variants' => [],
            ]
        );

        $response->assertRedirect(
            route('login')
        );

        $product->refresh();

        $this->assertSame(
            'T-shirt',
            $product->name
        );
    }
}
