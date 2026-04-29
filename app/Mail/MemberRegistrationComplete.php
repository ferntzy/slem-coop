<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MemberRegistrationComplete extends Mailable
{
    public function __construct(
        public User $user
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to SLEM Coop - Your Member Account',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.member-registration-complete',
            with: [
                'fullName' => $this->user->profile?->full_name ?? 'Member',
                'email' => $this->user->profile?->email ?? '',
            ],
        );
    }
}