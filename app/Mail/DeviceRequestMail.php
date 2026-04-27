<?php

namespace App\Mail;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DeviceRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User       $user,
        public UserDevice $device,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PLN Dashboard] Request Perangkat Baru — ' . $this->user->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.device-request',
            with: [
                'user'       => $this->user,
                'device'     => $this->device,
                'approveUrl' => route('admin.devices.approve', $this->device->id),
                'rejectUrl'  => route('admin.devices.reject',  $this->device->id),
                'panelUrl'   => route('admin.devices'),
            ],
        );
    }
}