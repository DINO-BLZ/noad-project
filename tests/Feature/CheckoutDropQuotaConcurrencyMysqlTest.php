<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Drop;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CheckoutDropQuotaConcurrencyMysqlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'mysql') {
            $this->markTestSkipped(
                'This test is intentionally MySQL-only. Use phpunit.mysql.xml to run it.'
            );
        }

        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped(
                'pdo_mysql is required for this MySQL-specific locking test.'
            );
        }
    }

    public function test_drop_product_lock_serializes_two_quota_reservations(): void
    {
        $category = Category::create([
            'name' => 'Quota Lock Category',
            'slug' => 'quota-lock-category-'.uniqid(),
        ]);

        $drop = Drop::create([
            'name' => 'Quota Lock Drop '.uniqid(),
            'slug' => 'quota-lock-drop-'.uniqid(),
            'start_date' => now()->subHour(),
            'end_date' => now()->addDay(),
        ]);

        $product = Product::create([
            'name' => 'Quota Lock Product',
            'slug' => 'quota-lock-product-'.uniqid(),
            'price' => 25,
            'category_id' => $category->id,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 100,
            'sku' => 'QUOTA-LOCK-'.uniqid(),
            'color' => 'Black',
        ]);

        /*
         * Quota globale du produit :
         * 10.
         */
        $drop->products()->attach($product->id, [
            'quota' => 10,
        ]);

        $pdoA = $this->makePdo();
        $pdoB = $this->makePdo();

        $orderId = null;

        try {
            /*
             * ==========================================================
             * TRANSACTION A
             * ==========================================================
             */
            $pdoA->beginTransaction();

            /*
             * On teste explicitement le comportement sous
             * REPEATABLE READ.
             */
            $pdoA->query(
                'SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ'
            );

            /*
             * A verrouille le quota du produit dans le drop.
             */
            $pdoA->query(
                'SELECT id
                 FROM drop_product
                 WHERE drop_id = '.$drop->id.'
                   AND product_id = '.$product->id.'
                 FOR UPDATE'
            );

            $reservedBefore = (int) $pdoA->query(
                "SELECT COALESCE(SUM(order_items.quantity), 0)
                 FROM order_items
                 INNER JOIN orders
                     ON orders.id = order_items.order_id
                 WHERE order_items.drop_id = {$drop->id}
                   AND order_items.product_id = {$product->id}
                   AND orders.status IN (
                       'pending',
                       'paid',
                       'shipped',
                       'delivered'
                   )"
            )->fetchColumn();

            $this->assertSame(
                0,
                $reservedBefore
            );

            /*
             * ==========================================================
             * TRANSACTION B
             * ==========================================================
             */

            $pdoB->beginTransaction();

            $pdoB->query(
                'SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ'
            );

            /*
             * Pour que le test ne reste pas bloqué indéfiniment.
             */
            $pdoB->query(
                'SET SESSION innodb_lock_wait_timeout = 1'
            );

            try {
                /*
                 * B tente de verrouiller le même quota.
                 *
                 * B doit attendre A.
                 */
                $pdoB->query(
                    'SELECT id
                     FROM drop_product
                     WHERE drop_id = '.$drop->id.'
                       AND product_id = '.$product->id.'
                     FOR UPDATE'
                );

                $this->fail(
                    'The second checkout should wait for the drop_product row lock.'
                );
            } catch (\PDOException $e) {
                $this->assertTrue(
                    str_contains(
                        $e->getMessage(),
                        'Lock wait timeout exceeded'
                    )
                    ||
                    str_contains(
                        $e->getMessage(),
                        'Deadlock found'
                    ),
                    'Expected a MySQL lock timeout or deadlock when the same drop_product row is locked by another transaction.'
                );
            }

            /*
             * ==========================================================
             * A FINALISE SA RÉSERVATION
             * ==========================================================
             */

            $orderId = $this->insertPendingOrder($pdoA);

            $pdoA
                ->prepare(
                    'INSERT INTO order_items
                        (
                            order_id,
                            drop_id,
                            product_id,
                            variant_id,
                            quantity,
                            price,
                            variant_sku,
                            variant_size,
                            variant_color,
                            product_name,
                            created_at,
                            updated_at
                        )
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())'
                )
                ->execute([
                    $orderId,
                    $drop->id,
                    $product->id,
                    $variant->id,
                    6,
                    25,
                    $variant->sku,
                    $variant->size,
                    $variant->color,
                    $product->name,
                ]);

            /*
             * A valide sa transaction.
             */
            $pdoA->commit();

            /*
             * ==========================================================
             * VÉRIFICATION
             * ==========================================================
             */

            $quota = (int) DB::table('drop_product')
                ->where('drop_id', $drop->id)
                ->where('product_id', $product->id)
                ->value('quota');

            $reservedAfter = (int) DB::table('order_items')
                ->join(
                    'orders',
                    'orders.id',
                    '=',
                    'order_items.order_id'
                )
                ->where(
                    'order_items.drop_id',
                    $drop->id
                )
                ->where(
                    'order_items.product_id',
                    $product->id
                )
                ->whereIn(
                    'orders.status',
                    [
                        'pending',
                        'paid',
                        'shipped',
                        'delivered',
                    ]
                )
                ->sum('order_items.quantity');

            /*
             * Quota = 10.
             * Réservé = 6.
             * Reste = 4.
             *
             * Une deuxième commande de 6 doit donc être refusée
             * par CreateOrderAction.
             */
            $this->assertSame(
                10,
                $quota
            );

            $this->assertSame(
                6,
                $reservedAfter
            );

            $this->assertSame(
                4,
                $quota - $reservedAfter
            );
        } finally {
            try {
                $pdoA->rollBack();
            } catch (\Throwable $e) {
                // Transaction déjà commitée ou fermée.
            }

            try {
                $pdoB->rollBack();
            } catch (\Throwable $e) {
                // Transaction déjà fermée après timeout.
            }

            if ($orderId !== null) {
                DB::table('order_items')
                    ->where('order_id', $orderId)
                    ->delete();

                DB::table('orders')
                    ->where('id', $orderId)
                    ->delete();
            }

            $drop->products()->detach();

            Variant::where(
                'id',
                $variant->id
            )->delete();

            Product::where(
                'id',
                $product->id
            )->delete();

            Drop::where(
                'id',
                $drop->id
            )->delete();

            Category::where(
                'id',
                $category->id
            )->delete();
        }
    }

    private function insertPendingOrder(\PDO $pdo): int
    {
        $pdo->prepare(
            'INSERT INTO orders
                (
                    user_id,
                    full_name,
                    phone,
                    address,
                    wilaya,
                    payment_method,
                    status,
                    payment_status,
                    total,
                    checkout_token,
                    created_at,
                    updated_at
                )
             VALUES (
                 NULL,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 ?,
                 NULL,
                 NOW(),
                 NOW()
             )'
        )->execute([
            'Quota Lock Test Order',
            '0555000000',
            'Algiers',
            'Algiers',
            'cod',
            'pending',
            'pending',
            150,
        ]);

        return (int) $pdo->lastInsertId();
    }

    private function makePdo(): \PDO
    {
        return new \PDO(
            sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                env('DB_HOST', '127.0.0.1'),
                env('DB_PORT', '3306'),
                env('DB_DATABASE', 'laravel')
            ),
            env('DB_USERNAME', 'root'),
            env('DB_PASSWORD', ''),
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );
    }
}
