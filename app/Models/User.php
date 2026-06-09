<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable(['name', 'email', 'phone', 'password', 'role', 'membership_tier', 'membership_status', 'membership_expiry', 'is_active', 'status', 'phone_verified_at', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'membership_expiry' => 'datetime',
            'is_active' => 'boolean',
            'role' => 'string',
            'membership_status' => 'string',
            'phone_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'status' => 'string',
        ];
    }

    /**
     * A user has many verification otps.
     */
    public function verificationOtps(): HasMany
    {
        return $this->hasMany(VerificationOtp::class);
    }

    /**
     * A user has many login histories.
     */
    public function loginHistories(): HasMany
    {
        return $this->hasMany(LoginHistory::class);
    }

    /**
     * A user has many trusted devices.
     */
    public function trustedDevices(): HasMany
    {
        return $this->hasMany(TrustedDevice::class);
    }

    /**
     * A user has many auth sessions.
     */
    public function authSessions(): HasMany
    {
        return $this->hasMany(AuthSession::class);
    }

    /**
     * A user has many attempt sessions.
     */
    public function attempts(): HasMany
    {
        return $this->hasMany(Attempt::class);
    }

    /**
     * A user has many learning progress records.
     */
    public function learningProgresses(): HasMany
    {
        return $this->hasMany(LearningProgress::class);
    }

    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'ADMIN' || $this->isSuperAdmin();
    }

    /**
     * Check if the user is a super admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'SUPERADMIN';
    }

    /**
     * Check if the user has a premium membership tier.
     */
    public function isPremium(): bool
    {
        return $this->membership_tier === 'PREMIUM';
    }

    /**
     * Check if the user has an active premium membership.
     */
    public function isPremiumActive(): bool
    {
        if (!$this->isPremium()) {
            return false;
        }

        if ($this->membership_status !== 'ACTIVE') {
            return false;
        }

        if ($this->membership_expiry && $this->membership_expiry->isPast()) {
            return false;
        }

        return true;
    }
}