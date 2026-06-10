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

#[Fillable(['name', 'email', 'password', 'role', 'membership_tier', 'membership_status', 'membership_expiry', 'is_active', 'status', 'last_login_at', 'totp_secret', 'totp_enabled', 'backup_codes'])]
#[Hidden(['password', 'remember_token', 'totp_secret', 'backup_codes'])]
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
            'last_login_at' => 'datetime',
            'status' => 'string',
            'totp_enabled' => 'boolean',
            'totp_secret' => 'encrypted',
            'backup_codes' => 'encrypted:array',
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

    /**
     * Check if TOTP is set up for this user.
     */
    public function hasTotpEnabled(): bool
    {
        return $this->totp_enabled && !empty($this->totp_secret);
    }

    /**
     * Get remaining backup codes count.
     */
    public function remainingBackupCodes(): int
    {
        $codes = $this->backup_codes;
        if (!is_array($codes)) {
            return 0;
        }
        return count(array_filter($codes, fn($code) => !($code['used'] ?? false)));
    }

    /**
     * Use a backup code. Returns true if the code was valid.
     */
    public function useBackupCode(string $inputCode): bool
    {
        $codes = $this->backup_codes;
        if (!is_array($codes)) {
            return false;
        }

        foreach ($codes as $key => $code) {
            if (!($code['used'] ?? false) && hash_equals($code['code'], $inputCode)) {
                $codes[$key]['used'] = true;
                $codes[$key]['used_at'] = now()->toIso8601String();
                $this->backup_codes = $codes;
                $this->save();
                return true;
            }
        }

        return false;
    }

    /**
     * Generate new backup codes.
     */
    public function generateBackupCodes(int $count = 8): array
    {
        $codes = [];
        $plainCodes = [];

        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(bin2hex(random_bytes(4))); // 8 char hex codes
            $plainCodes[] = $code;
            $codes[] = [
                'code' => $code,
                'used' => false,
                'used_at' => null,
            ];
        }

        $this->backup_codes = $codes;
        $this->save();

        return $plainCodes;
    }
}