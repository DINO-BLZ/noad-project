<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatus;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteProductTest extends TestCase
{
    use RefreshDatabase;

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
            'slug' => 'vetements-'.uniqid(),
        ]);
    }

    private function createProduct(Category $category): Product
    {
        return Product::create([
            'category_id' => $category->id,
            'name' => 'T-shirt Test',
            'slug' => 't-shirt-test-'.uniqid(),
            'price' => 5000,
            'description' => 'Description test',
        ]);
    }

    public function test_admin_can_delete_product_without_orders(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variantM = $product->variants()->create([
            'size' => 'M',
            'stock' => 10,
            'sku' => 'DEL-M-001',
            'color' => 'Noir',
        ]);

        $variantL = $product->variants()->create([
            'size' => 'L',
            'stock' => 20,
            'sku' => 'DEL-L-001',
            'color' => 'Noir',
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.products.destroy', $product)
        );

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Produit supprimé.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('variants', ['id' => $variantM->id]);
        $this->assertDatabaseMissing('variants', ['id' => $variantL->id]);
    }

    public function test_admin_can_delete_product_with_only_inactive_closed_orders(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'M',
            'stock' => 5,
            'sku' => 'DEL-CLOSED-001',
            'color' => 'Noir',
        ]);

        $order = Order::create([
            'full_name' => 'Client Clôturé',
            'phone' => '123456789',
            'address' => 'Rue Alger',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => OrderStatus::Delivered,
            'total' => 5000,
        ]);

        $orderItem = OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 5000,
            'product_name' => $product->name,
            'variant_size' => $variant->size,
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.products.destroy', $product)
        );

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Produit supprimé.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
        $this->assertDatabaseMissing('variants', ['id' => $variant->id]);

        // Historique de commande conservé via snapshots et nullOnDelete
        $this->assertDatabaseHas('order_items', [
            'id' => $orderItem->id,
            'product_name' => 'T-shirt Test',
            'variant_id' => null,
        ]);
    }

    public function test_admin_cannot_delete_product_linked_to_pending_order(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'M',
            'stock' => 15,
            'sku' => 'DEL-PENDING-001',
            'color' => 'Noir',
        ]);

        $order = Order::create([
            'full_name' => 'Client En Attente',
            'phone' => '123456789',
            'address' => 'Rue Alger',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => OrderStatus::Pending,
            'total' => 5000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 5000,
            'product_name' => $product->name,
            'variant_size' => $variant->size,
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.products.destroy', $product)
        );

        $response->assertSessionHasErrors('product');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('variants', [
            'id' => $variant->id,
            'stock' => 15,
        ]);
    }

    public function test_admin_cannot_delete_product_linked_to_paid_order(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variant = $product->variants()->create([
            'size' => 'L',
            'stock' => 7,
            'sku' => 'DEL-PAID-001',
            'color' => 'Blanc',
        ]);

        $order = Order::create([
            'full_name' => 'Client Payé',
            'phone' => '123456789',
            'address' => 'Rue Alger',
            'wilaya' => 'Algiers',
            'payment_method' => 'cib',
            'status' => OrderStatus::Paid,
            'total' => 5000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
            'price' => 5000,
            'product_name' => $product->name,
            'variant_size' => $variant->size,
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.products.destroy', $product)
        );

        $response->assertSessionHasErrors('product');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseHas('variants', [
            'id' => $variant->id,
            'stock' => 7,
        ]);
    }

    public function test_product_and_variants_remain_intact_after_failed_deletion(): void
    {
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $variantM = $product->variants()->create([
            'size' => 'M',
            'stock' => 10,
            'sku' => 'PROTECT-M-001',
            'color' => 'Noir',
        ]);

        $variantL = $product->variants()->create([
            'size' => 'L',
            'stock' => 20,
            'sku' => 'PROTECT-L-001',
            'color' => 'Blanc',
        ]);

        $order = Order::create([
            'full_name' => 'Client Actif',
            'phone' => '123456789',
            'address' => 'Rue Alger',
            'wilaya' => 'Algiers',
            'payment_method' => 'cod',
            'status' => OrderStatus::Pending,
            'total' => 5000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $variantM->id,
            'quantity' => 1,
            'price' => 5000,
            'product_name' => $product->name,
            'variant_size' => $variantM->size,
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.products.destroy', $product)
        );

        $response->assertSessionHasErrors('product');

        // Vérification de non-altération
        $product->refresh();
        $this->assertSame('T-shirt Test', $product->name);

        $variantM->refresh();
        $this->assertSame(10, $variantM->stock);

        $variantL->refresh();
        $this->assertSame(20, $variantL->stock);
    }

    public function test_guest_and_non_admin_cannot_delete_product(): void
    {
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        // Invité
        $guestResponse = $this->delete(route('admin.products.destroy', $product));
        $guestResponse->assertRedirect(route('login'));

        // Utilisateur non-admin
        $user = User::factory()->create(['is_admin' => false]);
        $userResponse = $this->actingAs($user)->delete(route('admin.products.destroy', $product));
        $userResponse->assertForbidden();

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }
    // -------------------------------------------------------------------------
    // Tests : produit sans variantes
    // -------------------------------------------------------------------------

    public function test_admin_can_delete_product_without_variants_and_without_orders(): void
    {
        // Vérifie que la suppression fonctionne même quand le produit n'a
        // aucune variante (le verrou produit suffit à sérialiser l'opération).
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        // Pas de variantes créées volontairement.
        $this->assertSame(0, $product->variants()->count());

        $response = $this->actingAs($admin)->delete(
            route('admin.products.destroy', $product)
        );

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Produit supprimé.');

        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_cannot_delete_product_without_variants_but_with_pending_order_via_product_id(): void
    {
        // Simule le cas où une variante existait, une commande a été passée,
        // puis la variante a été supprimée (nullOnDelete sur variant_id).
        // L'order_item conserve product_id mais variant_id = NULL.
        // La vérification hasActiveOrders doit tout de même bloquer la suppression.
        $admin = $this->createAdmin();
        $category = $this->createCategory();
        $product = $this->createProduct($category);

        $order = Order::create([
            'full_name'      => 'Client Orphelin',
            'phone'          => '123456789',
            'address'        => 'Rue Alger',
            'wilaya'         => 'Algiers',
            'payment_method' => 'cod',
            'status'         => OrderStatus::Pending,
            'total'          => 5000,
        ]);

        // variant_id = null : simule un order_item dont la variante a été
        // supprimée mais dont le product_id est encore intact.
        OrderItem::create([
            'order_id'     => $order->id,
            'product_id'   => $product->id,
            'variant_id'   => null,
            'quantity'     => 1,
            'price'        => 5000,
            'product_name' => $product->name,
            'variant_size' => 'M',
        ]);

        $response = $this->actingAs($admin)->delete(
            route('admin.products.destroy', $product)
        );

        $response->assertSessionHasErrors('product');

        $this->assertDatabaseHas('products', ['id' => $product->id]);
    }

    // -------------------------------------------------------------------------
    // Note sur les tests de concurrence MySQL
    // -------------------------------------------------------------------------
    //
    // Les tests ci-dessus s'exécutent sous SQLite (base en mémoire) via
    // RefreshDatabase, qui enveloppe chaque test dans une transaction.
    // SQLite ne supporte pas FOR UPDATE. Ces tests vérifient la logique
    // métier (vérification des commandes actives, rollback), mais ne
    // démontrent PAS que les verrous InnoDB fonctionnent sous MySQL.
    //
    // Pour tester la concurrence réelle (deux connexions concurrentes
    // qui se bloquent l'une l'autre sur Product et Variants), il faut
    // un test MySQL dédié sur phpunit.mysql.xml, similaire à
    // CheckoutPessimisticLockingMysqlTest. Ce test est hors périmètre
    // de la tâche 5.3 mais son absence est documentée ici.
    //
    // Risques NON démontrés par les tests présents :
    // - Deux suppressions concurrentes du même produit sans variantes
    //   sous MySQL (sérialisées par le verrou produit : correct en théorie,
    //   non prouvé sous MySQL avec deux connexions PDO réelles).
    // - Race condition entre UpdateProductAction (création de variante) et
    //   DeleteProductAction (vérification hasActiveOrders) sous MySQL.
    // Ces deux cas sont correctement couverts par la logique du code
    // (hiérarchie Product → Variants), mais pas par des tests d'intégration
    // multi-connexions.
}
