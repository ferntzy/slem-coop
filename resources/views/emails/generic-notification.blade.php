<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{{ $title }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f4f8; font-family: 'Segoe UI', Arial, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f8; padding:48px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 8px 32px rgba(0,0,0,0.10);">

                {{-- Header --}}
                <tr>
                    <td style="background:linear-gradient(135deg, #145c32 0%, #1a6b3c 50%, #27ae60 100%); padding:36px 40px 28px; text-align:center;">
                        <img src="{{ asset('logo.png') }}"
                             alt="SLEM Coop"
                             style="width:72px; height:72px; border-radius:50%; object-fit:contain; background:#fff; padding:6px; margin-bottom:14px; display:block; margin-left:auto; margin-right:auto; box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                        <h1 style="margin:0; color:#ffffff; font-size:24px; font-weight:700; letter-spacing:0.5px;">SLEM Coop</h1>
                        <p style="margin:6px 0 0; color:#a8f0c6; font-size:13px; letter-spacing:0.3px;">Notification</p>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:40px;">

                        {{-- Title --}}
                        <h2 style="margin:0 0 12px; color:#1a6b3c; font-size:22px; font-weight:700;">
                            {{ $title }}
                        </h2>
                        <p style="margin:0 0 28px; color:#4b5563; font-size:15px; line-height:1.7;">
                            {!! nl2br(e($message)) !!}
                        </p>

                        {{-- Footer --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f9fafb; border-radius:8px; margin-top:20px;">
                            <tr>
                                <td style="padding:20px 24px; text-align:center;">
                                    <p style="margin:0; font-size:12px; color:#6b7280;">
                                        This is an automated notification from SLEM Coop.
                                        Please do not reply to this email.
                                    </p>
                                    <p style="margin:8px 0 0; font-size:11px; color:#9ca3af;">
                                        &copy; {{ date('Y') }} SLEM Coop. All rights reserved.
                                    </p>
                                </td>
                            </tr>
                        </table>

                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>