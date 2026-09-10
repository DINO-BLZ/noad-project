<?php

namespace App\Mail;

use App\Models\DropWhitelist;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DropOpenedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public DropWhitelist $whitelist)
    {
        $this->whitelist->loadMissing('drop', 'user');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Le drop {$this->whitelist->drop->name} est ouvert — Noad",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.drops.opened',
        );
    }
}
