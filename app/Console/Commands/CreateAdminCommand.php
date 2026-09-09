<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin {email} {name}';

    protected $description = 'Crée un compte administrateur.';

    public function handle(): int
    {
        $email = $this->argument('email');
        $name = $this->argument('name');

        $validator = Validator::make(
            ['email' => $email],
            ['email' => 'required|email']
        );

        if ($validator->fails()) {
            $this->error("L'adresse email fournie n'est pas valide.");

            return self::FAILURE;
        }

        if (User::where('email', $email)->exists()) {
            $this->error("Un compte existe déjà avec l'email {$email}.");

            return self::FAILURE;
        }

        $password = $this->secret('Mot de passe (minimum 8 caractères)');
        $passwordConfirm = $this->secret('Confirme le mot de passe');

        if ($password !== $passwordConfirm) {
            $this->error('Les deux mots de passe ne correspondent pas.');

            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Le mot de passe doit contenir au moins 8 caractères.');

            return self::FAILURE;
        }

       $user = User::create([
    'name' => $name,
    'email' => $email,
    'password' => Hash::make($password),
]);

$user->forceFill(['is_admin' => true])->save();

        $this->info("Compte administrateur {$email} créé avec succès.");

        return self::SUCCESS;
    }
}