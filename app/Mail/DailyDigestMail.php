<?php

namespace App\Mail;

use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DailyDigestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public CarbonInterface $date,
        public array $sales,
        public array $orders,
        public array $whitelist,
        public array $stock,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rapport quotidien NOAD — '.$this->date->format('d/m/Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.daily-digest',
        );
    }
}