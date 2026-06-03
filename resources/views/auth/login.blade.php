@extends('app')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4" style="background: var(--bg-canvas);">
        <div class="w-full max-w-[360px]">
            {{-- Card --}}
            <div class="rounded-lg p-6 sm:p-8"
                style="background: var(--bg-surface); border: 1px solid var(--border-subtle);">
                {{-- Title --}}
                <h1 class="text-xl font-medium tracking-[-0.025em] text-center mb-5"
                    style="color: var(--text-primary); letter-spacing: -0.025em;">Platform Tryout SKB</h1>

                {{-- Error messages --}}
                @if($errors->any())
                    <div class="mb-4 p-2.5 rounded-md text-[13px]"
                        style="background: var(--danger-subtle); border: 1px solid var(--danger); color: var(--danger);">
                        {{ $errors->first() }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-4 p-2.5 rounded-md text-[13px]"
                        style="background: var(--danger-subtle); border: 1px solid var(--danger); color: var(--danger);">
                        {{ session('error') }}
                    </div>
                @endif

                {{-- Login Form --}}
                <div id="login-section">
                    <p class="text-[13px] mb-4" style="color: var(--text-secondary);">Masuk ke akun Anda untuk melanjutkan.
                    </p>
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="block text-[12px] font-medium mb-1"
                                style="color: var(--text-secondary);">Email</label>
                            <input type="email" name="email" id="email" class="input-field" placeholder="email@contoh.com"
                                required autocomplete="email" value="{{ old('email') }}">
                        </div>
                        <div class="mb-4">
                            <label for="password" class="block text-[12px] font-medium mb-1"
                                style="color: var(--text-secondary);">Password</label>
                            <input type="password" name="password" id="password" class="input-field"
                                placeholder="Masukkan password" required autocomplete="current-password">
                        </div>
                        <button type="submit"
                            class="w-full h-10 rounded-md text-[14px] font-medium transition-opacity hover:opacity-90 disabled:opacity-45"
                            style="background: var(--text-primary); color: var(--text-inverse);">Masuk</button>
                    </form>

                    <p class="mt-4 text-center text-[13px]" style="color: var(--text-secondary);">
                        Belum punya akun?
                        <button onclick="toggleForm('register')" class="font-medium hover:underline"
                            style="color: var(--accent);">Daftar Akun Baru</button>
                    </p>
                </div>

                {{-- Register Form (hidden by default) --}}
                <div id="register-section" class="hidden">
                    <p class="text-[13px] mb-4" style="color: var(--text-secondary);">Buat akun baru untuk memulai.</p>
                    <form method="POST" action="{{ route('register') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="reg-name" class="block text-[12px] font-medium mb-1"
                                style="color: var(--text-secondary);">Nama Lengkap</label>
                            <input type="text" name="name" id="reg-name" class="input-field" placeholder="Nama lengkap"
                                required autocomplete="name" value="{{ old('name') }}">
                        </div>
                        <div class="mb-3">
                            <label for="reg-email" class="block text-[12px] font-medium mb-1"
                                style="color: var(--text-secondary);">Email</label>
                            <input type="email" name="email" id="reg-email" class="input-field"
                                placeholder="email@contoh.com" required autocomplete="email">
                        </div>
                        <div class="mb-4">
                            <label for="reg-password" class="block text-[12px] font-medium mb-1"
                                style="color: var(--text-secondary);">Password</label>
                            <input type="password" name="password" id="reg-password" class="input-field"
                                placeholder="Minimal 5 karakter" required minlength="5" autocomplete="new-password">
                        </div>
                        <button type="submit"
                            class="w-full h-10 rounded-md text-[14px] font-medium transition-opacity hover:opacity-90 disabled:opacity-45"
                            style="background: var(--text-primary); color: var(--text-inverse);">Daftar</button>
                    </form>

                    <p class="mt-4 text-center text-[13px]" style="color: var(--text-secondary);">
                        Sudah terdaftar?
                        <button onclick="toggleForm('login')" class="font-medium hover:underline"
                            style="color: var(--accent);">Masuk Sekarang</button>
                    </p>
                </div>
            </div>

            {{-- Demo credentials hint --}}
            <p class="text-center mt-6 text-[11px]" style="color: var(--text-muted);">
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