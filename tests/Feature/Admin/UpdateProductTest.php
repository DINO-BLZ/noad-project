<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
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

                'variants' => [],
            ]
        );

        $response->assertRedirect();

        $this->assertDatabaseMissing('variants', [
            'id' => $variant->id,
        ]);
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
