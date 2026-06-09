<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\User;
use App\Models\VerificationOtp;
use App\Models\LoginHistory;
use App\Mail\OtpMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function showVerifyOtp(Request $request)
    {
        if (!$request->session()->has('otp_email')) {
            return redirect()->route('login');
        }
        return view('auth.verify-otp');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:5',
        ]);

        $user = User::where('email', strtolower($request->email))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->logLoginAttempt($user->id ?? null, $request->email, 'login_failed', 'failed', 'invalid_credentials', $request);
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (in_array($user->status, ['suspended', 'deleted'])) {
            $this->logLoginAttempt($user->id, $request->email, 'login_failed', 'blocked', 'account_' . $user->status, $request);
            throw ValidationException::withMessages([
                'email' => ['Akun Anda telah dinonaktifkan oleh administrator.'],
            ]);
        }

        if ($user->status === 'pending_verification') {
            if ($user->isSuperAdmin()) {
                $user->status = 'active';
                $user->email_verified_at = now();
                $user->save();
            } else {
                $this->logLoginAttempt($user->id, $request->email, 'login_failed', 'blocked', 'unverified', $request);
                $this->generateAndSendOtp($user, 'register_verification', 10);
                $request->session()->put('otp_email', $user->email);
                $request->session()->put('otp_purpose', 'register_verification');
                return redirect()->route('verify-otp')->with('info', 'Akun Anda belum diverifikasi. Kode OTP telah dikirim ulang ke email Anda.');
            }
        }

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

        if ($user->isSuperAdmin()) {
            $isTrusted = true;
        }

        if (!$isTrusted) {
            $this->logLoginAttempt($user->id, $request->email, 'new_device_detected', 'otp_required', null, $request);
            $this->generateAndSendOtp($user, 'new_device_login', 5);
            $request->session()->put('otp_email', $user->email);
            $request->session()->put('otp_purpose', 'new_device_login');
            return redirect()->route('verify-otp')->with('info', 'Kami mendeteksi login dari perangkat baru. Kode OTP telah dikirim ke email Anda.');
        }

        $user->last_login_at = now();
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();
        $this->logLoginAttempt($user->id, $request->email, 'login_success', 'success', null, $request);

        return redirect()->intended(route('dashboard'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:5',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'phone' => $request->phone ?? null,
            'password' => Hash::make($request->password),
            'role' => 'USER',
            'membership_tier' => 'FREE',
            'membership_status' => 'ACTIVE',
            'is_active' => true,
            'status' => 'pending_verification',
        ]);

        $this->generateAndSendOtp($user, 'register_verification', 10);

        $request->session()->put('otp_email', $user->email);
        $request->session()->put('otp_purpose', 'register_verification');

        return redirect()->route('verify-otp')->with('success', 'Registrasi berhasil. Silakan cek email Anda untuk kode verifikasi.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $email = $request->session()->get('otp_email');
        $purpose = $request->session()->get('otp_purpose');

        if (!$email || !$purpose) {
            return redirect()->route('login')->withErrors(['otp' => 'Sesi verifikasi tidak valid.']);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau tidak berlaku.']);
        }

        $otpRecord = VerificationOtp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord || now()->gt($otpRecord->expires_at) || $otpRecord->attempt_count >= 5) {
            return back()->withErrors(['otp' => 'Kode OTP salah atau tidak berlaku.']);
        }

        if (!Hash::check($request->otp, $otpRecord->otp_hash)) {
            $otpRecord->increment('attempt_count');
            return back()->withErrors(['otp' => 'Kode OTP salah atau tidak berlaku.']);
        }

        // OTP is valid
        $otpRecord->used_at = now();
        $otpRecord->save();

        if ($purpose === 'register_verification') {
            $user->status = 'active';
            $user->email_verified_at = now();
            $user->last_login_at = now();
            $user->save();
            
            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->forget(['otp_email', 'otp_purpose']);

            $this->logLoginAttempt($user->id, $user->email, 'login_success', 'success', null, $request);

            return redirect()->route('dashboard')->with('success', 'Akun berhasil diverifikasi.');
        }

        if ($purpose === 'new_device_login') {
            // Check limits
            $trustedCount = \App\Models\TrustedDevice::where('user_id', $user->id)
                ->whereNull('revoked_at')
                ->count();

            if ($trustedCount >= 2) {
                // Auto revoke oldest device for YOLO mode
                $oldestDevice = \App\Models\TrustedDevice::where('user_id', $user->id)
                    ->whereNull('revoked_at')
                    ->orderBy('trusted_at', 'asc')
                    ->first();
                if ($oldestDevice) {
                    $oldestDevice->update(['revoked_at' => now()]);
                    // Revoke its sessions
                    \App\Models\AuthSession::where('trusted_device_id', $oldestDevice->device_id)->update(['revoked_at' => now(), 'revoked_reason' => 'device_removed']);
                }
            }

            // Create new device
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

            $user->last_login_at = now();
            $user->save();

            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->forget(['otp_email', 'otp_purpose']);
            
            $this->logLoginAttempt($user->id, $user->email, 'login_success', 'success', null, $request);

            // Set cookie for 1 year
            return redirect()->route('dashboard')->withCookie(cookie()->forever('device_id', $newDeviceId));
        }

        return redirect()->route('dashboard'); // Default
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function generateAndSendOtp(User $user, string $purpose, int $expiryMinutes = 5)
    {
        VerificationOtp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        VerificationOtp::create([
            'user_id' => $user->id,
            'target_type' => 'email',
            'target_value' => $user->email,
            'otp_hash' => Hash::make($code),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes($expiryMinutes),
            'last_sent_at' => now(),
            'attempt_count' => 0,
            'resend_count' => 0,
        ]);

        try {
            Mail::to($user->email)->send(new OtpMail($code, $user->name, $purpose));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send OTP email to {$user->email}: " . $e->getMessage());
            session()->flash('otp_fallback', "Gagal mengirim email verifikasi. Gunakan kode OTP demo berikut untuk melanjutkan: $code");
        }
    }

    private function logLoginAttempt($userId, $input, $activityType, $status, $failureReason, Request $request)
    {
        LoginHistory::create([
            'user_id' => $userId,
            'email_or_phone_input' => $input,
            'activity_type' => $activityType,
            'status' => $status,
            'failure_reason' => $failureReason,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}