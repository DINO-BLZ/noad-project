<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DropProductValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_product_in_a_drop_requires_an_existing_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.drops.store'), [
            'name' => 'Validation Drop',
            'start_date' => now()->addDay()->toDateTimeString(),
            'end_date' => now()->addDays(2)->toDateTimeString(),
            'new_products' => [
                [
                    'name' => 'Uncategorized Product',
                    'price' => 2500,
                    'sizes' => [
                        ['size' => 'M', 'stock' => 5],
                    ],
                ],
            ],
        ]);

        $response->assertSessionHasErrors('new_products.0.category_id');
        $this->assertDatabaseCount('drops', 0);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_new_product_in_a_drop_accepts_a_valid_category(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::create([
            'name' => 'Drop Category',
            'slug' => 'drop-category',
        ]);

        $this->actingAs($admin)->post(route('admin.drops.store'), [
            'name' => 'Valid Drop',
            'start_date' => now()->addDay()->toDateTimeString(),
            'end_date' => now()->addDays(2)->toDateTimeString(),
            'new_products' => [
                [
                    'name' => 'Categorized Product',
                    'price' => 2500,
                    'category_id' => $category->id,
                    'sizes' => [
                        ['size' => 'M', 'stock' => 5, 'sku' => ''],
                    ],
                ],
            ],
        ])->assertRedirect(route('admin.drops.index'));

        $this->assertDatabaseHas('products', [
            'name' => 'Categorized Product',
            'category_id' => $category->id,
        ]);
        $this->assertDatabaseHas('variants', [
            'sku' => null,
            'color' => '',
        ]);
    }
}
