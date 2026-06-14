@extends('emails.layout')

@section('title', 'Your Account Credentials - Smart Duuka')
@section('preheader', 'Your Smart Duuka account credentials are ready.')

@section('content')
    <tr>
        <td align="center" style="padding: 30px 20px 10px 20px;">
            <img src="https://img.icons8.com/ios-filled/100/EA580C/key.png" width="64" height="64" alt="Credentials" style="display: block; margin-bottom: 15px;">
            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #EA580C;">Account Credentials</h1>
        </td>
    </tr>
    <tr>
        <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">Hello <strong>{{ $user->name }}</strong>,</p>
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">Your Smart Duuka account has been created or updated. Use the credentials below to sign in.</p>
            <div style="background-color: #fff7ed; border-left: 4px solid #EA580C; border-radius: 4px; padding: 20px; margin-bottom: 25px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td style="padding-bottom: 12px; font-size: 12px; color: #666666; text-transform: uppercase; font-weight: bold;">Email</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 18px; font-size: 16px; color: #333333;">{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 12px; font-size: 12px; color: #666666; text-transform: uppercase; font-weight: bold;">Password</td>
                    </tr>
                    <tr>
                        <td style="padding-bottom: 18px; font-size: 16px; color: #333333; font-family: monospace;">{{ $password }}</td>
                    </tr>
                    @if($pin)
                        <tr>
                            <td style="padding-bottom: 12px; font-size: 12px; color: #666666; text-transform: uppercase; font-weight: bold;">POS PIN</td>
                        </tr>
                        <tr>
                            <td style="font-size: 16px; color: #333333; font-family: monospace;">{{ $pin }}</td>
                        </tr>
                    @endif
                </table>
            </div>
            <p style="margin: 0; font-size: 14px; line-height: 22px; color: #666666;">Please change your password after logging in.</p>
        </td>
    </tr>
@endsection
