<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Login a user — Step 1: Email + Password.
     * Returns either a token (trusted device) or requires TOTP.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'device_id' => 'nullable|string',
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();

        if (!$user) {
            $this->logLoginAttempt(null, $validated['email'], 'login_failed', 'failed', 'user_not_found', $request);
            return response()->json(['error' => 'Email atau password salah.'], 401);
        }

        if (!Hash::check($validated['password'], $user->password)) {
            $this->logLoginAttempt($user->id, $validated['email'], 'login_failed', 'failed', 'wrong_password', $request);
            return response()->json(['error' => 'Email atau password salah.'], 401);
        }

        // Check if account is suspended or deleted
        if (in_array($user->status, ['suspended', 'deleted'])) {
            $this->logLoginAttempt($user->id, $validated['email'], 'login_failed', 'blocked', 'account_' . $user->status, $request);
            return response()->json(['error' => 'Akun Anda telah dinonaktifkan oleh administrator.'], 403);
        }

        // If pending verification, they need to set up TOTP
        if ($user->status === 'pending_verification') {
            if ($user->isSuperAdmin()) {
                $user->status = 'active';
                $user->email_verified_at = now();
                $user->save();
            } else {
                $this->logLoginAttempt($user->id, $validated['email'], 'login_failed', 'blocked', 'unverified', $request);
                return response()->json([
                    'error' => 'unverified',
                    'message' => 'Akun belum diverifikasi. Silakan setup Authenticator.',
                    'requires_totp_setup' => true,
                ], 403);
            }
        }

        // Check trusted device
        $deviceId = $validated['device_id'] ?? null;
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

        if ($isTrusted || $user->isSuperAdmin()) {
            // Direct login
            return $this->completeLogin($user, $request);
        }

        // Require TOTP
        if (!$user->hasTotpEnabled()) {
            return response()->json([
                'error' => 'totp_not_setup',
                'message' => 'Authenticator belum diaktifkan.',
                'requires_totp_setup' => true,
            ], 403);
        }

        $this->logLoginAttempt($user->id, $validated['email'], 'new_device_detected', 'otp_required', null, $request);

        // Generate a temporary token for TOTP verification step
        $tempToken = bin2hex(random_bytes(32));
        cache()->put('totp_verify_' . $tempToken, $user->id, now()->addMinutes(5));

        return response()->json([
            'requires_totp' => true,
            'temp_token' => $tempToken,
            'message' => 'Masukkan kode dari aplikasi Authenticator.',
        ]);
    }

    /**
     * Login Step 2: Verify TOTP code.
     */
    public function verifyTotp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'temp_token' => 'required|string',
            'otp' => 'required|string|min:6|max:8',
        ]);

        $userId = cache()->get('totp_verify_' . $validated['temp_token']);
        if (!$userId) {
            return response()->json(['error' => 'Sesi verifikasi telah berakhir. Silakan login ulang.'], 401);
        }

        $user = User::find($userId);
        if (!$user) {
            return response()->json(['error' => 'Akun tidak ditemukan.'], 404);
        }

        $otp = $validated['otp'];
        $valid = false;

        // Try TOTP
        if (strlen($otp) === 6 && ctype_digit($otp)) {
            $valid = $this->google2fa->verifyKey($user->totp_secret, $otp, 1);
        }

        // Try backup code
        if (!$valid) {
            $valid = $user->useBackupCode(strtoupper($otp));
        }

        if (!$valid) {
            return response()->json(['error' => 'Kode OTP salah atau backup code tidak valid.'], 400);
        }

        // Clear temp token
        cache()->forget('totp_verify_' . $validated['temp_token']);

        return $this->completeLogin($user, $request);
    }

    /**
     * Register a new user.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:5',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'role' => 'USER',
            'membership_tier' => 'FREE',
            'membership_status' => 'ACTIVE',
            'is_active' => true,
            'status' => 'pending_verification',
        ]);

        // Generate TOTP secret
        $secret = $this->google2fa->generateSecretKey(32);
        $user->totp_secret = $secret;
        $user->save();

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'SKB Tryout'),
            $user->email,
            $secret
        );

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Silakan scan QR code dengan Authenticator.',
            'token' => $token,
            'totp_secret' => $secret,
            'qr_code_url' => $qrCodeUrl,
            'requires_totp_setup' => true,
        ], 201);
    }

    /**
     * Setup TOTP for API users.
     */
    public function setupTotp(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasTotpEnabled()) {
            return response()->json(['error' => 'Authenticator sudah aktif.'], 400);
        }

        if (empty($user->totp_secret)) {
            $secret = $this->google2fa->generateSecretKey(32);
            $user->totp_secret = $secret;
            $user->save();
        }

        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name', 'SKB Tryout'),
            $user->email,
            $user->totp_secret
        );

        return response()->json([
            'totp_secret' => $user->totp_secret,
            'qr_code_url' => $qrCodeUrl,
        ]);
    }

    /**
     * Verify TOTP setup for API users.
     */
    public function verifyTotpSetup(Request $request): JsonResponse
    {
        $request->validate([
            'otp' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if ($user->hasTotpEnabled()) {
            return response()->json(['error' => 'Authenticator sudah aktif.'], 400);
        }

        $valid = $this->google2fa->verifyKey($user->totp_secret, $request->otp, 1);

        if (!$valid) {
            return response()->json(['error' => 'Kode OTP salah.'], 400);
        }

        $user->totp_enabled = true;
        if ($user->status === 'pending_verification') {
            $user->status = 'active';
            $user->email_verified_at = now();
        }
        $user->save();

        $backupCodes = $user->generateBackupCodes(8);

        return response()->json([
            'success' => true,
            'message' => 'Authenticator berhasil diaktifkan.',
            'backup_codes' => $backupCodes,
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($this->formatUser($request->user()));
    }

    /**
     * Change the authenticated user's password.
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:5',
        ]);

        $user = $request->user();

        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['error' => 'Password saat ini yang Anda masukkan salah.'], 400);
        }

        $user->update([
            'password' => Hash::make($validated['new_password']),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password Anda berhasil diperbarui.',
        ]);
    }

    /**
     * Logout the authenticated user by deleting the current access token.
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar dari sesi.',
        ]);
    }

    /**
     * Complete login and return token + user data.
     */
    private function completeLogin(User $user, Request $request): JsonResponse
    {
        $user->last_login_at = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;
        $this->logLoginAttempt($user->id, $user->email, 'login_success', 'success', null, $request);

        // Check for active attempt
        $activeAttempt = Attempt::where('user_id', $user->id)
            ->where('status', 'IN_PROGRESS')
            ->where('expires_at', '>', now())
            ->first();

        $activeAttemptInfo = null;
        if ($activeAttempt) {
            $tryout = $activeAttempt->tryout;
            $activeAttemptInfo = [
                'attemptId' => $activeAttempt->id,
                'tryoutSlug' => $tryout ? $tryout->slug : null,
                'tryoutTitle' => $tryout ? $tryout->title : 'Tryout',
                'expiresAt' => $activeAttempt->expires_at->toIso8601String(),
            ];
        }

        return response()->json([
            'token' => $token,
            'user' => $this->formatUser($user),
            'activeAttempt' => $activeAttemptInfo,
        ]);
    }

    /**
     * Helper to format user for API response
     */
    private function formatUser(User $user)
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'membership_tier' => $user->membership_tier,
            'membership_status' => $user->membership_status,
            'membership_expiry' => $user->membership_expiry ? $user->membership_expiry->toIso8601String() : null,
            'is_active' => $user->is_active,
            'status' => $user->status,
            'totp_enabled' => $user->totp_enabled,
        ];
    }

    /**
     * Helper to log login attempts
     */
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