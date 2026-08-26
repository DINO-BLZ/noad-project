<?php

namespace App\Mail;

use App\Models\DropWhitelist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WhitelistStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public DropWhitelist $whitelist)
    {
        $this->whitelist->loadMissing('drop', 'user');
    }

    public function envelope(): Envelope
    {
        $approved = $this->whitelist->status === 'approved';

        return new Envelope(
            subject: $approved
                ? 'Votre demande de whitelist a été approuvée — Noad'
                : 'Votre demande de whitelist a été refusée — Noad',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.whitelist.status',
        );
    }
}
