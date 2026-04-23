<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Your SLEM Coop Account is Ready</title>
</head>
<body style="margin:0; padding:0; background-color:#f0f4f8; font-family: 'Segoe UI', Arial, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f8; padding: 40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.08);">

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #1a6b3c, #27ae60); padding: 40px 40px 30px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:26px; font-weight:700; letter-spacing:0.5px;">
                                SLEM Coop
                            </h1>
                            <p style="margin:8px 0 0; color:#a8f0c6; font-size:14px;">Member Portal</p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 40px;">

                            <h2 style="margin:0 0 8px; color:#1a6b3c; font-size:22px;">
                                Welcome, {{ $fullName }}! 🎉
                            </h2>
                            <p style="margin:0 0 24px; color:#555; font-size:15px; line-height:1.6;">
                                Your SLEM Coop member account has been <strong>approved and activated</strong>.
                                You can now log in to the member portal using your Gmail address and the temporary password below.
                            </p>

                            {{-- Credentials Box --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fffe; border:1px solid #c3e6cb; border-radius:8px; margin-bottom:24px;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <p style="margin:0 0 4px; font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px;">Login Email</p>
                                        <p style="margin:0 0 20px; font-size:16px; color:#1a6b3c; font-weight:600;">{{ $email }}</p>

                                        <p style="margin:0 0 4px; font-size:12px; color:#888; text-transform:uppercase; letter-spacing:1px;">Temporary Password</p>
                                        <p style="margin:0; font-size:20px; color:#1a1a1a; font-weight:700; letter-spacing:2px; font-family: 'Courier New', monospace; background:#eafaf1; display:inline-block; padding:8px 16px; border-radius:6px;">
                                            {{ $tempPassword }}
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Warning --}}
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#fff8e1; border-left:4px solid #f59e0b; border-radius:4px; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:14px 18px;">
                                        <p style="margin:0; color:#92400e; font-size:14px; line-height:1.5;">
                                            ⚠️ <strong>Important:</strong> Please change your password immediately after your first login for security purposes.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            {{-- Steps --}}
                            <p style="margin:0 0 12px; color:#333; font-size:15px; font-weight:600;">Getting started:</p>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;">
                                @foreach([
                                    ['1', 'Go to the SLEM Coop Member Portal'],
                                    ['2', 'Log in using your Gmail address and temporary password'],
                                    ['3', 'Change your password immediately after logging in'],
                                ] as [$step, $text])
                                <tr>
                                    <td width="36" valign="top" style="padding-bottom:10px;">
                                        <span style="display:inline-block; width:26px; height:26px; background:#1a6b3c; color:#fff; border-radius:50%; text-align:center; line-height:26px; font-size:13px; font-weight:700;">{{ $step }}</span>
                                    </td>
                                    <td style="padding-bottom:10px; padding-left:8px; color:#555; font-size:14px; line-height:1.5; vertical-align:middle;">
                                        {{ $text }}
                                    </td>
                                </tr>
                                @endforeach
                            </table>

                            <p style="margin:0; color:#888; font-size:13px; line-height:1.6;">
                                If you did not apply for this account or believe this is an error, please contact our support team immediately.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8f8f8; padding:24px 40px; text-align:center; border-top:1px solid #eee;">
                            <p style="margin:0 0 4px; color:#1a6b3c; font-size:14px; font-weight:600;">SLEM Coop Management</p>
                            <p style="margin:0; color:#aaa; font-size:12px;">This is an automated message. Please do not reply to this email.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>