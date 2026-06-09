<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Kode OTP Anda</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <h2>Halo {{ $userName }},</h2>

    @if($purpose === 'register_verification')
        <p>Terima kasih telah mendaftar di Platform Tryout Online.</p>
        <p>Kode verifikasi akun Anda adalah:</p>
    @elseif($purpose === 'new_device_login')
        <p>Kami mendeteksi login dari perangkat baru.</p>
        @if(!empty($extraData))
        <div style="background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 15px 0;">
            <p style="margin: 0;"><strong>Perangkat:</strong> {{ $extraData['device_name'] ?? 'Unknown' }}</p>
            <p style="margin: 0;"><strong>Browser:</strong> {{ $extraData['browser'] ?? 'Unknown' }}</p>
            <p style="margin: 0;"><strong>IP:</strong> {{ $extraData['ip_address'] ?? 'Unknown' }}</p>
            <p style="margin: 0;"><strong>Waktu:</strong> {{ $extraData['time'] ?? 'Unknown' }}</p>
        </div>
        @endif
        <p>Kode OTP Anda adalah:</p>
    @elseif($purpose === 'logout_all_devices')
        <p>Anda meminta untuk logout dari semua perangkat.</p>
        <p>Kode OTP Anda adalah:</p>
    @else
        <p>Berikut adalah kode OTP Anda:</p>
    @endif

    <div style="text-align: center; margin: 30px 0;">
        <span style="display: inline-block; font-size: 32px; font-weight: bold; letter-spacing: 5px; padding: 15px 30px; background: #004ac6; color: #fff; border-radius: 8px;">
            {{ $otp }}
        </span>
    </div>

    @if($purpose === 'new_device_login' || $purpose === 'logout_all_devices' || $purpose === 'password_reset')
        <p style="color: #666; font-size: 14px;">Kode ini berlaku selama 5 menit.</p>
        <p style="color: #666; font-size: 14px;">Jangan berikan kode ini kepada siapa pun. Jika ini bukan Anda, segera ubah password akun Anda.</p>
    @else
        <p style="color: #666; font-size: 14px;">Kode ini berlaku selama 10 menit.</p>
        <p style="color: #666; font-size: 14px;">Jangan berikan kode ini kepada siapa pun. Jika Anda tidak meminta kode ini, abaikan email ini.</p>
    @endif
</body>
</html>
