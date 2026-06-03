<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login a user and create a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($validated)) {
            return response()->json(['error' => 'Email atau password salah.'], 401);
        }

        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return response()->json(['error' => 'Akun Anda telah dinonaktifkan oleh administrator.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

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
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
                'membership_tier' => $user->membership_tier,
                'membership_status' => $user->membership_status,
                'membership_expiry' => $user->membership_expiry ? $user->membership_expiry->toIso8601String() : null,
                'is_active' => $user->is_active,
            ],
            'activeAttempt' => $activeAttemptInfo,
        ]);
    }

    /**
     * Register a new user and create a Sanctum token.
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
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'role' => $user->role,
                'membership_tier' => $user->membership_tier,
                'membership_status' => $user->membership_status,
                'membership_expiry' => $user->membership_expiry ? $user->membership_expiry->toIso8601String() : null,
            ],
        ], 201);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'role' => $user->role,
            'membership_tier' => $user->membership_tier,
            'membership_status' => $user->membership_status,
            'membership_expiry' => $user->membership_expiry ? $user->membership_expiry->toIso8601String() : null,
            'is_active' => $user->is_active,
        ]);
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
}