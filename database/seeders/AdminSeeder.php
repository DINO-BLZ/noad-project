<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@noad.dz');
        $name = env('ADMIN_NAME', 'Admin NOAD');
        $password = env('ADMIN_PASSWORD', 'Admin2026!Noad');

        $admin = User::firstOrNew(['email' => $email]);
        $admin->name = $name;
        $admin->password = Hash::make($password);
        $admin->is_admin = true;
        $admin->email_verified_at = now();
        $admin->save();
    }
}
