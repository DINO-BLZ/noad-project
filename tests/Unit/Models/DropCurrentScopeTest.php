<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Drop;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropCurrentScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_current_returns_the_most_recent_active_drop(): void
    {
        $olderDrop = $this->createDrop(
            now()->subDays(2),
            now()->addDays(5)
        );

        $newerDrop = $this->createDrop(
            now()->subDay(),
            now()->addDays(6)
        );

        $currentDrop = Drop::current()->first();

        $this->assertNotNull($currentDrop);

        $this->assertSame(
            $newerDrop->id,
            $currentDrop->id
        );

        $this->assertNotSame(
            $olderDrop->id,
            $currentDrop->id
        );
    }

    public function test_current_returns_the_only_active_drop(): void
    {
        $activeDrop = $this->createDrop(
            now()->subDay(),
            now()->addDays(5)
        );

        $this->createDrop(
            now()->addDay(),
            now()->addDays(6)
        );

        $currentDrop = Drop::current()->first();

        $this->assertNotNull($currentDrop);

        $this->assertSame(
            $activeDrop->id,
            $currentDrop->id
        );
    }

    public function test_current_returns_null_when_there_is_no_active_drop(): void
    {
        $this->createDrop(
            now()->subDays(5),
            now()->subDay()
        );

        $this->createDrop(
            now()->addDay(),
            now()->addDays(5)
        );

        $currentDrop = Drop::current()->first();

        $this->assertNull($currentDrop);
    }

    private function createDrop(
        $startDate,
        $endDate
    ): Drop {
        $drop = Drop::create([
            'name' => 'Test Drop '.uniqid(),
            'slug' => 'test-drop-'.uniqid(),
            'description' => 'Drop de test.',
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max_whitelist_slots' => 10,
        ]);

        $product = $this->createProduct();

        $drop->products()->attach(
            $product->id,
            [
                'quota' => 10,
            ]
        );

        return $drop->fresh();
    }

    private function createProduct(): Product
    {
        $category = Category::create([
            'name' => 'Test Category '.uniqid(),
            'slug' => 'test-category-'.uniqid(),
        ]);

        $product = Product::create([
            'name' => 'Test Product '.uniqid(),
            'slug' => 'test-product-'.uniqid(),
            'price' => 100,
            'category_id' => $category->id,
        ]);

        $this->createVariant($product);

        return $product;
    }

    private function createVariant(Product $product): Variant
    {
        return $product->variants()->create([
            'size' => 'Unique',
            'stock' => 10,
            'sku' => 'TEST-'.strtoupper(uniqid()),
        ]);
    }
}
