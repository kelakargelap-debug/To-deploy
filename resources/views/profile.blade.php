@extends('app')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-8">
        <h2 class="text-display-lg" style="color: var(--md-on-surface);">Profil Saya</h2>
        <p class="text-body-md mt-1" style="color: var(--md-on-surface-variant);">Kelola informasi personal dan pengaturan akun Anda.</p>
    </div>

    {{-- Profile Overview Card --}}
    <div class="card animate-fade-in-up p-6 sm:p-8 mb-6 relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 sm:gap-8">
            {{-- Avatar --}}
            <div class="w-16 h-16 rounded-full flex items-center justify-center text-2xl font-bold shrink-0"
                style="background: var(--md-primary-fixed); color: var(--md-on-primary-fixed-variant);">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>

            <div class="flex-1 text-center sm:text-left">
                <div class="flex flex-col sm:flex-row sm:items-center gap-3 mb-2">
                    <h3 class="text-display-lg" style="color: var(--md-on-surface);">{{ Auth::user()->name }}</h3>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-label-sm self-center"
                        style="background: var(--md-primary-fixed); color: var(--md-on-primary-fixed-variant); border: 1px solid var(--md-primary-fixed-dim);">
                        {{ Auth::user()->membership_tier }} Member
                    </span>
                </div>
                <p class="text-body-lg" style="color: var(--md-on-surface-variant);">{{ Auth::user()->email }}</p>

                <div class="flex flex-wrap justify-center sm:justify-start gap-6 mt-4 pt-4" style="border-top: 1px solid var(--md-outline-variant);">
                    <div>
                        <p class="text-label-sm uppercase tracking-wider mb-1" style="color: var(--md-outline);">Role</p>
                        <p class="text-body-md font-medium" style="color: var(--md-on-surface);">{{ Auth::user()->role }}</p>
                    </div>
                    <div>
                        <p class="text-label-sm uppercase tracking-wider mb-1" style="color: var(--md-outline);">Status</p>
                        <p class="text-body-md font-medium" style="color: var(--md-on-surface);">{{ Auth::user()->membership_status }}</p>
                    </div>
                    <div>
                        <p class="text-label-sm uppercase tracking-wider mb-1" style="color: var(--md-outline);">Berlaku Sampai</p>
                        <p class="text-body-md font-medium" style="color: var(--md-on-surface);">
                            @if(Auth::user()->membership_expiry)
                                {{ \Carbon\Carbon::parse(Auth::user()->membership_expiry)->format('d M Y') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bento Grid Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 animate-fade-in-up">
        {{-- Personal Information --}}
        <div class="lg:col-span-7 card p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4" style="border-bottom: 1px solid var(--md-outline-variant);">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--md-surface-container-low); color: var(--md-primary); border: 1px solid var(--md-outline-variant);">
                    <span class="material-symbols-outlined">person</span>
                </div>
                <h4 class="text-headline-md" style="color: var(--md-on-surface);">Informasi Personal</h4>
            </div>
            @if(session('success'))
                <div class="mb-4 p-4 rounded-lg" style="background: var(--md-primary-container); color: var(--md-on-primary-container);">
                    {{ session('success') }}
                </div>
            @endif
            <form method="POST" action="{{ route('profile') }}" class="space-y-5">
                @csrf
                @method('PUT')
                <div class="space-y-2">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ Auth::user()->name }}" class="input-field">
                </div>
                <div class="space-y-2">
                    <label class="form-label">Email</label>
                    <input type="email" value="{{ Auth::user()->email }}" class="input-field" disabled style="opacity: 0.7; cursor: not-allowed;">
                    <p class="text-label-sm" style="color: var(--md-outline);">Email tidak dapat diubah. Hubungi support.</p>
                </div>
                <div class="space-y-2">
                    <label class="form-label">Nomor HP</label>
                    <input type="text" name="phone" value="{{ Auth::user()->phone }}" class="input-field" placeholder="08xxxxxxxx">
                </div>
                <div class="pt-4 flex justify-end" style="border-top: 1px solid var(--md-outline-variant);">
                    <button type="submit" class="btn-primary">
                        <span class="material-symbols-outlined text-lg">save</span>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        {{-- Account Security --}}
        <div class="lg:col-span-5 card p-6 sm:p-8">
            <div class="flex items-center gap-3 mb-6 pb-4" style="border-bottom: 1px solid var(--md-outline-variant);">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--md-error-container); color: var(--md-error); border: 1px solid var(--md-error-container);">
                    <span class="material-symbols-outlined">lock</span>
                </div>
                <h4 class="text-headline-md" style="color: var(--md-on-surface);">Keamanan Akun</h4>
            </div>
            @if($errors->any())
                <div class="mb-4 p-4 rounded-lg" style="background: var(--md-error-container); color: var(--md-on-error-container);">
                    <ul class="list-disc pl-4">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('change-password') }}" class="space-y-5">
                @csrf
                <div class="space-y-2">
                    <label class="form-label">Password Saat Ini</label>
                    <input type="password" name="current_password" placeholder="••••••••" class="input-field">
                </div>
                <div class="space-y-2">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="new_password" placeholder="••••••••" class="input-field">
                </div>
                <div class="space-y-2">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" placeholder="••••••••" class="input-field">
                </div>
                <div class="pt-4 mt-2">
                    <button type="submit" class="btn-secondary w-full">
                        <span class="material-symbols-outlined text-lg">lock_reset</span>
                        Ubah Password
                    </button>
                </div>
            </form>
            
            <div class="mt-6 pt-6" style="border-top: 1px solid var(--md-outline-variant);">
                <h5 class="text-title-md mb-2" style="color: var(--md-on-surface);">Manajemen Perangkat</h5>
                <p class="text-body-sm mb-4" style="color: var(--md-on-surface-variant);">Lihat riwayat login dan atur perangkat yang dipercaya.</p>
                <a href="{{ route('security.index') }}" class="btn-primary w-full flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">devices</span>
                    Buka Keamanan Akun
                </a>
            </div>
        </div>
    </div>
</div>
@endsection