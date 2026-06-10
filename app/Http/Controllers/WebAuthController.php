<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use PragmaRX\Google2FA\Google2FA;

class WebAuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
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

        // If pending verification and TOTP not set up, redirect to setup
        if ($user->status === 'pending_verification') {
            $this->logLoginAttempt($user->id, $request->email, 'login_failed', 'blocked', 'unverified', $request);
            
            // Temporarily login to allow TOTP setup
            Auth::login($user);
            $request->session()->regenerate();
            
            return redirect()->route('totp.setup')->with('info', 'Silakan setup Authenticator untuk mengaktifkan akun Anda.');
        }

        // Check if device is trusted
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

        if ($isTrusted) {
            // Trusted device → direct login (no OTP needed)
            $user->last_login_at = now();
            $user->save();

            Auth::login($user);
            $request->session()->regenerate();
            $this->logLoginAttempt($user->id, $request->email, 'login_success', 'success', null, $request);

            return redirect()->intended(route('dashboard'));
        }

        // Not trusted → require Authenticator OTP
        if (!$user->hasTotpEnabled()) {
            // Edge case: user active but TOTP not set up (shouldn't happen, but handle gracefully)
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->route('totp.setup')->with('info', 'Silakan setup Authenticator terlebih dahulu.');
        }

        $this->logLoginAttempt($user->id, $request->email, 'new_device_detected', 'otp_required', null, $request);

        // Store user ID in session for TOTP verification
        $request->session()->put('totp_user_id', $user->id);

        return redirect()->route('totp.verify-login')->with('info', 'Masukkan kode dari aplikasi Authenticator Anda.');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:5',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => strtolower($request->email),
            'password' => Hash::make($request->password),
            'role' => 'USER',
            'membership_tier' => 'FREE',
            'membership_status' => 'ACTIVE',
            'is_active' => true,
            'status' => 'pending_verification',
        ]);

        // Login the user temporarily so they can setup TOTP
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('totp.setup')->with('success', 'Registrasi berhasil! Silakan setup Authenticator untuk mengamankan akun Anda.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
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