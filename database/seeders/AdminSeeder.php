<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class AdminSeeder extends Seeder
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

        $email = env('ADMIN_EMAIL');
        $name = env('ADMIN_NAME', 'Admin NOAD');
        $password = env('ADMIN_PASSWORD');

        if (
            ! is_string($email)
            || filter_var($email, FILTER_VALIDATE_EMAIL) === false
        ) {
            throw new RuntimeException(
                'La variable ADMIN_EMAIL doit contenir une adresse e-mail valide pour exécuter AdminSeeder.'
            );
        }

        if (
            ! is_string($password)
            || trim($password) === ''
            || strlen($password) < 8
        ) {
            throw new RuntimeException(
                'La variable ADMIN_PASSWORD doit contenir au moins 8 caractères pour exécuter AdminSeeder.'
            );
        }

        if (User::where('email', $email)->exists()) {
            $this->command?->info(
                "Un utilisateur existe déjà avec l'adresse {$email} ; aucune modification effectuée."
            );

            return;
        }

        $admin = new User([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);
        $admin->forceFill([
            'is_admin' => true,
            'email_verified_at' => now(),
        ])->save();
    }
}
