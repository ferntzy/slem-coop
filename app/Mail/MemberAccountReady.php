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
        public string $username,
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
                'user' => $this->user,
                'username' => $this->username,
                'tempPassword' => $this->tempPassword,
                'fullName' => $this->user->profile?->full_name ?? 'Member',
            ],
        );
    }
}