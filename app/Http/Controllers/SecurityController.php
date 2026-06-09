<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrustedDevice;
use App\Models\LoginHistory;
use App\Models\AuthSession;
use App\Models\VerificationOtp;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class SecurityController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $trustedDevices = TrustedDevice::where('user_id', $user->id)
            ->whereNull('revoked_at')
            ->orderBy('last_seen_at', 'desc')
            ->get();
            
        $loginHistories = LoginHistory::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        $currentDeviceId = $request->cookie('device_id');

        return view('security.index', compact('trustedDevices', 'loginHistories', 'currentDeviceId'));
    }

    public function revokeDevice(Request $request, $id)
    {
        $user = Auth::user();
        
        $device = TrustedDevice::where('user_id', $user->id)->where('id', $id)->firstOrFail();
        
        $device->update([
            'revoked_at' => now()
        ]);
        
        // Revoke associated sessions
        AuthSession::where('user_id', $user->id)
            ->where('trusted_device_id', $device->device_id)
            ->update([
                'revoked_at' => now(),
                'revoked_reason' => 'device_removed'
            ]);

        LoginHistory::create([
            'user_id' => $user->id,
            'activity_type' => 'device_removed',
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'device_id' => $device->device_id,
        ]);

        return redirect()->back()->with('success', 'Perangkat berhasil dihapus.');
    }

    public function requestLogoutAll(Request $request)
    {
        $user = Auth::user();
        
        VerificationOtp::where('user_id', $user->id)
            ->where('purpose', 'logout_all_devices')
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        VerificationOtp::create([
            'user_id' => $user->id,
            'target_type' => 'email',
            'target_value' => $user->email,
            'otp_hash' => Hash::make($code),
            'purpose' => 'logout_all_devices',
            'expires_at' => now()->addMinutes(5),
            'last_sent_at' => now(),
        ]);

        $mailSent = true;
        try {
            Mail::to($user->email)->send(new OtpMail($code, $user->name, 'logout_all_devices'));
        } catch (\Exception $e) {
            $mailSent = false;
            \Illuminate\Support\Facades\Log::error("Failed to send logout OTP email to {$user->email}: " . $e->getMessage());
        }

        if ($mailSent) {
            return redirect()->back()->with('show_logout_otp', true)->with('info', 'Kode OTP telah dikirim ke email Anda untuk konfirmasi Logout Semua Perangkat.');
        } else {
            return redirect()->back()->with('show_logout_otp', true)->with('info', "Gagal mengirim email verifikasi. Gunakan kode OTP demo berikut untuk konfirmasi: $code");
        }
    }

    public function confirmLogoutAll(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|size:6'
        ]);

        $user = Auth::user();
        
        $otpRecord = VerificationOtp::where('user_id', $user->id)
            ->where('purpose', 'logout_all_devices')
            ->whereNull('used_at')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord || now()->gt($otpRecord->expires_at) || $otpRecord->attempt_count >= 5) {
            return redirect()->back()->with('show_logout_otp', true)->withErrors(['otp' => 'Kode OTP salah atau telah kedaluwarsa.']);
        }

        if (!Hash::check($request->otp, $otpRecord->otp_hash)) {
            $otpRecord->increment('attempt_count');
            return redirect()->back()->with('show_logout_otp', true)->withErrors(['otp' => 'Kode OTP salah atau telah kedaluwarsa.']);
        }

        $otpRecord->used_at = now();
        $otpRecord->save();

        // Revoke all sessions except current
        $currentSessionHash = hash('sha256', $request->session()->getId());
        
        AuthSession::where('user_id', $user->id)
            ->where('session_token_hash', '!=', $currentSessionHash)
            ->update([
                'revoked_at' => now(),
                'revoked_reason' => 'logout_all_devices'
            ]);

        LoginHistory::create([
            'user_id' => $user->id,
            'activity_type' => 'logout_all_devices',
            'status' => 'success',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->back()->with('success', 'Berhasil logout dari semua perangkat lain.');
    }
}
