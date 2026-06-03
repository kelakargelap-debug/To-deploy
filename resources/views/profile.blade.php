@extends('app')

@section('content')
<div class="p-6 max-w-2xl mx-auto">
    <x-page-header title="Profil Saya" subtitle="Informasi akun kamu" />

    <div class="card">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-semibold text-xl">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">{{ Auth::user()->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Role -->
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Role</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    <x-badge :type="Auth::user()->role === 'ADMIN' || Auth::user()->role === 'SUPERADMIN' ? 'info' : 'free'" :text="Auth::user()->role" />
                </p>
            </div>

            <!-- Membership Tier -->
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Membership</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    <x-badge :type="Auth::user()->membership_tier === 'PREMIUM' ? 'premium' : 'free'" :text="Auth::user()->membership_tier" />
                </p>
            </div>

            <!-- Status -->
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    <x-badge :type="Auth::user()->membership_status === 'ACTIVE' ? 'success' : 'danger'" :text="Auth::user()->membership_status" />
                </p>
            </div>

            <!-- Membership Expiry -->
            <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <p class="text-sm text-gray-500 dark:text-gray-400">Berlaku Sampai</p>
                <p class="font-medium text-gray-900 dark:text-gray-100">
                    @if(Auth::user()->membership_expiry)
                        {{ \Carbon\Carbon::parse(Auth::user()->membership_expiry)->format('d M Y') }}
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex gap-3">
            <a href="{{ route('change-password') }}" class="btn-primary text-sm py-2 px-4 rounded-lg">Ubah Password</a>
        </div>
    </div>
</div>
@endsection