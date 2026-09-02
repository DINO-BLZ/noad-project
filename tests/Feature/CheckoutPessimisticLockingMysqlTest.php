<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPessimisticLockingMysqlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        if (config('database.default') !== 'mysql') {
            $this->markTestSkipped('This test is intentionally MySQL-only. Use phpunit.mysql.xml to run it.');
        }

        if (! extension_loaded('pdo_mysql')) {
            $this->markTestSkipped('pdo_mysql is required for this MySQL-specific locking test.');
        }
    }

    public function test_variant_row_lock_blocks_second_transaction(): void
    {
        $category = Category::create(['name' => 'Test', 'slug' => 'mysql-lock']);

        $product = Product::create([
            'name' => 'T-shirt',
            'slug' => 'tshirt-mysql-lock',
            'price' => 25.00,
            'category_id' => $category->id,
        ]);

        $variant = Variant::create([
            'product_id' => $product->id,
            'size' => 'M',
            'stock' => 1,
            'sku' => 'SKU-MYSQL-LOCK',
            'color' => 'Blue',
        ]);

        $pdoA = new \PDO(
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

        $pdoB = new \PDO(
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

        $pdoA->beginTransaction();
        $pdoA->query('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $pdoA->query("SELECT * FROM variants WHERE id = {$variant->id} FOR UPDATE");

        $pdoB->beginTransaction();
        $pdoB->query('SET SESSION innodb_lock_wait_timeout = 1');

        try {
            $pdoB->query("SELECT * FROM variants WHERE id = {$variant->id} FOR UPDATE");
            $this->fail('The second transaction should have been blocked by the row lock.');
        } catch (\PDOException $e) {
            $this->assertTrue(
                str_contains($e->getMessage(), 'Lock wait timeout exceeded') || str_contains($e->getMessage(), 'Deadlock found'),
                'Expected a MySQL lock timeout or deadlock when the same row is locked by another transaction.'
            );
        } finally {
            try {
                $pdoA->rollBack();
            } catch (\Throwable $e) {
                // no-op: transaction may already be closed by the DB.
            }

            try {
                $pdoB->rollBack();
            } catch (\Throwable $e) {
                // no-op: transaction may already be closed by the DB.
            }
        }
    }
}
