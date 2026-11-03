<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_fails_with_invalid_email(): void
    {
        $this->artisan('app:create-admin', [
            'email' => 'invalid-email',
            'name' => 'Valid Name',
        ])
            ->expectsOutput("L'adresse email fournie n'est pas valide.")
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_if_email_already_exists(): void
    {
        User::factory()->create([
            'email' => 'duplicate@example.test',
        ]);

        $this->artisan('app:create-admin', [
            'email' => 'duplicate@example.test',
            'name' => 'Another Admin',
        ])
            ->expectsOutput("Un compte existe déjà avec l'email duplicate@example.test.")
            ->assertExitCode(1);
    }

    public function test_fails_with_empty_name(): void
    {
        $this->artisan('app:create-admin', [
            'email' => 'admin@example.test',
            'name' => '   ',
        ])
            ->expectsOutput('Le nom est requis.')
            ->assertExitCode(1);
    }

    public function test_fails_when_passwords_do_not_match(): void
    {
        $this->artisan('app:create-admin', [
            'email' => 'admin@example.test',
            'name' => 'Admin Name',
        ])
            ->expectsQuestion('Mot de passe (minimum 8 caractères)', 'secret1234')
            ->expectsQuestion('Confirme le mot de passe', 'differentpassword')
            ->expectsOutput('Les deux mots de passe ne correspondent pas.')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_fails_when_password_is_shorter_than_8_chars(): void
    {
        $this->artisan('app:create-admin', [
            'email' => 'admin@example.test',
            'name' => 'Admin Name',
        ])
            ->expectsQuestion('Mot de passe (minimum 8 caractères)', 'short')
            ->expectsQuestion('Confirme le mot de passe', 'short')
            ->expectsOutput('Le mot de passe doit contenir au moins 8 caractères.')
            ->assertExitCode(1);

        $this->assertDatabaseCount('users', 0);
    }

    public function test_creates_admin_successfully(): void
    {
        $this->artisan('app:create-admin', [
            'email' => 'newadmin@example.test',
            'name' => 'Super Admin',
        ])
            ->expectsQuestion('Mot de passe (minimum 8 caractères)', 'StrongPassword2026!')
            ->expectsQuestion('Confirme le mot de passe', 'StrongPassword2026!')
            ->expectsOutput('Compte administrateur newadmin@example.test créé avec succès.')
            ->assertExitCode(0);

        $admin = User::where('email', 'newadmin@example.test')->first();
        $this->assertNotNull($admin);
        $this->assertSame('Super Admin', $admin->name);
        $this->assertTrue((bool) $admin->is_admin);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('StrongPassword2026!', $admin->password));
    }

    public function test_prompts_interactively_when_arguments_are_missing(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion("Adresse email de l'administrateur", 'interactive@example.test')
            ->expectsQuestion("Nom de l'administrateur", 'Interactive Admin')
            ->expectsQuestion('Mot de passe (minimum 8 caractères)', 'InteractivePass123')
            ->expectsQuestion('Confirme le mot de passe', 'InteractivePass123')
            ->expectsOutput('Compte administrateur interactive@example.test créé avec succès.')
            ->assertExitCode(0);

        $admin = User::where('email', 'interactive@example.test')->first();
        $this->assertNotNull($admin);
        $this->assertSame('Interactive Admin', $admin->name);
        $this->assertTrue((bool) $admin->is_admin);
        $this->assertTrue(Hash::check('InteractivePass123', $admin->password));
    }
}
