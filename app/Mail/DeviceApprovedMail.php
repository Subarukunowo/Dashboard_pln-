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

    public $device;

    public function __construct(UserDevice $device)
    {
        $this->device = $device;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Perangkat Anda Telah Disetujui',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.device_approved',
            with: [
                'userName' => $this->device->user->name,
                'deviceName' => $this->device->device_name,
            ],
        );
    }
}