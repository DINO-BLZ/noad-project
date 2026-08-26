<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductImageDeletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_deleting_product_image_removes_physical_file()
    {
        Storage::fake('public');

        $category = Category::create(['name' => 'Test', 'slug' => 'test']);

        $product = Product::create([
            'name' => 'Hat',
            'slug' => 'hat-test',
            'price' => 10,
            'category_id' => $category->id,
        ]);

        $path = 'products/photo.jpg';
        Storage::disk('public')->put($path, 'contents');

        $img = ProductImage::create([
            'product_id' => $product->id,
            'path' => $path,
            'position' => 0,
            'is_primary' => false,
        ]);

        $this->assertTrue(Storage::disk('public')->exists($path));

        $img->delete();

        $this->assertFalse(Storage::disk('public')->exists($path));
    }
}
