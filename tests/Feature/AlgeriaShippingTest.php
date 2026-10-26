<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Variant;
use App\Services\AlgeriaShippingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlgeriaShippingTest extends TestCase
{
    use RefreshDatabase;

    public function test_shipping_service_provides_all_58_wilayas_and_calculates_fees(): void
    {
        $service = new AlgeriaShippingService;
        $wilayas = $service->all();

        $this->assertCount(58, $wilayas);

        $algerCommunes = $service->getCommunesForWilaya('16 - Alger');
        $this->assertContains('Hydra', $algerCommunes);
        $this->assertContains('Kouba', $algerCommunes);

        $oranCommunes = $service->getCommunesForWilaya('31 - Oran');
        $this->assertContains('Es Senia', $oranCommunes);

        // Alger: 500 DA domicile, 350 DA desk
        $this->assertEquals(500, $service->calculateShippingFee('16 - Alger', 'home'));
        $this->assertEquals(350, $service->calculateShippingFee('16 - Alger', 'desk'));

        // Grand Sud (Adrar, Tamanrasset, etc.): 1350 DA domicile, 900 DA desk
        $this->assertEquals(1350, $service->calculateShippingFee('01 - Adrar', 'home'));
        $this->assertEquals(900, $service->calculateShippingFee('01 - Adrar', 'desk'));
    }

    public function test_checkout_creates_order_with_commune_and_delivery_type(): void
    {
        $user = User::factory()->create();
        $category = Category::create(['name' => 'Streetwear', 'slug' => 'streetwear']);

        $product = Product::create([
            'name' => 'Hoodie Tech',
            'slug' => 'hoodie-tech',
            'price' => 8500.00,
            'category_id' => $category->id,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'L',
            'stock' => 5,
            'sku' => 'HD-TECH-L',
            'color' => 'Noir',
        ]);

        CartItem::create([
            'user_id' => $user->id,
            'session_id' => null,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'full_name' => 'Mounir Larbi',
            'phone' => '0550123456',
            'address' => 'Résidence El Qods, Bât A',
            'wilaya' => '16 - Alger',
            'commune' => 'Hydra',
            'delivery_type' => 'desk',
            'payment_method' => 'cod',
        ]);

        $response->assertRedirect();

        $order = Order::where('full_name', 'Mounir Larbi')->first();
        $this->assertNotNull($order);
        $this->assertEquals('16 - Alger', $order->wilaya);
        $this->assertEquals('Hydra', $order->commune);
        $this->assertEquals('desk', $order->delivery_type);
        $this->assertEquals('Bureau / Stop-Desk', $order->delivery_type_label);
        $this->assertEquals(350.00, (float) $order->shipping_cost);

        $successResponse = $this->get($response->headers->get('Location'));
        $successResponse->assertOk();
        $successResponse->assertSee('Hydra, 16 - Alger');
        $successResponse->assertSee('RÉCUPÉRATION BUREAU (STOP-DESK)');
    }

    public function test_admin_can_export_manifest_for_yalidine_and_zr(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $order = Order::create([
            'full_name' => 'Amine Belkacem',
            'phone' => '0661234567',
            'address' => 'Rue Didouche Mourad 14',
            'wilaya' => '16 - Alger',
            'commune' => 'Alger Centre',
            'delivery_type' => 'home',
            'shipping_cost' => 500,
            'payment_method' => 'cod',
            'status' => OrderStatus::Pending,
            'total' => 12000,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.orders.export-manifest'));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename="yalidine_zr_manifest_', $response->headers->get('Content-Disposition'));

        $content = $response->streamedContent();
        $this->assertStringContainsString('Reference_Commande;Nom_Destinataire;Telephone;Wilaya;Commune;Adresse;Type_Livraison;Montant_COD', $content);
        $this->assertStringContainsString('Amine Belkacem', $content);
        $this->assertStringContainsString('0661234567', $content);
        $this->assertStringContainsString('Alger Centre', $content);
        $this->assertStringContainsString('Domicile', $content);
        $this->assertStringContainsString('12500', $content); // 12000 + 500
    }
}
