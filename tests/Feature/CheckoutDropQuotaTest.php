<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Drop;
use App\Models\DropWhitelist;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutDropQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_enforces_quota_across_variants_of_the_same_product(): void
    {
        $drop = $this->createActiveDrop();

        $userA = User::factory()->create();
        $userB = User::factory()->create();

        DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $userA->id,
            'status' => 'approved',
        ]);

        DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $userB->id,
            'status' => 'approved',
        ]);

        $product = $this->createProduct();

        $variantM = $this->createVariant(
            $product,
            'M',
            50
        );

        $variantL = $this->createVariant(
            $product,
            'L',
            50
        );

        /*
         * Quota total du produit dans ce drop :
         * 10 unités.
         */
        $drop->products()->attach($product->id, [
            'quota' => 10,
        ]);

        /*
         * Client A commande 6 en taille M.
         */
        CartItem::create([
            'user_id' => $userA->id,
            'session_id' => null,
            'variant_id' => $variantM->id,
            'quantity' => 6,
        ]);

        /*
         * Client B commande 6 en taille L.
         *
         * Le stock L est suffisant.
         * Mais le quota global du produit n'est plus suffisant.
         */
        CartItem::create([
            'user_id' => $userB->id,
            'session_id' => null,
            'variant_id' => $variantL->id,
            'quantity' => 6,
        ]);

        $payload = [
            'full_name' => 'John Doe',
            'phone' => '0555000000',
            'address' => 'Algiers',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
        ];

        /*
         * Première commande :
         * 6 / 10 du quota.
         */
        $this->actingAs($userA)
            ->post(
                route('checkout.store'),
                $payload
            )
            ->assertRedirect();

        /*
         * Deuxième commande :
         * demande 6 alors qu'il ne reste que 4.
         */
        $response = $this->actingAs($userB)
            ->post(
                route('checkout.store'),
                $payload
            );

        $response->assertRedirectBackWithErrors('checkout');

        $variantM->refresh();
        $variantL->refresh();

        /*
         * M : 50 - 6 = 44.
         */
        $this->assertSame(
            44,
            $variantM->stock
        );

        /*
         * L n'a jamais été vendu.
         *
         * Le quota a été rejeté AVANT le décrément du stock.
         */
        $this->assertSame(
            50,
            $variantL->stock
        );

        /*
         * Une seule commande existe.
         */
        $this->assertSame(
            1,
            Order::count()
        );

        /*
         * Le panier du deuxième utilisateur existe toujours.
         */
        $this->assertDatabaseCount(
            'cart_items',
            1
        );

        $this->assertDatabaseHas('cart_items', [
            'user_id' => $userB->id,
            'variant_id' => $variantL->id,
            'quantity' => 6,
        ]);
    }

    public function test_rejected_quota_does_not_decrement_stock(): void
    {
        $drop = $this->createActiveDrop();

        $user = User::factory()->create();

        DropWhitelist::create([
            'drop_id' => $drop->id,
            'user_id' => $user->id,
            'status' => 'approved',
        ]);

        $product = $this->createProduct();

        $variant = $this->createVariant(
            $product,
            'M',
            100
        );

        /*
         * Quota = 10.
         */
        $drop->products()->attach($product->id, [
            'quota' => 10,
        ]);

        /*
         * Demande immédiatement 11.
         */
        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 11,
        ]);

        $response = $this->actingAs($user)
            ->post(
                route('checkout.store'),
                [
                    'full_name' => 'John Doe',
                    'phone' => '0555000000',
                    'address' => 'Algiers',
                    'wilaya' => 'Algiers',
                    'payment_method' => 'cod',
                ]
            );

        $response->assertRedirectBackWithErrors('checkout');

        $variant->refresh();

        /*
         * Aucun stock n'est perdu.
         */
        $this->assertSame(
            100,
            $variant->stock
        );

        /*
         * Aucune commande.
         */
        $this->assertDatabaseCount(
            'orders',
            0
        );

        $this->assertDatabaseCount(
            'order_items',
            0
        );
    }

    private function createActiveDrop(): Drop
    {
        return Drop::create([
            'name' => 'Quota Test Drop '.uniqid(),
            'slug' => 'quota-test-drop-'.uniqid(),
            'start_date' => now()->subHour(),
            'end_date' => now()->addDay(),
        ]);
    }

    private function createProduct(): Product
    {
        $unique = uniqid();

        $category = Category::create([
            'name' => 'Quota Category '.$unique,
            'slug' => 'quota-category-'.$unique,
        ]);

        return Product::create([
            'name' => 'Quota Product '.$unique,
            'slug' => 'quota-product-'.$unique,
            'price' => 25,
            'category_id' => $category->id,
        ]);
    }

    private function createVariant(
        Product $product,
        string $size,
        int $stock
    ): Variant {
        return Variant::create([
            'product_id' => $product->id,
            'size' => $size,
            'stock' => $stock,
            'sku' => 'QUOTA-'.uniqid(),
            'color' => 'Black',
        ]);
    }
}