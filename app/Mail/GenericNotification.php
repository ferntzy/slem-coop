<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class GenericNotification extends Mailable
{
    public function __construct(
        public string $email,
        public string $subject,
        public string $title,
        public string $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.generic-notification',
            with: [
                'title' => $this->title,
                'message' => $this->message,
            ],
        );
    }
}
