@extends('emails.layout')

@section('title', 'Verify Email - Smart Duuka')
@section('preheader', 'Your Smart Duuka email verification code is ready.')

@section('content')
    <tr>
        <td align="center" style="padding: 30px 20px 10px 20px;">
            <img src="https://img.icons8.com/ios-filled/100/EA580C/secured-letter.png" width="64" height="64" alt="Verify email" style="display: block; margin-bottom: 15px;">
            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #EA580C;">Verify Email</h1>
        </td>
    </tr>
    <tr>
        <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">Use the code below to verify your email address on Smart Duuka.</p>
            <div style="background-color: #fff7ed; border-left: 4px solid #EA580C; border-radius: 4px; padding: 20px; margin-bottom: 25px; text-align: center;">
                <p style="margin: 0 0 8px 0; font-size: 12px; color: #666666; font-weight: bold; text-transform: uppercase;">One-time code</p>
                <p style="margin: 0; font-size: 32px; line-height: 38px; color: #EA580C; font-weight: bold; letter-spacing: 6px;">{{ $pin }}</p>
            </div>
            <p style="margin: 0; font-size: 14px; line-height: 22px; color: #666666;">Do not share this code with anyone. Smart Duuka support will never ask for it.</p>
        </td>
    </tr>
@endsection
