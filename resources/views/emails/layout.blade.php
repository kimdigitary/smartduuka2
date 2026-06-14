<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>@yield('title', 'Smart Duuka')</title>
    <style>
        body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
        table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
        img { -ms-interpolation-mode: bicubic; border: 0; height: auto; line-height: 100%; outline: none; text-decoration: none; }
        table { border-collapse: collapse !important; }
        body { height: 100% !important; margin: 0 !important; padding: 0 !important; width: 100% !important; font-family: Helvetica, Arial, sans-serif; background-color: #f4f4f4; color: #333333; }
        a[x-apple-data-detectors] { color: inherit !important; text-decoration: none !important; font-size: inherit !important; font-family: inherit !important; font-weight: inherit !important; line-height: inherit !important; }
        @media screen and (max-width: 600px) {
            .wrapper { width: 100% !important; max-width: 100% !important; }
            .mobile-padding { padding: 30px 20px !important; }
            .mobile-stack { display: block !important; width: 100% !important; text-align: left !important; }
        }
        .button:hover { background-color: #c2410c !important; }
    </style>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f4;">
<div style="display: none; max-height: 0; overflow: hidden;">@yield('preheader')</div>
<table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
        <td bgcolor="#f4f4f4" align="center" style="padding: 20px 0 40px 0;">
            <table border="0" cellpadding="0" cellspacing="0" width="600" class="wrapper" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-top: 5px solid #EA580C;">
                <tr>
                    <td bgcolor="#ffffff" align="center" style="padding: 30px 20px 20px 20px; border-bottom: 1px solid #eeeeee;">
                        <img src="{{ asset('logo.png') }}" width="200" height="60" alt="Smart Duuka" style="display: block; border: 0; font-family: Helvetica, Arial, sans-serif; font-weight: bold; font-size: 24px; color: #EA580C;">
                    </td>
                </tr>

                @yield('content')

                @include('emails.partials.footer')
            </table>
        </td>
    </tr>
</table>
</body>
</html>
