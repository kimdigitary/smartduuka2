@extends('emails.layout')

@section('title', 'Reset Password - Smart Duuka')
@section('preheader', 'Use this Smart Duuka code to reset your password.')

@section('content')
    <tr>
        <td align="center" style="padding: 30px 20px 10px 20px;">
            <img src="https://img.icons8.com/ios-filled/100/EA580C/password.png" width="64" height="64" alt="Reset password" style="display: block; margin-bottom: 15px;">
            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #EA580C;">Reset Password</h1>
        </td>
    </tr>
    <tr>
        <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">We received a request to reset your Smart Duuka password. Use this code to continue.</p>
            <div style="background-color: #fff7ed; border-left: 4px solid #EA580C; border-radius: 4px; padding: 20px; margin-bottom: 25px; text-align: center;">
                <p style="margin: 0 0 8px 0; font-size: 12px; color: #666666; font-weight: bold; text-transform: uppercase;">Reset code</p>
                <p style="margin: 0; font-size: 32px; line-height: 38px; color: #EA580C; font-weight: bold; letter-spacing: 6px;">{{ $pin }}</p>
            </div>
            <p style="margin: 0; font-size: 14px; line-height: 22px; color: #666666;">If you did not request a password reset, ignore this email and keep your login details private.</p>
        </td>
    </tr>
@endsection
