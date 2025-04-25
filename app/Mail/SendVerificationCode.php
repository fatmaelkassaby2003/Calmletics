<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendVerificationCode extends Mailable
{
    use Queueable, SerializesModels;

    public string $verificationCode;

    public function __construct(string $verificationCode)
    {
        $this->verificationCode = $verificationCode;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Calmletics Verification Code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.verification',
            with: ['verificationCode' => $this->verificationCode]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
