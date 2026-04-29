<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Welcome to SLEM Coop</title>
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
                        <p style="margin:6px 0 0; color:#a8f0c6; font-size:13px; letter-spacing:0.3px;">Member Portal</p>
                    </td>
                </tr>

                {{-- Body --}}
                <tr>
                    <td style="padding:40px;">

                        {{-- Greeting --}}
                        <h2 style="margin:0 0 12px; color:#1a6b3c; font-size:22px; font-weight:700;">
                            Welcome, {{ $fullName }}! 🎉
                        </h2>
                        <p style="margin:0 0 28px; color:#4b5563; font-size:15px; line-height:1.7;">
                            Your SLEM Coop membership account has been <strong style="color:#1a6b3c;">approved and activated</strong>.
                            Click the button below to set up your password and access your member dashboard.
                        </p>

                        {{-- Login Email Box --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="background:#f0faf5; border:1px solid #bbdfc8; border-radius:10px; margin-bottom:28px;">
                            <tr>
                                <td style="padding:20px 24px;">
                                    <p style="margin:0 0 3px; font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:1.2px; font-weight:600;">Your Login Email</p>
                                    <p style="margin:0; font-size:16px; color:#1a6b3c; font-weight:700;">{{ $email }}</p>
                                </td>
                            </tr>
                        </table>

                        {{-- Steps --}}
                        <p style="margin:0 0 14px; color:#111827; font-size:15px; font-weight:700;">Getting started:</p>
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:28px;">
                            @foreach([
                                ['1', 'Click the button below to open your personal registration link.'],
                                ['2', 'Your email is already filled in — just create your password.'],
                                ['3', 'Log in and explore your member dashboard.'],
                            ] as [$step, $text])
                            <tr>
                                <td width="36" valign="top" style="padding-bottom:12px;">
                                    <span style="display:inline-block; width:28px; height:28px; background:#1a6b3c; color:#fff; border-radius:50%; text-align:center; line-height:28px; font-size:13px; font-weight:700;">{{ $step }}</span>
                                </td>
                                <td style="padding-bottom:12px; padding-left:10px; color:#4b5563; font-size:14px; line-height:1.6; vertical-align:middle;">
                                    {{ $text }}
                                </td>
                            </tr>
                            @endforeach
                        </table>

                        {{-- CTA Button --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                            <tr>
                                <td style="text-align:center; padding-bottom:10px;">
                                    <a href="{{ $portalUrl }}"
                                       style="display:inline-block; background:linear-gradient(135deg, #1a6b3c, #27ae60); color:#ffffff; font-size:15px; font-weight:700; text-decoration:none; padding:15px 40px; border-radius:8px; letter-spacing:0.3px; box-shadow:0 4px 12px rgba(26,107,60,0.30);">
                                        Complete My Registration →
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td style="text-align:center;">
                                    <p style="margin:0; font-size:12px; color:#9ca3af;">
                                        Or copy this link:<br>
                                        <a href="{{ $portalUrl }}" style="color:#1a6b3c; word-break:break-all; font-size:11px;">{{ $portalUrl }}</a>
                                    </p>
                                </td>
                            </tr>
                        </table>

                        {{-- Expiry notice --}}
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                            <tr>
                                <td style="background:#fffbeb; border:1px solid #fcd34d; border-radius:8px; padding:14px 18px;">
                                    <p style="margin:0; color:#92400e; font-size:13px; line-height:1.6;">
                                         <strong>This link expires in 7 days.</strong> Please complete your registration as soon as possible.
                                    </p>
                                </td>
                            </tr>
                        </table>

                        {{-- Security notice --}}
                        <p style="margin:0; color:#9ca3af; font-size:12px; line-height:1.7;">
                             SLEM Coop will never ask for your password via email or phone. If you did not apply for this account, please ignore this email or contact our support team immediately.
                        </p>

                    </td>
                </tr>

                {{-- Footer --}}
                <tr>
                    <td style="background:#f8f8f8; padding:24px 40px; text-align:center; border-top:1px solid #eee;">
                        <p style="margin:0 0 4px; color:#1a6b3c; font-size:14px; font-weight:700;">SLEM Coop Management</p>
                        <p style="margin:0 0 6px; font-size:12px;">
                            <a href="https://slemcoop.creativedevlabs.com" style="color:#1a6b3c; text-decoration:none;">slemcoop.creativedevlabs.com</a>
                        </p>
                        <p style="margin:0; color:#d1d5db; font-size:11px;">This is an automated message. Please do not reply to this email.</p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>