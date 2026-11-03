<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Laravel\Scout\EngineManager;
use Laravel\Scout\Engines\Engine;
use Mockery;
use Tests\TestCase;

class ProductScoutIndexingTest extends TestCase
{
    use RefreshDatabase;

    public function test_scout_methods_on_product_model(): void
    {
        $category = Category::create([
            'name' => 'Hoodies & Sweatshirts',
            'slug' => 'hoodies-sweatshirts',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Classic Black Hoodie',
            'slug' => 'classic-black-hoodie',
            'price' => 4500,
            'description' => 'A warm and stylish hoodie.',
        ]);

        // searchableAs
        $this->assertSame('products', $product->searchableAs());

        // shouldBeSearchable
        $this->assertTrue($product->shouldBeSearchable());

        $orphanProduct = new Product(['name' => 'Orphan']);
        $this->assertFalse($orphanProduct->shouldBeSearchable());

        // toSearchableArray
        $searchableArray = $product->toSearchableArray();
        $this->assertSame((int) $product->id, $searchableArray['id']);
        $this->assertSame('Classic Black Hoodie', $searchableArray['name']);
        $this->assertSame('classic-black-hoodie', $searchableArray['slug']);
        $this->assertSame('A warm and stylish hoodie.', $searchableArray['description']);
        $this->assertSame('Hoodies & Sweatshirts', $searchableArray['category']);
        $this->assertSame(4500.0, $searchableArray['price']);

        // makeAllSearchableUsing
        $query = Product::query();
        $product->makeAllSearchableUsing($query);
        $this->assertArrayHasKey('category', $query->getEagerLoads());
    }

    public function test_indexing_is_triggered_when_product_is_created(): void
    {
        $mockEngine = Mockery::mock(Engine::class);
        $mockEngine->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function ($models) {
                return $models instanceof EloquentCollection
                    && $models->first()->name === 'New Indexed Product';
            }));

        $engineManager = $this->app->make(EngineManager::class);
        $engineManager->extend('custom_mock', fn () => $mockEngine);
        config(['scout.driver' => 'custom_mock']);

        $category = Category::create(['name' => 'T-Shirts', 'slug' => 't-shirts']);

        Product::create([
            'category_id' => $category->id,
            'name' => 'New Indexed Product',
            'slug' => 'new-indexed-product',
            'price' => 3000,
        ]);
    }

    public function test_indexing_is_triggered_when_product_is_updated(): void
    {
        $mockEngine = Mockery::mock(Engine::class);

        $engineManager = $this->app->make(EngineManager::class);
        $engineManager->extend('custom_mock', fn () => $mockEngine);
        config(['scout.driver' => 'custom_mock']);

        $category = Category::create(['name' => 'Caps', 'slug' => 'caps']);

        // First creation triggers update
        $mockEngine->shouldReceive('update')->once();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Original Cap',
            'slug' => 'original-cap',
            'price' => 1500,
        ]);

        // Next update triggers update with modified name
        $mockEngine->shouldReceive('update')
            ->once()
            ->with(Mockery::on(function ($models) {
                return $models instanceof EloquentCollection
                    && $models->first()->name === 'Updated Cap Name';
            }));

        $product->update([
            'name' => 'Updated Cap Name',
        ]);
    }

    public function test_product_is_removed_from_index_when_deleted(): void
    {
        $mockEngine = Mockery::mock(Engine::class);

        $engineManager = $this->app->make(EngineManager::class);
        $engineManager->extend('custom_mock', fn () => $mockEngine);
        config(['scout.driver' => 'custom_mock']);

        $category = Category::create(['name' => 'Caps', 'slug' => 'caps']);

        $mockEngine->shouldReceive('update')->once();

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cap To Delete',
            'slug' => 'cap-to-delete',
            'price' => 1500,
        ]);

        $mockEngine->shouldReceive('delete')
            ->once()
            ->with(Mockery::on(function ($models) use ($product) {
                return $models instanceof EloquentCollection
                    && $models->first()->id === $product->id;
            }));

        $product->delete();
    }

    public function test_indexing_errors_are_logged_and_do_not_crash_application(): void
    {
        Log::spy();

        $mockEngine = Mockery::mock(Engine::class);
        $mockEngine->shouldReceive('update')
            ->andThrow(new Exception('Elasticsearch node connection timeout'));

        $engineManager = $this->app->make(EngineManager::class);
        $engineManager->extend('failing_mock', fn () => $mockEngine);
        config(['scout.driver' => 'failing_mock']);

        $category = Category::create(['name' => 'Pants', 'slug' => 'pants']);

        // Creating product should succeed in database even if Elasticsearch fails
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Resilient Cargo Pants',
            'slug' => 'resilient-cargo-pants',
            'price' => 5500,
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id]);

        // Verify that the error was NOT silently ignored, but logged
        Log::shouldHaveReceived('error')
            ->atLeast()
            ->once()
            ->with(
                Mockery::pattern('/Erreur lors de la synchronisation de l\'index Scout/'),
                Mockery::hasKey('exception')
            );
    }

    public function test_removal_errors_are_logged_and_do_not_crash_deletion(): void
    {
        Log::spy();

        $mockEngine = Mockery::mock(Engine::class);
        $mockEngine->shouldReceive('update'); // creation
        $mockEngine->shouldReceive('delete')
            ->andThrow(new Exception('Elasticsearch index unavailable'));

        $engineManager = $this->app->make(EngineManager::class);
        $engineManager->extend('failing_delete_mock', fn () => $mockEngine);
        config(['scout.driver' => 'failing_delete_mock']);

        $category = Category::create(['name' => 'Pants', 'slug' => 'pants']);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Cargo To Delete',
            'slug' => 'cargo-to-delete',
            'price' => 5500,
        ]);

        // Product deletion in DB succeeds
        $product->delete();

        $this->assertDatabaseMissing('products', ['id' => $product->id]);

        // Verify error was logged
        Log::shouldHaveReceived('error')
            ->atLeast()
            ->once()
            ->with(
                Mockery::pattern('/Erreur lors de la suppression de l\'index Scout/'),
                Mockery::hasKey('exception')
            );
    }

    public function test_reindex_products_command_skips_when_scout_driver_is_null(): void
    {
        config(['scout.driver' => 'null']);

        $this->artisan('search:reindex-products')
            ->expectsOutput('Scout est désactivé, réindexation ignorée.')
            ->assertSuccessful();
    }

    public function test_reindex_products_command_calls_import_when_driver_is_enabled(): void
    {
        config(['scout.driver' => 'custom']);

        $category = Category::create(['name' => 'Jackets', 'slug' => 'jackets']);
        Product::create([
            'category_id' => $category->id,
            'name' => 'Winter Jacket',
            'slug' => 'winter-jacket',
            'price' => 8000,
        ]);

        $mockEngine = Mockery::mock(Engine::class);
        $mockEngine->shouldReceive('update')->atLeast()->once();

        $engineManager = $this->app->make(EngineManager::class);
        $engineManager->extend('custom', fn () => $mockEngine);

        $this->artisan('search:reindex-products')
            ->assertSuccessful();
    }

    public function test_automatic_database_fallback_when_elasticsearch_fails(): void
    {
        config([
            'scout.driver' => 'elastic',
            'elastic.client.connections.default.hosts' => ['127.0.0.1:1'],
        ]);

        $category = Category::create(['name' => 'Boutique Collection', 'slug' => 'boutique']);
        Product::withoutSyncingToSearch(function () use ($category): void {
            Product::create([
                'name' => 'Elastic Fallback Sneaker',
                'slug' => 'elastic-fallback-sneaker',
                'price' => 9500,
                'category_id' => $category->id,
                'description' => 'Comfortable running sneakers',
            ]);
        });

        // Search index page fallback
        $response = $this->get(route('search.index', ['q' => 'Fallback Sneaker']));
        $response->assertOk();
        $response->assertSee(strtoupper('Elastic Fallback Sneaker'));

        // Search suggestions API fallback
        $suggestionsResponse = $this->getJson(route('search.suggestions', ['q' => 'Fallback Sneaker']));
        $suggestionsResponse->assertOk();
        $suggestionsResponse->assertJsonFragment(['name' => 'Elastic Fallback Sneaker']);
    }
}
