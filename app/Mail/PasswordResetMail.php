<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $resetUrl;

    public function __construct(public User $user, string $token)
    {
        $this->resetUrl = route('password.reset', [
            'token' => $token,
            'email' => $user->email,
        ]);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Réinitialisation de votre mot de passe — Noad',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.auth.password-reset',
        );
    }
}