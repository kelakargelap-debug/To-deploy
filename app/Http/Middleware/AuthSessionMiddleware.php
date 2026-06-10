<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\AuthSession;
use Illuminate\Support\Facades\Auth;

class AuthSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Enforce TOTP setup for pending_verification status
            if ($user->status === 'pending_verification' && !$request->is('setup-totp', 'backup-codes', 'backup-codes/acknowledge', 'logout')) {
                return redirect()->route('totp.setup')->with('info', 'Silakan setup Authenticator untuk mengaktifkan akun Anda.');
            }

            $tokenHash = hash('sha256', $request->session()->getId());
            $deviceId = $request->cookie('device_id');

            $session = AuthSession::firstOrCreate(
                ['session_token_hash' => $tokenHash],
                [
                    'user_id' => $user->id,
                    'trusted_device_id' => $deviceId,
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'last_seen_at' => now(),
                    'idle_expires_at' => now()->addHours(2),
                    'absolute_expires_at' => now()->addDays(30),
                ]
            );

            // Sync trusted_device_id if it was not set during firstOrCreate
            if ($deviceId && $session->trusted_device_id !== $deviceId) {
                $session->update(['trusted_device_id' => $deviceId]);
            }

            // Check if revoked
            if ($session->revoked_at) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['email' => 'Sesi Anda telah dicabut (Revoked).']);
            }

            // Check if idle expired
            if (now()->gt($session->idle_expires_at) || now()->gt($session->absolute_expires_at)) {
                $session->update(['revoked_at' => now(), 'revoked_reason' => 'expired']);
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors(['email' => 'Sesi Anda telah berakhir. Silakan login kembali.']);
            }

            $session->update([
                'last_seen_at' => now(),
                'idle_expires_at' => now()->addHours(2),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        return $next($request);
    }
}
