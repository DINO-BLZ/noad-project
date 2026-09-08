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
            'slug' => 'vetements-' . uniqid(),
        ]);
    }

    private function createProduct(Category $category): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => 'T-shirt',
            'slug' => 't-shirt-' . uniqid(),
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
            'slug' => 't-shirt-' . uniqid(),
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