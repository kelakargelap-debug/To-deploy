@extends('app')

@section('content')
    <div class="min-h-screen flex items-center justify-center px-4" style="background: var(--md-background);">
        <div class="w-full max-w-[420px] animate-fade-in-up">
            <!-- Branding area -->
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg"
                    style="background: var(--md-tertiary-container, var(--md-primary)); color: var(--md-on-tertiary-container, var(--md-on-primary));">
                    <span class="material-symbols-outlined text-3xl" data-weight="fill">key</span>
                </div>
                <h1 class="text-display-lg" style="color: var(--md-on-surface);">Backup Codes</h1>
                <p class="text-body-md mt-1" style="color: var(--md-on-surface-variant);">Simpan kode cadangan ini di tempat yang aman</p>
            </div>

            {{-- Card --}}
            <div class="card p-6 sm:p-8">
                {{-- Warning --}}
                <div class="mb-6 p-4 rounded-xl flex gap-3" style="background: var(--md-error-container); color: var(--md-on-error-container);">
                    <span class="material-symbols-outlined shrink-0">warning</span>
                    <div>
                        <p class="text-title-sm font-bold">PENTING! Simpan kode ini sekarang.</p>
                        <p class="text-body-sm mt-1">Kode ini <strong>hanya ditampilkan sekali</strong>. Jika Anda kehilangan akses ke Authenticator, gunakan salah satu kode ini untuk login. Setiap kode hanya bisa digunakan <strong>satu kali</strong>.</p>
                    </div>
                </div>

                {{-- Backup Codes Grid --}}
                <div class="grid grid-cols-2 gap-3 mb-6" id="backup-codes">
                    @foreach($backupCodes as $code)
                    <div class="p-3 rounded-xl text-center font-mono text-body-lg tracking-widest select-all"
                         style="background: var(--md-surface-container); border: 1px solid var(--md-outline-variant); color: var(--md-on-surface);">
                        {{ $code }}
                    </div>
                    @endforeach
                </div>

                {{-- Actions --}}
                <div class="flex gap-3 mb-6">
                    <button type="button" onclick="copyBackupCodes()" class="flex-1 btn-secondary flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg">content_copy</span>
                        Salin Semua
                    </button>
                    <button type="button" onclick="downloadBackupCodes()" class="flex-1 btn-secondary flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg">download</span>
                        Download
                    </button>
                </div>

                {{-- Acknowledge --}}
                <form method="POST" action="{{ route('totp.acknowledge-backup') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="flex items-start gap-3 cursor-pointer">
                            <input type="checkbox" id="confirm-saved" required 
                                   class="mt-1 w-5 h-5 rounded" style="accent-color: var(--md-primary);">
                            <span class="text-body-md" style="color: var(--md-on-surface);">
                                Saya sudah menyimpan backup codes ini di tempat yang aman.
                            </span>
                        </label>
                    </div>
                    <button type="submit" class="w-full btn-primary" id="continue-btn">
                        <span class="material-symbols-outlined text-lg">check_circle</span>
                        Lanjutkan ke Dashboard
                    </button>
                </form>
            </div>
        </div>
    </div>

    <style>
        #sidebar { display: none !important; }
        #main-area { margin-left: 0 !important; }
        #main-content { padding: 0 !important; }
    </style>

    <script>
        function copyBackupCodes() {
            const codes = @json($backupCodes);
            const text = "SKB Tryout - Backup Codes\n" + "=" .repeat(30) + "\n\n" + codes.join("\n") + "\n\nSimpan kode ini di tempat yang aman!\nSetiap kode hanya bisa digunakan satu kali.";
            navigator.clipboard.writeText(text).then(() => {
                alert('Backup codes berhasil disalin!');
            });
        }

        function downloadBackupCodes() {
            const codes = @json($backupCodes);
            const text = "SKB Tryout - Backup Codes\n" + "=".repeat(30) + "\n\n" + codes.join("\n") + "\n\nSimpan kode ini di tempat yang aman!\nSetiap kode hanya bisa digunakan satu kali.\nGenerated: " + new Date().toISOString();
            
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'skb-tryout-backup-codes.txt';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }
    </script>
@endsection
