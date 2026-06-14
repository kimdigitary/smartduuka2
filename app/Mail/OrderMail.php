<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string|int $orderId,
        public ?string $message
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(config('mail.from.address'), 'Smart Duuka'),
            subject: 'Order Notification',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.order',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
