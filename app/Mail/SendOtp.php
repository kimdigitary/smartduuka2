<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendOtp extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */

    public string $pin;
    public function __construct($pin)
    {
        $this->pin = $pin;
    }


    public function build() : SendOtp
    {
        return $this->from(config('mail.from.address'), 'Smart Duuka')
                    ->subject("Reset Password")
                    ->view('emails.password');
    }
}
