<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DeviceRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public string $deviceName) {}

    public function build()
    {
        return $this->subject('Request Perangkat Baru — ' . $this->user->name)
                    ->markdown('emails.device-request');
    }
}