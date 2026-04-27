<?php

namespace App\Mail;

use App\Models\UserDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeviceApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public UserDevice $device,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PLN Dashboard] Perangkat Anda Disetujui ✓',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.device-approved',
            with: [
                'user'     => $this->device->user,
                'device'   => $this->device,
                'loginUrl' => route('login'),
            ],
        );
    }
}