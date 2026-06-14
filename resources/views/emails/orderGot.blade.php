@extends('emails.layout')

@section('title', 'New Order Received - Smart Duuka')
@section('preheader', 'A new Smart Duuka order needs attention.')

@section('content')
    <tr>
        <td align="center" style="padding: 30px 20px 10px 20px;">
            <img src="https://img.icons8.com/ios-filled/100/EA580C/online-store.png" width="64" height="64" alt="New order" style="display: block; margin-bottom: 15px;">
            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #EA580C;">New Order Received</h1>
        </td>
    </tr>
    <tr>
        <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">A new order has been placed and needs review.</p>
            <div style="background-color: #fff7ed; border-left: 4px solid #EA580C; border-radius: 4px; padding: 20px; margin-bottom: 25px;">
                <table border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr>
                        <td class="mobile-stack" style="font-size: 14px; color: #666666; padding-bottom: 8px;">Order ID:</td>
                        <td class="mobile-stack" align="right" style="font-size: 14px; color: #333333; font-weight: bold; padding-bottom: 8px;">{{ $orderId }}</td>
                    </tr>
                </table>
            </div>
            <p style="margin: 0; font-size: 16px; line-height: 24px; color: #555555;">{{ $message }}</p>
        </td>
    </tr>
@endsection
