<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_seeder_creates_admin_user_with_full_permissions(): void
    {
        $this->seed(AdminSeeder::class);

        $admin = User::where('email', env('ADMIN_EMAIL', 'admin@noad.dz'))->first();

        $this->assertNotNull($admin);
        $this->assertTrue((bool) $admin->is_admin);
        $this->assertTrue(Auth::attempt([
            'email' => env('ADMIN_EMAIL', 'admin@noad.dz'),
            'password' => env('ADMIN_PASSWORD', 'Admin2026!Noad'),
        ]));
    }

    public function test_database_seeder_includes_admin_on_db_wipe_and_seed(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', env('ADMIN_EMAIL', 'admin@noad.dz'))->first();

        $this->assertNotNull($admin);
        $this->assertTrue((bool) $admin->is_admin);
    }
}
