<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_falls_back_to_database_when_elasticsearch_is_unavailable(): void
    {
        config([
            'scout.driver' => 'elastic',
            'elastic.client.connections.default.hosts' => ['127.0.0.1:1'],
        ]);

        $category = Category::create([
            'name' => 'Fallback Category',
            'slug' => 'fallback-category',
        ]);
        Product::withoutSyncingToSearch(function () use ($category): void {
            Product::create([
                'name' => 'Fallback Jacket',
                'slug' => 'fallback-jacket',
                'price' => 3200,
                'category_id' => $category->id,
            ]);
        });

        $this->get(route('search.index', ['q' => 'Fallback Jacket']))
            ->assertOk()
            ->assertSee('Fallback Jacket');

        $this->get(route('search.suggestions', ['q' => 'Fallback Jacket']))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Fallback Jacket']);
    }
}
