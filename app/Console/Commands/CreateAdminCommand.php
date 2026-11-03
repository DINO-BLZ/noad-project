<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateAdminCommand extends Command
{
    protected $signature = 'app:create-admin {email?} {name?}';

    protected $description = 'Crée un compte administrateur.';

    public function handle(): int
    {
        $email = $this->argument('email') ?? $this->ask("Adresse email de l'administrateur");
        $name = $this->argument('name') ?? $this->ask("Nom de l'administrateur");

        $validator = Validator::make(
            [
                'email' => $email,
                'name' => $name,
            ],
            [
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
                'name' => ['required', 'string', 'max:255'],
            ],
            [
                'email.required' => "L'adresse email est requise.",
                'email.email' => "L'adresse email fournie n'est pas valide.",
                'email.unique' => "Un compte existe déjà avec l'email {$email}.",
                'name.required' => 'Le nom est requis.',
            ]
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $password = $this->secret('Mot de passe (minimum 8 caractères)');
        $passwordConfirm = $this->secret('Confirme le mot de passe');

        if (! is_string($password) || trim($password) === '') {
            $this->error('Le mot de passe ne peut pas être vide.');

            return self::FAILURE;
        }

        if ($password !== $passwordConfirm) {
            $this->error('Les deux mots de passe ne correspondent pas.');

            return self::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('Le mot de passe doit contenir au moins 8 caractères.');

            return self::FAILURE;
        }

        $user = new User([
            'name' => trim((string) $name),
            'email' => trim((string) $email),
            'password' => Hash::make($password),
        ]);

        $user->forceFill([
            'is_admin' => true,
            'email_verified_at' => now(),
        ])->save();

        $this->info("Compte administrateur {$email} créé avec succès.");

        return self::SUCCESS;
    }
}
