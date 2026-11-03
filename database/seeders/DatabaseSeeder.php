<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->info(
                'Les seeders sont désactivés en production. '
                .'Utilisez `php artisan app:create-admin {email} {name}` pour créer un administrateur.'
            );

            return;
        }

        $this->call([
            AdminSeeder::class,
            ProductSeeder::class,
        ]);
    }
}
