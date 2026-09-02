<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Drop;
use App\Models\Product;
use App\Models\User;
use App\Policies\CategoryPolicy;
use App\Policies\DropPolicy;
use App\Policies\ProductPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_policy_allows_only_admins(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $product = new Product;
        $policy = new ProductPolicy;

        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $product));
        $this->assertTrue($policy->delete($admin, $product));
        $this->assertFalse($policy->create($user));
        $this->assertFalse($policy->update($user, $product));
        $this->assertFalse($policy->delete($user, $product));
    }

    public function test_category_policy_allows_only_admins(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $category = new Category;
        $policy = new CategoryPolicy;

        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $category));
        $this->assertTrue($policy->delete($admin, $category));
        $this->assertFalse($policy->create($user));
        $this->assertFalse($policy->update($user, $category));
        $this->assertFalse($policy->delete($user, $category));
    }

    public function test_drop_policy_allows_only_admins(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $user = User::factory()->create(['is_admin' => false]);
        $drop = new Drop;
        $policy = new DropPolicy;

        $this->assertTrue($policy->create($admin));
        $this->assertTrue($policy->update($admin, $drop));
        $this->assertTrue($policy->delete($admin, $drop));
        $this->assertFalse($policy->create($user));
        $this->assertFalse($policy->update($user, $drop));
        $this->assertFalse($policy->delete($user, $drop));
    }

    public function test_non_admin_cannot_create_a_category_through_http(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->post(route('admin.categories.store'), [
            'name' => 'Unauthorized category',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('categories', ['name' => 'Unauthorized category']);
    }

    public function test_admin_can_create_a_category_through_http(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), [
            'name' => 'Authorized category',
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', [
            'name' => 'Authorized category',
            'slug' => 'authorized-category',
        ]);
    }
}
