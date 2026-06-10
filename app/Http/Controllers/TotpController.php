<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

class TotpController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Show the TOTP setup page (QR code + verify).
     */
    public function showSetup(Request $request)
    {
        $user = Auth::user();

        // If already fully setup, redirect
        if ($user->hasTotpEnabled()) {
            return redirect()->route('dashboard')->with('info', 'Authenticator sudah aktif.');
        }

        // Generate secret if not yet stored
        if (empty($user->totp_secret)) {
            $secret = $this->google2fa->generateSecretKey(32);
            $user->totp_secret = $secret;
            $user->save();
        }

        $secret = $user->totp_secret;
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'SKB Tryout'),
            $user->email,
            $secret
        );

        return view('auth.setup-totp', [
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
            'user' => $user,
        ]);
    }

    /**
     * Verify TOTP and activate authenticator.
     */
    public function verifySetup(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if ($user->hasTotpEnabled()) {
            return redirect()->route('dashboard');
        }

        $secret = $user->totp_secret;
        if (!$secret) {
            return redirect()->route('totp.setup')->withErrors(['otp' => 'Silakan generate QR code terlebih dahulu.']);
        }

        $valid = $this->google2fa->verifyKey($secret, $request->otp, 1); // window = 1 (±30s)

        if (!$valid) {
            return back()->withErrors(['otp' => 'Kode OTP salah. Pastikan waktu di perangkat Anda sinkron.']);
        }

        // Activate TOTP
        $user->totp_enabled = true;

        // If user was pending_verification (new register), activate the account
        if ($user->status === 'pending_verification') {
            $user->status = 'active';
            $user->email_verified_at = now();
        }

        $user->save();

        // Generate backup codes
        $backupCodes = $user->generateBackupCodes(8);

        // Store in session so we can show them once
        $request->session()->put('backup_codes', $backupCodes);

        return redirect()->route('totp.backup-codes');
    }

    /**
     * Show backup codes (one-time display after setup).
     */
    public function showBackupCodes(Request $request)
    {
        $backupCodes = $request->session()->get('backup_codes');

        if (!$backupCodes) {
            return redirect()->route('dashboard');
        }

        return view('auth.backup-codes', [
            'backupCodes' => $backupCodes,
        ]);
    }

    /**
     * Acknowledge backup codes have been saved.
     */
    public function acknowledgeBackupCodes(Request $request)
    {
        $request->session()->forget('backup_codes');

        $user = Auth::user();
        $user->last_login_at = now();
        $user->save();

        return redirect()->route('dashboard')->with('success', 'Authenticator berhasil diaktifkan! Akun Anda sekarang lebih aman.');
    }

    /**
     * Show TOTP verification page (during login).
     */
    public function showVerifyLogin(Request $request)
    {
        if (!$request->session()->has('totp_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.verify-totp');
    }

    /**
     * Verify TOTP during login.
     */
    public function verifyLogin(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|min:6|max:8',
        ]);

        $userId = $request->session()->get('totp_user_id');
        if (!$userId) {
            return redirect()->route('login')->withErrors(['email' => 'Sesi verifikasi tidak valid.']);
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->withErrors(['email' => 'Akun tidak ditemukan.']);
        }

        $otp = $request->otp;
        $valid = false;

        // Try regular TOTP first
        if (strlen($otp) === 6 && ctype_digit($otp)) {
            $valid = $this->google2fa->verifyKey($user->totp_secret, $otp, 1);
        }

        // If not valid, try backup code
        if (!$valid) {
            $valid = $user->useBackupCode(strtoupper($otp));
        }

        if (!$valid) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau backup code tidak valid.']);
        }

        // OTP verified - complete login
        $user->last_login_at = now();
        $user->save();

        // Handle device trust
        $deviceId = $request->cookie('device_id');
        $isTrusted = false;

        if ($deviceId) {
            $device = \App\Models\TrustedDevice::where('device_id', $deviceId)
                ->where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->first();
            if ($device) {
                $isTrusted = true;
            }
        }

        // If not trusted, register this device
        $cookie = null;
        if (!$isTrusted) {
            // Check limits
            $trustedCount = \App\Models\TrustedDevice::where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->count();

            if ($trustedCount >= 2) {
                $oldestDevice = \App\Models\TrustedDevice::where('user_id', $user->id)
                    ->whereNull('revoked_at')
                    ->orderBy('trusted_at', 'asc')
                    ->first();
                if ($oldestDevice) {
                    $oldestDevice->update(['revoked_at' => now()]);
                    \App\Models\AuthSession::where('trusted_device_id', $oldestDevice->device_id)
                        ->update(['revoked_at' => now(), 'revoked_reason' => 'device_removed']);
                }
            }

            $newDeviceId = 'device_' . bin2hex(random_bytes(10));
            \App\Models\TrustedDevice::create([
                'user_id' => $user->id,
                'device_id' => $newDeviceId,
                'device_name' => $request->userAgent() ? substr($request->userAgent(), 0, 50) : 'Unknown',
                'browser' => 'Web Browser',
                'os' => 'Unknown',
                'user_agent' => $request->userAgent(),
                'last_ip' => $request->ip(),
                'trusted_at' => now(),
                'last_seen_at' => now(),
            ]);

            $cookie = cookie()->forever('device_id', $newDeviceId);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget(['totp_user_id']);

        \App\Models\LoginHistory::create([
            'user_id' => $user->id,
            'email_or_phone_input' => $user->email,
            'activity_type' => 'login_success',
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $redirect = redirect()->intended(route('dashboard'));
        return $cookie ? $redirect->withCookie($cookie) : $redirect;
    }

    /**
     * Reset TOTP (from security settings).
     */
    public function resetTotp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
            'password' => 'required|string',
        ]);

        $user = Auth::user();

        if (!\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Password salah.']);
        }

        if (!$this->google2fa->verifyKey($user->totp_secret, $request->otp, 1)) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        // Reset TOTP
        $user->totp_secret = null;
        $user->totp_enabled = false;
        $user->backup_codes = null;
        $user->save();

        return redirect()->route('totp.setup')->with('success', 'Authenticator berhasil di-reset. Silakan setup ulang.');
    }

    /**
     * Regenerate backup codes.
     */
    public function regenerateBackupCodes(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = Auth::user();

        if (!$this->google2fa->verifyKey($user->totp_secret, $request->otp, 1)) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        $backupCodes = $user->generateBackupCodes(8);
        $request->session()->put('backup_codes', $backupCodes);

        return redirect()->route('totp.backup-codes');
    }
}
