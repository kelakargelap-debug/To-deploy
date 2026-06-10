@extends('app')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4" style="background: var(--md-background);">
        <div class="w-full max-w-[420px] animate-fade-in-up">
            <!-- Branding area -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg"
                    style="background: var(--md-primary); color: var(--md-on-primary);">
                    <span class="material-symbols-outlined text-3xl" data-weight="fill">security</span>
                </div>
                <h1 class="text-display-lg" style="color: var(--md-on-surface);">Setup Authenticator</h1>
                <p class="text-body-md mt-1" style="color: var(--md-on-surface-variant);">Scan QR code dengan aplikasi Authenticator Anda</p>
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

                @if($errors->any())
                    <div class="mb-5">
                        <x-alert type="danger" :message="$errors->first()" />
                    </div>
                @endif

                {{-- Steps --}}
                <div class="space-y-6">
                    {{-- Step 1: Install Authenticator --}}
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-label-md font-bold"
                            style="background: var(--md-primary); color: var(--md-on-primary);">1</div>
                        <div>
                            <h3 class="text-title-md mb-1" style="color: var(--md-on-surface);">Install Authenticator</h3>
                            <p class="text-body-sm" style="color: var(--md-on-surface-variant);">
                                Download <strong>Google Authenticator</strong> atau <strong>Microsoft Authenticator</strong> dari App Store / Play Store.
                            </p>
                        </div>
                    </div>

                    {{-- Step 2: Scan QR --}}
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-label-md font-bold"
                            style="background: var(--md-primary); color: var(--md-on-primary);">2</div>
                        <div class="flex-1">
                            <h3 class="text-title-md mb-3" style="color: var(--md-on-surface);">Scan QR Code</h3>
                            
                            {{-- QR Code Display --}}
                            <div class="flex justify-center mb-4">
                                <div class="p-4 rounded-2xl" style="background: white;">
                                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ urlencode($qrCodeUrl) }}" 
                                         alt="QR Code" 
                                         width="200" height="200"
                                         class="block"
                                         id="qr-code-img">
                                </div>
                            </div>

                            {{-- Manual entry --}}
                            <details class="mb-2">
                                <summary class="cursor-pointer text-body-sm font-medium" style="color: var(--md-primary);">
                                    Tidak bisa scan? Masukkan kode manual
                                </summary>
                                <div class="mt-3 p-4 rounded-xl" style="background: var(--md-surface-container); border: 1px solid var(--md-outline-variant);">
                                    <p class="text-label-sm mb-2" style="color: var(--md-outline);">Secret Key:</p>
                                    <code class="block text-body-md font-mono break-all select-all p-2 rounded-lg" 
                                          style="background: var(--md-surface-container-high); color: var(--md-on-surface);"
                                          id="totp-secret">{{ $secret }}</code>
                                    <button type="button" onclick="copySecret()" class="mt-2 text-label-sm font-medium" style="color: var(--md-primary);">
                                        <span class="material-symbols-outlined text-sm align-middle">content_copy</span> Salin kode
                                    </button>
                                </div>
                            </details>
                        </div>
                    </div>

                    {{-- Step 3: Verify --}}
                    <div class="flex gap-4">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 text-label-md font-bold"
                            style="background: var(--md-primary); color: var(--md-on-primary);">3</div>
                        <div class="flex-1">
                            <h3 class="text-title-md mb-3" style="color: var(--md-on-surface);">Verifikasi Kode</h3>
                            <form method="POST" action="{{ route('totp.setup') }}" class="space-y-4">
                                @csrf
                                <x-form-field 
                                    id="otp" 
                                    name="otp"
                                    label="Kode dari Authenticator" 
                                    type="text" 
                                    placeholder="000000" 
                                    required="true" 
                                    autofocus="true"
                                    maxlength="6"
                                    pattern="\d{6}"
                                    class="text-center text-2xl tracking-widest font-mono py-3"
                                />
                                
                                <button type="submit" class="w-full btn-primary">
                                    <span class="material-symbols-outlined text-lg">verified_user</span>
                                    Aktifkan Authenticator
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            @if(auth()->user()->status !== 'pending_verification')
            <p class="text-center mt-6 text-body-md" style="color: var(--md-on-surface-variant);">
                <a href="{{ route('dashboard') }}" class="font-bold hover:underline" style="color: var(--md-primary);">Kembali ke Dashboard</a>
            </p>
            @endif
        </div>
    </div>

    <style>
        #sidebar { display: none !important; }
        #main-area { margin-left: 0 !important; }
        #main-content { padding: 0 !important; }
    </style>

    <script>
        function copySecret() {
            const secret = document.getElementById('totp-secret').textContent;
            navigator.clipboard.writeText(secret).then(() => {
                alert('Secret key berhasil disalin!');
            });
        }
    </script>
@endsection
