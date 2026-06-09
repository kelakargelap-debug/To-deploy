@extends('app')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4" style="background: var(--md-background);">
        <div class="w-full max-w-[420px] animate-fade-in-up">
            <!-- Branding area -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg"
                    style="background: var(--md-primary); color: var(--md-on-primary);">
                    <span class="material-symbols-outlined text-3xl" data-weight="fill">school</span>
                </div>
                <h1 class="text-display-lg" style="color: var(--md-on-surface);">SKB Tryout</h1>
                <p class="text-body-md mt-1" style="color: var(--md-on-surface-variant);">Platform ujian kompetensi cerdas</p>
            </div>

            {{-- Card --}}
            <div class="card p-6 sm:p-8">
                {{-- Error messages --}}
                @if($errors->any())
                    <div class="mb-5">
                        <x-alert type="danger" :message="$errors->first()" />
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-5">
                        <x-alert type="danger" :message="session('error')" />
                    </div>
                @endif

                {{-- Login Form --}}
                <div id="login-section" class="animate-fade-in-up" style="animation-duration: 0.3s;">
                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf
                        <x-form-field 
                            id="email" 
                            label="Email" 
                            type="email" 
                            placeholder="email@contoh.com" 
                            required="true" 
                            :value="old('email')"
                            autocomplete="email"
                        />
                        
                        <x-form-field 
                            id="password" 
                            label="Password" 
                            type="password" 
                            placeholder="Masukkan password" 
                            required="true" 
                            autocomplete="current-password"
                        />
                        
                        <button type="submit" class="w-full btn-primary mt-2">
                            <span class="material-symbols-outlined text-lg">login</span>
                            Masuk
                        </button>
                    </form>

                    <p class="mt-6 text-center text-body-md" style="color: var(--md-on-surface-variant);">
                        Belum punya akun?
                        <button onclick="toggleForm('register')" class="font-bold hover:underline" style="color: var(--md-primary);">Daftar Sekarang</button>
                    </p>
                </div>

                {{-- Register Form (hidden by default) --}}
                <div id="register-section" class="hidden animate-fade-in-up" style="animation-duration: 0.3s;">
                    <form method="POST" action="{{ route('register') }}" class="space-y-5">
                        @csrf
                        <x-form-field 
                            id="reg-name" 
                            name="name"
                            label="Nama Lengkap" 
                            type="text" 
                            placeholder="Nama lengkap" 
                            required="true" 
                            :value="old('name')"
                            autocomplete="name"
                        />
                        
                        <x-form-field 
                            id="reg-email" 
                            name="email"
                            label="Email" 
                            type="email" 
                            placeholder="email@contoh.com" 
                            required="true" 
                            autocomplete="email"
                        />
                        
                        <x-form-field 
                            id="reg-password" 
                            name="password"
                            label="Password" 
                            type="password" 
                            placeholder="Minimal 5 karakter" 
                            required="true" 
                            minlength="5"
                            autocomplete="new-password"
                        />
                        
                        <button type="submit" class="w-full btn-primary mt-2">
                            <span class="material-symbols-outlined text-lg">person_add</span>
                            Daftar Akun
                        </button>
                    </form>

                    <p class="mt-6 text-center text-body-md" style="color: var(--md-on-surface-variant);">
                        Sudah terdaftar?
                        <button onclick="toggleForm('login')" class="font-bold hover:underline" style="color: var(--md-primary);">Masuk Sekarang</button>
                    </p>
                </div>
            </div>

            {{-- Demo credentials hint --}}
            <p class="text-center mt-6 text-label-sm font-mono" style="color: var(--md-outline);">
                Demo: superadmin@skbtryout.id / Admin@1234
            </p>
        </div>
    </div>

    <style>
        #sidebar {
            display: none !important;
        }

        #main-area {
            margin-left: 0 !important;
        }

        #main-content {
            padding: 0 !important;
        }
    </style>

    <script>
        function toggleForm(form) {
            if (form === 'register') {
                document.getElementById('login-section').classList.add('hidden');
                document.getElementById('register-section').classList.remove('hidden');
            } else {
                document.getElementById('register-section').classList.add('hidden');
                document.getElementById('login-section').classList.remove('hidden');
            }
        }
    </script>
@endsection