<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use RuntimeException;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    private array $originalAdminEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['ADMIN_EMAIL', 'ADMIN_NAME', 'ADMIN_PASSWORD'] as $key) {
            $this->originalAdminEnvironment[$key] = [
                'process' => getenv($key),
                'env_exists' => array_key_exists($key, $_ENV),
                'env' => $_ENV[$key] ?? null,
                'server_exists' => array_key_exists($key, $_SERVER),
                'server' => $_SERVER[$key] ?? null,
            ];
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->originalAdminEnvironment as $key => $values) {
            if ($values['process'] === false) {
                putenv($key);
            } else {
                putenv($key.'='.$values['process']);
            }

            if ($values['env_exists']) {
                $_ENV[$key] = $values['env'];
            } else {
                unset($_ENV[$key]);
            }

            if ($values['server_exists']) {
                $_SERVER[$key] = $values['server'];
            } else {
                unset($_SERVER[$key]);
            }
        }

        parent::tearDown();
    }

    public function test_admin_seeder_creates_admin_user_with_full_permissions(): void
    {
        $this->setAdminEnvironment(
            'admin-seeder@example.test',
            'explicit-test-admin-password'
        );

        $this->seed(AdminSeeder::class);

        $admin = User::where('email', 'admin-seeder@example.test')->first();

        $this->assertNotNull($admin);
        $this->assertTrue((bool) $admin->is_admin);
        $this->assertTrue(Hash::check('explicit-test-admin-password', $admin->password));
    }

    public function test_database_seeder_includes_admin_on_db_wipe_and_seed(): void
    {
        $this->setAdminEnvironment(
            'database-seeder@example.test',
            'explicit-test-admin-password'
        );

        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'database-seeder@example.test')->first();

        $this->assertNotNull($admin);
        $this->assertTrue((bool) $admin->is_admin);
        $this->assertDatabaseHas('products', ['slug' => 'noad-tee-edition-01']);
    }

    public function test_admin_seeder_does_nothing_in_production(): void
    {
        $this->setAdminEnvironment(
            'production-admin@example.test',
            'explicit-test-admin-password'
        );
        $this->setProductionEnvironment();

        $this->artisan('db:seed', [
            '--class' => AdminSeeder::class,
            '--force' => true,
        ])
            ->expectsOutputToContain('php artisan app:create-admin')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_product_seeder_does_nothing_in_production(): void
    {
        $this->setAdminEnvironment(
            'production-admin@example.test',
            'explicit-test-admin-password'
        );
        $this->setProductionEnvironment();

        $this->artisan('db:seed', [
            '--class' => ProductSeeder::class,
            '--force' => true,
        ])
            ->expectsOutputToContain('php artisan app:create-admin')
            ->assertExitCode(0);

        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_admin_seeder_fails_when_password_is_not_provided(): void
    {
        $this->setAdminEnvironment('missing-password@example.test', null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ADMIN_PASSWORD');

        app(AdminSeeder::class)->run();
    }

    public function test_admin_seeder_fails_when_email_is_not_provided(): void
    {
        $this->setAdminEnvironment(null, 'explicit-test-admin-password');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('ADMIN_EMAIL');

        app(AdminSeeder::class)->run();
    }

    public function test_admin_seeder_fails_when_email_is_invalid(): void
    {
        $this->setAdminEnvironment('invalid-email', 'explicit-test-admin-password');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('adresse e-mail valide');

        app(AdminSeeder::class)->run();
    }

    public function test_admin_seeder_fails_when_password_is_too_short(): void
    {
        $this->setAdminEnvironment('short-password@example.test', 'short');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('au moins 8 caractères');

        app(AdminSeeder::class)->run();
    }

    public function test_admin_seeder_does_not_overwrite_an_existing_user(): void
    {
        $this->setAdminEnvironment(
            'existing-user@example.test',
            'new-test-admin-password',
            'New Admin Name'
        );

        $existingUser = User::factory()->create([
            'email' => 'existing-user@example.test',
            'name' => 'Existing User',
            'password' => 'existing-user-password',
            'is_admin' => false,
        ]);
        $originalPasswordHash = $existingUser->password;

        $this->seed(AdminSeeder::class);

        $existingUser->refresh();
        $this->assertSame('Existing User', $existingUser->name);
        $this->assertSame($originalPasswordHash, $existingUser->password);
        $this->assertFalse((bool) $existingUser->is_admin);
    }

    public function test_admin_seeder_does_not_modify_existing_admin_password(): void
    {
        $this->setAdminEnvironment(
            'admin@example.test',
            'new-seeder-password',
            'New Admin Name'
        );

        $existingAdmin = User::factory()->create([
            'email' => 'admin@example.test',
            'name' => 'Existing Admin',
            'password' => Hash::make('original-admin-password'),
            'is_admin' => true,
        ]);
        $originalPasswordHash = $existingAdmin->password;

        $this->seed(AdminSeeder::class);

        $existingAdmin->refresh();
        $this->assertSame('Existing Admin', $existingAdmin->name);
        $this->assertSame($originalPasswordHash, $existingAdmin->password);
        $this->assertTrue((bool) $existingAdmin->is_admin);
        $this->assertTrue(Hash::check('original-admin-password', $existingAdmin->password));
        $this->assertFalse(Hash::check('new-seeder-password', $existingAdmin->password));
    }

    public function test_database_seeder_creates_no_demo_data_in_production(): void
    {
        $this->setAdminEnvironment(
            'production-admin@example.test',
            'explicit-test-admin-password'
        );
        $this->setProductionEnvironment();

        $this->artisan('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ])
            ->expectsOutputToContain('php artisan app:create-admin')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_product_seeder_is_idempotent(): void
    {
        app(ProductSeeder::class)->run();
        app(ProductSeeder::class)->run();

        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('variants', 4);
    }

    private function setAdminEnvironment(
        ?string $email,
        ?string $password,
        string $name = 'Admin NOAD'
    ): void {
        $this->setEnvironmentVariable('ADMIN_EMAIL', $email);
        $this->setEnvironmentVariable('ADMIN_PASSWORD', $password);
        $this->setEnvironmentVariable('ADMIN_NAME', $name);
    }

    private function setProductionEnvironment(): void
    {
        $this->app->detectEnvironment(static fn (): string => 'production');
    }

    private function setEnvironmentVariable(string $key, ?string $value): void
    {
        if ($value === null) {
            putenv($key);
            unset($_ENV[$key], $_SERVER[$key]);

            return;
        }

        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
