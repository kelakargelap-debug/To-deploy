<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\User;
use App\Models\VerificationOtp;
use App\Models\LoginHistory;
use App\Mail\OtpMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    private $otpFallbackCode = null;
    /**
     * Login a user and create a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
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

        // If pending verification, they need to verify email
        if ($user->status === 'pending_verification') {
            $this->logLoginAttempt($user->id, $validated['email'], 'login_failed', 'blocked', 'unverified', $request);
            return response()->json(['error' => 'unverified', 'message' => 'Akun belum diverifikasi. Silakan cek email Anda untuk kode OTP.'], 403);
        }

        // TODO: Trusted device and New Device OTP logic will go here in Phase 2
        // For now, just login successfully
        
        $user->last_login_at = now();
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;
        $this->logLoginAttempt($user->id, $validated['email'], 'login_success', 'success', null, $request);

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
     * Register a new user and send OTP.
     */
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|unique:users,phone',
            'password' => 'required|string|min:5',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'USER',
            'membership_tier' => 'FREE',
            'membership_status' => 'ACTIVE',
            'is_active' => true,
            'status' => 'pending_verification',
        ]);

        $this->generateAndSendOtp($user, 'register_verification', 10);

        $responseData = [
            'success' => true,
            'message' => 'Registrasi berhasil. Silakan cek email Anda untuk kode OTP.',
            'email' => $user->email,
        ];

        if ($this->otpFallbackCode) {
            $responseData['otp_fallback'] = $this->otpFallbackCode;
        }

        return response()->json($responseData, 201);
    }

    /**
     * Verify Email OTP
     */
    public function verifyEmailOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
            'purpose' => 'required|string'
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();
        if (!$user) {
            return response()->json(['error' => 'Kode OTP salah atau tidak berlaku.'], 400);
        }

        $otpRecord = VerificationOtp::where('user_id', $user->id)
            ->where('purpose', $validated['purpose'])
            ->whereNull('used_at')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$otpRecord || now()->gt($otpRecord->expires_at) || $otpRecord->attempt_count >= 5) {
            return response()->json(['error' => 'Kode OTP salah atau tidak berlaku.'], 400);
        }

        if (!Hash::check($validated['otp'], $otpRecord->otp_hash)) {
            $otpRecord->increment('attempt_count');
            return response()->json(['error' => 'Kode OTP salah atau tidak berlaku.'], 400);
        }

        // OTP is valid
        $otpRecord->used_at = now();
        $otpRecord->save();

        if ($validated['purpose'] === 'register_verification') {
            $user->status = 'active';
            $user->email_verified_at = now();
            $user->save();
            
            // Auto login user
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json([
                'success' => true,
                'message' => 'Akun berhasil diverifikasi.',
                'token' => $token,
                'user' => $this->formatUser($user),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'OTP diverifikasi.']);
    }

    /**
     * Resend Email OTP
     */
    public function resendEmailOtp(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'purpose' => 'required|string'
        ]);

        $user = User::where('email', strtolower($validated['email']))->first();
        if (!$user) {
            // Silently succeed to prevent email enumeration
            return response()->json(['success' => true]);
        }

        // Check cooldown
        $lastOtp = VerificationOtp::where('user_id', $user->id)
            ->where('purpose', $validated['purpose'])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($lastOtp && $lastOtp->last_sent_at && now()->diffInSeconds($lastOtp->last_sent_at) < 60) {
            return response()->json(['error' => 'Tunggu beberapa detik sebelum meminta kode baru.'], 429);
        }

        // Check resend limits (max 3 times in 15 minutes)
        $resendsLast15m = VerificationOtp::where('user_id', $user->id)
            ->where('purpose', $validated['purpose'])
            ->where('created_at', '>=', now()->subMinutes(15))
            ->count();

        if ($resendsLast15m >= 3) {
            return response()->json(['error' => 'Terlalu banyak permintaan kode OTP. Silakan coba lagi beberapa saat lagi.'], 429);
        }

        $this->generateAndSendOtp($user, $validated['purpose'], $validated['purpose'] === 'register_verification' ? 10 : 5);

        $responseData = ['success' => true, 'message' => 'Kode OTP baru telah dikirim.'];
        if ($this->otpFallbackCode) {
            $responseData['otp_fallback'] = $this->otpFallbackCode;
        }

        return response()->json($responseData);
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
     * Helper to format user for API response
     */
    private function formatUser(User $user)
    {
        return [
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'phone' => $user->phone,
            'role' => $user->role,
            'membership_tier' => $user->membership_tier,
            'membership_status' => $user->membership_status,
            'membership_expiry' => $user->membership_expiry ? $user->membership_expiry->toIso8601String() : null,
            'is_active' => $user->is_active,
            'status' => $user->status,
        ];
    }

    /**
     * Helper to generate and send OTP
     */
    private function generateAndSendOtp(User $user, string $purpose, int $expiryMinutes = 5)
    {
        // Invalidate previous unused OTPs for this purpose
        VerificationOtp::where('user_id', $user->id)
            ->where('purpose', $purpose)
            ->whereNull('used_at')
            ->update(['used_at' => now()]); // mark as used/invalidated

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

        // Send email
        try {
            Mail::to($user->email)->send(new OtpMail($code, $user->name, $purpose));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send OTP email to {$user->email}: " . $e->getMessage());
            $this->otpFallbackCode = $code;
        }
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