<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\ContactRequest;
    use App\Mail\ContactFormMail;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Mail;

    class ContactController extends Controller
    {
        /**
         * Human-readable labels for the subject keys sent by the website form.
         */
        private const SUBJECT_LABELS = [
            'sales'       => 'Sales Inquiry & Pricing' ,
            'demo'        => 'Request a Software Demo' ,
            'support'     => 'Technical Support' ,
            'partnership' => 'Partnership / API Integration' ,
        ];

        /**
         * Receive a public contact-form submission and email it to the support team.
         */
        public function store(ContactRequest $request)
        {
            $validated = $request->validated();

            $subjectKey = $validated[ 'subject' ] ?? 'sales';

            $data = [
                'name'         => trim( "{$validated['first_name']} {$validated['last_name']}" ) ,
                'first_name'   => $validated[ 'first_name' ] ,
                'last_name'    => $validated[ 'last_name' ] ,
                'email'        => $validated[ 'email' ] ,
                'phone'        => $validated[ 'phone' ] ,
                'company'      => $validated[ 'company' ] ?? null ,
                'subject'      => $subjectKey ,
                'subjectLabel' => self::SUBJECT_LABELS[ $subjectKey ] ?? 'General Inquiry' ,
                'message'      => $validated[ 'message' ] ,
            ];

            try {
                Mail::to( config( 'mail.support_address' ) )->send( new ContactFormMail( $data ) );
            } catch ( \Throwable $e ) {
                Log::error( 'Contact form email failed: ' . $e->getMessage() , [ 'email' => $data[ 'email' ] ] );

                return response()->json( [
                    'success' => FALSE ,
                    'message' => 'We could not send your message right now. Please try again or email us directly at support@smartduuka.com.' ,
                ] , 500 );
            }

            return response()->json( [
                'success' => TRUE ,
                'message' => 'Thank you! Your message has been received. Our team will get back to you shortly.' ,
            ] );
        }
    }
