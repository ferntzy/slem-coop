<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MemberAccountReady extends Mailable
{
    public function __construct(
        public User $user,
        public string $tempPassword
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your SLEM Coop Member Account is Ready',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.member-account-ready',
            with: [
                'fullName' => $this->user->profile?->full_name ?? 'Member',
                'email' => $this->user->profile?->email ?? '',
                'tempPassword' => $this->tempPassword,
            ],
        );
    }
}
