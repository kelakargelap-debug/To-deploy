<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TrustedDevice;
use App\Models\LoginHistory;
use App\Models\AuthSession;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FA\Google2FA;

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

    /**
     * Logout from all devices — requires Authenticator OTP confirmation.
     */
    public function requestLogoutAll(Request $request)
    {
        // Just show the OTP input modal
        return redirect()->back()->with('show_logout_otp', true)->with('info', 'Masukkan kode dari aplikasi Authenticator untuk konfirmasi.');
    }

    /**
     * Confirm logout all with Authenticator OTP.
     */
    public function confirmLogoutAll(Request $request)
    {
        $request->validate([
            'otp' => 'required|string|min:6|max:8'
        ]);

        $user = Auth::user();
        $google2fa = new Google2FA();

        $otp = $request->otp;
        $valid = false;

        // Try TOTP
        if (strlen($otp) === 6 && ctype_digit($otp)) {
            $valid = $google2fa->verifyKey($user->totp_secret, $otp, 1);
        }

        // Try backup code
        if (!$valid) {
            $valid = $user->useBackupCode(strtoupper($otp));
        }

        if (!$valid) {
            return redirect()->back()
                ->with('show_logout_otp', true)
                ->withErrors(['otp' => 'Kode OTP salah atau tidak valid.']);
        }

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
