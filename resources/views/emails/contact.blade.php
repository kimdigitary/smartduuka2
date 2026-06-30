@extends('emails.layout')

@section('title', 'New Contact Enquiry - Smart Duuka')
@section('preheader')
    New website enquiry from {{ $contact['name'] ?? 'a visitor' }} — {{ $contact['subjectLabel'] ?? 'General Inquiry' }}.
@endsection

@section('content')
    <tr>
        <td align="center" style="padding: 30px 20px 10px 20px;">
            <img src="https://img.icons8.com/ios-filled/100/EA580C/filled-chat.png" width="64" height="64" alt="Enquiry" style="display: block; margin-bottom: 15px;">
            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #EA580C;">New Contact Enquiry</h1>
            <p style="margin: 8px 0 0 0; font-size: 14px; color: #999999;">{{ $contact['subjectLabel'] ?? 'General Inquiry' }}</p>
        </td>
    </tr>
    <tr>
        <td class="mobile-padding" style="padding: 20px 50px 10px 50px;">
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">
                You have received a new message through the <strong>smartduuka.com</strong> contact form.
            </p>

            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #fff7ed; border-left: 4px solid #EA580C; border-radius: 4px; margin-bottom: 25px;">
                <tr>
                    <td style="padding: 20px;">
                        <table border="0" cellpadding="0" cellspacing="0" width="100%">
                            <tr>
                                <td class="mobile-stack" style="font-size: 14px; color: #666666; padding: 6px 0;" width="35%">Name</td>
                                <td class="mobile-stack" style="font-size: 14px; color: #333333; font-weight: bold; padding: 6px 0;">{{ $contact['name'] ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="mobile-stack" style="font-size: 14px; color: #666666; padding: 6px 0;">Email</td>
                                <td class="mobile-stack" style="font-size: 14px; color: #333333; font-weight: bold; padding: 6px 0;">
                                    <a href="mailto:{{ $contact['email'] ?? '' }}" style="color: #EA580C; text-decoration: none;">{{ $contact['email'] ?? '—' }}</a>
                                </td>
                            </tr>
                            <tr>
                                <td class="mobile-stack" style="font-size: 14px; color: #666666; padding: 6px 0;">Phone</td>
                                <td class="mobile-stack" style="font-size: 14px; color: #333333; font-weight: bold; padding: 6px 0;">
                                    <a href="tel:{{ str_replace(' ', '', $contact['phone'] ?? '') }}" style="color: #EA580C; text-decoration: none;">{{ $contact['phone'] ?? '—' }}</a>
                                </td>
                            </tr>
                            @if(!empty($contact['company']))
                            <tr>
                                <td class="mobile-stack" style="font-size: 14px; color: #666666; padding: 6px 0;">Company / Shop</td>
                                <td class="mobile-stack" style="font-size: 14px; color: #333333; font-weight: bold; padding: 6px 0;">{{ $contact['company'] }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="mobile-stack" style="font-size: 14px; color: #666666; padding: 6px 0;">Topic</td>
                                <td class="mobile-stack" style="font-size: 14px; color: #333333; font-weight: bold; padding: 6px 0;">{{ $contact['subjectLabel'] ?? 'General Inquiry' }}</td>
                            </tr>
                            <tr>
                                <td class="mobile-stack" style="font-size: 14px; color: #666666; padding: 6px 0;">Received</td>
                                <td class="mobile-stack" style="font-size: 14px; color: #333333; font-weight: bold; padding: 6px 0;">{{ $contact['submittedAt'] ?? now()->format('d M Y, H:i') . ' EAT' }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <p style="margin: 0 0 8px 0; font-size: 14px; font-weight: bold; color: #333333; text-transform: uppercase; letter-spacing: 0.5px;">Message</p>
            <div style="background-color: #f9fafb; border: 1px solid #eeeeee; border-radius: 6px; padding: 18px;">
                <p style="margin: 0; font-size: 15px; line-height: 24px; color: #444444; white-space: pre-line;">{{ $contact['message'] ?? '' }}</p>
            </div>
        </td>
    </tr>
    <tr>
        <td align="center" style="padding: 10px 50px 35px 50px;">
            <a href="mailto:{{ $contact['email'] ?? '' }}?subject=RE: {{ $contact['subjectLabel'] ?? 'Your enquiry' }}"
               class="button"
               style="display: inline-block; background-color: #EA580C; color: #ffffff; font-size: 15px; font-weight: bold; text-decoration: none; padding: 14px 32px; border-radius: 8px;">
                Reply to {{ $contact['first_name'] ?? 'Visitor' }}
            </a>
        </td>
    </tr>
@endsection
