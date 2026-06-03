<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@skbtryout.id',
            'password' => Hash::make('Admin@1234'),
            'role' => 'SUPERADMIN',
            'membership_tier' => 'PREMIUM',
            'membership_status' => 'ACTIVE',
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Budi Santoso',
            'email' => 'member@premium.com',
            'password' => Hash::make('Premium@1234'),
            'role' => 'USER',
            'membership_tier' => 'PREMIUM',
            'membership_status' => 'ACTIVE',
            'membership_expiry' => now()->addYear(),
        ]);

        User::create([
            'name' => 'Ahmad Fauzi',
            'email' => 'member@free.com',
            'password' => Hash::make('Free@1234'),
            'role' => 'USER',
            'membership_tier' => 'FREE',
            'membership_status' => 'ACTIVE',
        ]);
    }
}