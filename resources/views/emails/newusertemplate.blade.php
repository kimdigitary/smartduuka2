@extends('emails.layout')

@section('title', 'Welcome to Smart Duuka')
@section('preheader', 'Your Smart Duuka account is ready.')

@section('content')
    <tr>
        <td align="center" style="padding: 30px 20px 10px 20px;">
            <img src="https://img.icons8.com/ios-filled/100/EA580C/user-group-man-man.png" width="64" height="64" alt="Welcome" style="display: block; margin-bottom: 15px;">
            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #EA580C;">Welcome Aboard!</h1>
        </td>
    </tr>
    <tr>
        <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">Hello <strong>{{ $data['name'] ?? 'there' }}</strong>,</p>
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">An account has been created for you at <strong>{{ $data['company_name'] ?? 'your business' }}</strong>. You can now access Smart Duuka to manage sales, inventory, and reports.</p>

            <div style="background-color: #fff7ed; border-left: 4px solid #EA580C; border-radius: 4px; padding: 20px; margin-bottom: 25px;">
                <p style="margin: 0 0 15px 0; font-size: 14px; color: #333333; font-weight: bold;">Your login credentials</p>
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="padding-bottom: 8px; font-size: 12px; color: #666666; text-transform: uppercase; font-weight: bold;">Login Email</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 16px; font-size: 16px; color: #333333;">{{ $data['email'] ?? '' }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 8px; font-size: 12px; color: #666666; text-transform: uppercase; font-weight: bold;">Temporary Password</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 16px; font-size: 16px; color: #333333; font-family: monospace;">{{ $data['password'] ?? '' }}</td>
                    </tr>
                    @if(!empty($data['pin']))
                        <tr>
                            <td style="padding-bottom: 8px; font-size: 12px; color: #666666; text-transform: uppercase; font-weight: bold;">POS PIN</td>
                        </tr>
                        <tr>
                            <td style="font-size: 16px; color: #333333; font-family: monospace;">{{ $data['pin'] }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            @if(!empty($data['login_url']))
                <table border="0" cellspacing="0" cellpadding="0" width="100%" style="margin-bottom: 25px;">
                    <tr>
                        <td align="center">
                            <table border="0" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td align="center" style="border-radius: 50px;" bgcolor="#EA580C">
                                        <a href="{{ $data['login_url'] }}" target="_blank" class="button" style="font-size: 16px; font-family: Helvetica, Arial, sans-serif; color: #ffffff; text-decoration: none; padding: 15px 40px; border-radius: 50px; border: 1px solid #EA580C; display: inline-block; font-weight: bold; box-shadow: 0 4px 6px rgba(234, 88, 12, 0.3);">Login to Smart Duuka</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            @endif

            <p style="margin: 0; font-size: 14px; line-height: 22px; color: #b91c1c; background-color: #fef2f2; border-radius: 4px; padding: 12px;"><strong>Security alert:</strong> Please change your password and PIN immediately after your first login.</p>
        </td>
    </tr>
@endsection
