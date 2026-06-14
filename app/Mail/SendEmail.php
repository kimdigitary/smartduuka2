<?php

    namespace App\Mail;

    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Mail\Mailables\Address;
    use Illuminate\Mail\Mailables\Content;
    use Illuminate\Mail\Mailables\Envelope;
    use Illuminate\Queue\SerializesModels;

    class SendEmail extends Mailable
    {
        use Queueable , SerializesModels;

        public function __construct(public string $template , public string $subj , public array $data) {}

        public function envelope() : Envelope
        {
            $tenant = tenant( 'id' );
            return new Envelope(
                from: new Address( config( 'mail.from.address' ) , "Smart Duuka ($tenant)" ) ,
                subject: $this->subj ,
            );
        }

        public function content() : Content
        {
            return new Content(
                view: $this->template ,
            );
        }


        public function attachments() : array
        {
            return [];
        }
    }
