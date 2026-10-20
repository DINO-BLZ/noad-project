<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOrderHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function createOrderForUser(User $user, string $productName = 'T-Shirt Oversize'): Order
    {
        $category = Category::firstOrCreate(
            ['slug' => 'vetements'],
            ['name' => 'Vêtements']
        );

        $product = Product::create([
            'category_id' => $category->id,
            'name' => $productName,
            'slug' => strtolower(str_replace(' ', '-', $productName)).'-'.uniqid(),
            'price' => 4500,
            'description' => 'Description test',
            'is_published' => true,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'color' => 'Noir',
            'sku' => 'TSH-'.uniqid(),
            'stock' => 10,
        ]);

        $order = Order::create([
            'user_id' => $user->id,
            'full_name' => $user->name,
            'phone' => '0555123456',
            'address' => '12 Rue Didouche Mourad',
            'wilaya' => 'Alger',
            'payment_method' => 'cod',
            'status' => OrderStatus::Pending,
            'total' => 4500,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 4500,
            'variant_sku' => $variant->sku,
            'variant_size' => $variant->size,
            'variant_color' => $variant->color,
            'product_name' => $product->name,
        ]);

        return $order;
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderForUser($user);

        $responseIndex = $this->get(route('orders.index'));
        $responseIndex->assertRedirect(route('login'));

        $responseShow = $this->get(route('orders.show', $order));
        $responseShow->assertRedirect(route('login'));
    }

    public function test_user_sees_empty_state_when_having_no_orders(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertOk();
        $response->assertSee('AUCUNE COMMANDE ENREGISTRÉE');
        $response->assertSee('ACCÉDER À LA BOUTIQUE');
    }

    public function test_user_can_view_their_orders_list(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderForUser($user, 'Hoodie Heavyweight');

        $response = $this->actingAs($user)->get(route('orders.index'));

        $response->assertOk();
        $response->assertSee($order->formatted_reference);
        $response->assertSee('Hoodie Heavyweight');
        $response->assertSee('4 500 DA');
        $response->assertSee('EN ATTENTE DE CONFIRMATION');
    }

    public function test_user_does_not_see_other_users_orders(): void
    {
        $alice = User::factory()->create(['name' => 'Alice']);
        $bob = User::factory()->create(['name' => 'Bob']);

        $aliceOrder = $this->createOrderForUser($alice, 'Produit Alice');
        $bobOrder = $this->createOrderForUser($bob, 'Produit Bob');

        $response = $this->actingAs($alice)->get(route('orders.index'));

        $response->assertOk();
        $response->assertSee($aliceOrder->formatted_reference);
        $response->assertSee('Produit Alice');
        $response->assertDontSee($bobOrder->formatted_reference);
        $response->assertDontSee('Produit Bob');
    }

    public function test_user_can_view_their_order_details(): void
    {
        $user = User::factory()->create();
        $order = $this->createOrderForUser($user, 'Cargo Tactical');

        $response = $this->actingAs($user)->get(route('orders.show', $order));

        $response->assertOk();
        $response->assertSee($order->formatted_reference);
        $response->assertSee('Cargo Tactical');
        $response->assertSee('Alger');
        $response->assertSee('12 Rue Didouche Mourad');
        $response->assertSee('RETOUR À MES COMMANDES');
    }

    public function test_user_cannot_view_another_users_order(): void
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();

        $bobOrder = $this->createOrderForUser($bob);

        $response = $this->actingAs($alice)->get(route('orders.show', $bobOrder));

        $response->assertForbidden();
    }

    public function test_admin_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $this->createOrderForUser($admin);

        $response = $this->actingAs($admin)->get(route('orders.index'));
        $response->assertRedirect(route('admin.dashboard'));
    }
}
