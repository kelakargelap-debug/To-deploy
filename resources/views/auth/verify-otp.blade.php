@extends('app')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4" style="background: var(--md-background);">
        <div class="w-full max-w-[420px] animate-fade-in-up">
            <!-- Branding area -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg"
                    style="background: var(--md-primary); color: var(--md-on-primary);">
                    <span class="material-symbols-outlined text-3xl" data-weight="fill">verified_user</span>
                </div>
                <h1 class="text-display-lg" style="color: var(--md-on-surface);">Verifikasi OTP</h1>
                <p class="text-body-md mt-1" style="color: var(--md-on-surface-variant);">Masukkan 6 digit kode dari email Anda</p>
            </div>

            {{-- Card --}}
            <div class="card p-6 sm:p-8">
                {{-- Messages --}}
                @if(session('success'))
                    <div class="mb-5">
                        <x-alert type="success" :message="session('success')" />
                    </div>
                @endif
                
                @if(session('info'))
                    <div class="mb-5">
                        <x-alert type="info" :message="session('info')" />
                    </div>
                @endif

                @if(session('otp_fallback'))
                    <div class="mb-5">
                        <x-alert type="warning" :message="session('otp_fallback')" />
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-5">
                        <x-alert type="danger" :message="$errors->first()" />
                    </div>
                @endif

                {{-- OTP Form --}}
                <div id="otp-section" class="animate-fade-in-up" style="animation-duration: 0.3s;">
                    <form method="POST" action="{{ route('verify-otp') }}" class="space-y-5">
                        @csrf
                        
                        <x-form-field 
                            id="otp" 
                            name="otp"
                            label="Kode OTP" 
                            type="text" 
                            placeholder="000000" 
                            required="true" 
                            autofocus="true"
                            maxlength="6"
                            pattern="\d{6}"
                            class="text-center text-2xl tracking-widest font-mono py-3"
                        />
                        
                        <button type="submit" class="w-full btn-primary mt-4">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                            Verifikasi
                        </button>
                    </form>

                    <p class="mt-6 text-center text-body-md" style="color: var(--md-on-surface-variant);">
                        Kembali ke 
                        <a href="{{ route('login') }}" class="font-bold hover:underline" style="color: var(--md-primary);">Halaman Login</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
@endsection
