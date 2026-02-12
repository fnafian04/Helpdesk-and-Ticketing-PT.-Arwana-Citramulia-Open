<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} - Kode Reset Password</title>
</head>

<body
    style="margin: 0; padding: 0; font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f6f9; color: #1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
        style="background-color: #f4f6f9; padding: 40px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border-radius: 16px; box-shadow: 0 20px 60px rgba(214, 40, 40, 0.15); overflow: hidden; max-width: 600px;">

                    {{-- Header with White Background + Logo --}}
                    <tr>
                        <td
                            style="background-color: #ffffff; padding: 40px; text-align: center; border-bottom: 3px solid #d62828;">
                            @if (isset($message))
                                <img src="{{ $message->embed(public_path('images/logo_arwana.png')) }}"
                                    alt="Arwana Logo"
                                    style="width: 80px; height: auto; margin-bottom: 16px; border-radius: 12px;">
                            @elseif (isset($logoUrl))
                                <img src="{{ $logoUrl }}" alt="Arwana Logo"
                                    style="width: 80px; height: auto; margin-bottom: 16px; border-radius: 12px;">
                            @endif
                            <h1
                                style="margin: 0; color: #d62828; font-size: 26px; font-weight: 700; letter-spacing: -0.5px;">
                                Ticketing Helpdesk Arwana
                            </h1>
                            <p
                                style="margin: 8px 0 0 0; color: #b91c1c; font-size: 13px; font-weight: 400; letter-spacing: 0.3px;">
                                PT. Arwana Citramulia Tbk.
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding: 40px;">
                            {{-- Greeting --}}
                            <h2 style="margin: 0 0 8px 0; font-size: 22px; font-weight: 600; color: #1f2937;">
                                Reset Password
                            </h2>

                            @if (!empty($userName))
                                <p style="margin: 0 0 24px 0; font-size: 15px; color: #6b7280;">
                                    Halo {{ $userName }},
                                </p>
                            @endif

                            {{-- Intro Text --}}
                            <p style="margin: 0 0 24px 0; font-size: 15px; line-height: 1.7; color: #4b5563;">
                                Kami menerima permintaan untuk mereset password akun Master Admin Anda.
                                Gunakan kode OTP berikut untuk melanjutkan proses reset password:
                            </p>

                            {{-- OTP Code Box --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding: 0 0 32px 0;">
                                        <div
                                            style="display: inline-block; background: linear-gradient(135deg, #fff5f5 0%, #ffe8e8 100%); border: 2px solid #d62828; border-radius: 16px; padding: 24px 48px; text-align: center;">
                                            <p style="margin: 0 0 8px 0; font-size: 13px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; font-weight: 500;">
                                                Kode Verifikasi
                                            </p>
                                            <p style="margin: 0; font-size: 40px; font-weight: 700; color: #d62828; letter-spacing: 12px; font-family: 'Courier New', monospace;">
                                                {{ $otpCode }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            {{-- Warning Box --}}
                            <div
                                style="background-color: #fffbeb; border: 1px solid #fcd34d; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                                <div style="margin-bottom: 8px; font-size: 14px; color: #92400e;">
                                    <span style="font-size: 16px;">⚠️</span>
                                    <strong>Penting:</strong>
                                </div>
                                <ul style="margin: 0; padding-left: 20px; font-size: 13px; color: #92400e; line-height: 1.8;">
                                    <li>Kode ini berlaku selama <strong>{{ $expirationMinutes }} menit</strong></li>
                                    <li>Jangan berikan kode ini kepada siapapun</li>
                                    <li>Maksimal 5 percobaan verifikasi</li>
                                    <li>Jika Anda tidak meminta reset password, abaikan email ini</li>
                                </ul>
                            </div>

                            {{-- Security Info --}}
                            <div
                                style="background-color: #fff5f5; border: 1px solid #ffcdd2; border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                                <div style="font-size: 14px; color: #475569;">
                                    <span style="color: #d62828; font-size: 16px;">🔒</span>
                                    <span>Fitur ini khusus untuk reset password akun <strong>Master Admin</strong>.
                                        Pastikan Anda mengakses dari perangkat yang aman.</span>
                                </div>
                            </div>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td
                            style="background-color: #fafafa; padding: 24px 40px; border-top: 1px solid #f0f0f0; text-align: center;">
                            <p style="margin: 0 0 4px 0; font-size: 12px; color: #9ca3af;">
                                Email ini dikirim secara otomatis oleh sistem.
                            </p>
                            <p style="margin: 0; font-size: 12px; color: #9ca3af;">
                                &copy; {{ date('Y') }} {{ $appName }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
