```php
<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreateProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_product_with_variants(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create([
            'is_admin' => true,
        ]);

        $category = Category::create([
            'name' => 'Vêtements',
            'slug' => 'vetements',
        ]);

        $image = UploadedFile::fake()->image(
            'product.jpg',
            1200,
            1200
        );

        $response = $this->actingAs($admin)->post(
            route('admin.products.store'),
            [
                'name' => 'Nike Air Max',
                'category_id' => $category->id,
                'price' => 25000,
                'description' => 'Chaussures Nike Air Max.',
                'image' => $image,
                'sizes' => [
                    'M' => [
                        'stock' => 10,
                    ],
                    'L' => [
                        'stock' => 15,
                    ],
                    'XL' => [
                        'stock' => 5,
                    ],
                ],
            ]
        );

        $response->assertRedirect(
            route('admin.products.index')
        );

        $product = Product::where(
            'name',
            'Nike Air Max'
        )->first();

        $this->assertNotNull($product);

        $this->assertSame(
            'nike-air-max',
            $product->slug
        );

        $this->assertSame(
            $category->id,
            $product->category_id
        );

        $this->assertEquals(
            25000,
            $product->price
        );

        $this->assertSame(
            'Chaussures Nike Air Max.',
            $product->description
        );

        $this->assertNotNull($product->image);

        Storage::disk('public')->assertExists(
            $product->image
        );

        $this->assertDatabaseHas('variants', [
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 10,
        ]);

        $this->assertDatabaseHas('variants', [
            'product_id' => $product->id,
            'size' => 'L',
            'stock' => 15,
        ]);

        $this->assertDatabaseHas('variants', [
            'product_id' => $product->id,
            'size' => 'XL',
            'stock' => 5,
        ]);
    }

    public function test_non_admin_cannot_create_a_product(): void
    {
        $user = User::factory()->create([
            'is_admin' => false,
        ]);

        $response = $this->actingAs($user)->post(
            route('admin.products.store'),
            []
        );

        $response->assertForbidden();
    }
}
