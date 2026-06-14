@extends('emails.layout')

@section('title', 'Order Invoice - Smart Duuka')
@section('preheader', 'Your invoice is attached for your records.')

@section('content')
    @php
        $customerName = data_get($order, 'user.name', 'there');
        $orderNumber = data_get($order, 'order_serial_no');
    @endphp
    <tr>
        <td align="center" style="padding: 30px 20px 10px 20px;">
            <img src="https://img.icons8.com/ios-filled/100/EA580C/invoice.png" width="64" height="64" alt="Invoice" style="display: block; margin-bottom: 15px;">
            <h1 style="margin: 0; font-size: 24px; font-weight: bold; color: #EA580C;">Order Invoice</h1>
        </td>
    </tr>
    <tr>
        <td class="mobile-padding" style="padding: 20px 50px 30px 50px;">
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">Hello <strong>{{ $customerName }}</strong>,</p>
            <p style="margin: 0 0 20px 0; font-size: 16px; line-height: 24px; color: #555555;">Please find your invoice attached to this email for your records.</p>
            @if($orderNumber)
                <div style="background-color: #fff7ed; border-left: 4px solid #EA580C; border-radius: 4px; padding: 20px; margin-bottom: 25px;">
                    <table border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td class="mobile-stack" style="font-size: 14px; color: #666666; padding-bottom: 8px;">Order ID:</td>
                            <td class="mobile-stack" align="right" style="font-size: 14px; color: #333333; font-weight: bold; padding-bottom: 8px;">{{ $orderNumber }}</td>
                        </tr>
                    </table>
                </div>
            @endif
        </td>
    </tr>
@endsection
