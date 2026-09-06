<?php

namespace Tests\Feature;

use App\Models\Drop;
use Tests\TestCase;

class DropWhitelistConcurrencyMysqlTest extends TestCase
{
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

    public function test_drop_row_lock_blocks_second_transaction_during_whitelist_approval(): void
    {
        // Pas de RefreshDatabase ici : même raison que CheckoutPessimisticLockingMysqlTest,
        // il faut que la ligne du drop soit réellement commit pour que les deux connexions
        // PDO brutes ci-dessous se bloquent l'une l'autre comme lors de deux approbations
        // admin réellement simultanées sur Admin/DropController::approveWhitelist.
        $drop = Drop::create([
            'name' => 'Test Drop',
            'slug' => 'mysql-whitelist-lock-'.uniqid(),
            'start_date' => now()->subDay(),
            'end_date' => now()->addDays(3),
            'status' => 'active',
            'max_whitelist_slots' => 1,
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

        try {
            // Simule le lockForUpdate() posé par la première requête d'approbation
            // dans Admin/DropController::approveWhitelist.
            $pdoA->beginTransaction();
            $pdoA->query('SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED');
            $pdoA->query("SELECT * FROM drops WHERE id = {$drop->id} FOR UPDATE");

            $pdoB->beginTransaction();
            $pdoB->query('SET SESSION innodb_lock_wait_timeout = 1');

            try {
                // Une deuxième approbation simultanée sur le même drop doit être bloquée
                // par le verrou de la première, exactement comme deux admins qui
                // cliqueraient "Approuver" au même moment sur la dernière place.
                $pdoB->query("SELECT * FROM drops WHERE id = {$drop->id} FOR UPDATE");
                $this->fail('The second transaction should have been blocked by the row lock.');
            } catch (\PDOException $e) {
                $this->assertTrue(
                    str_contains($e->getMessage(), 'Lock wait timeout exceeded') || str_contains($e->getMessage(), 'Deadlock found'),
                    'Expected a MySQL lock timeout or deadlock when the same drop row is locked by another transaction.'
                );
            }
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

            // Nettoyage manuel : comme on n'utilise pas RefreshDatabase.
            Drop::where('id', $drop->id)->delete();
        }
    }
}
