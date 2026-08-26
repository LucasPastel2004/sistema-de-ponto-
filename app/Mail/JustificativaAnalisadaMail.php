<?php

namespace App\Mail;

use App\Models\Justificativa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class JustificativaAnalisadaMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $justificativa;

    public function __construct(Justificativa $justificativa)
    {
        $this->justificativa = $justificativa;
    }

    public function envelope(): Envelope
    {
        $statusStr = $this->justificativa->status->value;
        return new Envelope(
            subject: "Sua Justificativa foi {$statusStr}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.justificativa-analisada',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
