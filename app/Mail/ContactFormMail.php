<?php

    namespace App\Mail;

    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable;
    use Illuminate\Mail\Mailables\Address;
    use Illuminate\Mail\Mailables\Content;
    use Illuminate\Mail\Mailables\Envelope;
    use Illuminate\Queue\SerializesModels;

    class ContactFormMail extends Mailable
    {
        use Queueable , SerializesModels;

        /**
         * @param array $data Validated contact form payload (name, email, phone, company, subject, subjectLabel, message).
         */
        public function __construct(public array $data) {}

        public function envelope() : Envelope
        {
            $name    = $this->data[ 'name' ] ?? 'Website Visitor';
            $subject = $this->data[ 'subjectLabel' ] ?? 'General Inquiry';

            return new Envelope(
                from: new Address( config( 'mail.from.address' ) , 'Smart Duuka Website' ) ,
                replyTo: [ new Address( $this->data[ 'email' ] , $name ) ] ,
                subject: "New Contact Enquiry: {$subject} — {$name}" ,
            );
        }

        public function content() : Content
        {
            return new Content(
                view: 'emails.contact' ,
                with: [ 'contact' => $this->data ] ,
            );
        }

        public function attachments() : array
        {
            return [];
        }
    }
