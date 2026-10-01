<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_existing_email_returns_success_without_errors(): void
    {
        Mail::fake();
        User::factory()->create(['email' => 'existing@example.com']);

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'existing@example.com'])
            ->assertSessionHas('success', 'Un lien de réinitialisation a été envoyé si cette adresse existe.')
            ->assertSessionHasNoErrors();
    }

    public function test_unknown_email_returns_success_without_errors(): void
    {
        Mail::fake();

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => 'unknown@example.com'])
            ->assertSessionHas('success', 'Un lien de réinitialisation a été envoyé si cette adresse existe.')
            ->assertSessionHasNoErrors();
    }
}
