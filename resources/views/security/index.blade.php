@extends('app')

@section('content')
<div class="max-w-5xl mx-auto pb-10">
    <div class="mb-8 flex items-center gap-3">
        <a href="{{ route('profile') }}" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-[var(--md-surface-variant)] transition-colors" style="color: var(--md-on-surface);">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h2 class="text-display-lg" style="color: var(--md-on-surface);">Keamanan Akun</h2>
            <p class="text-body-md mt-1" style="color: var(--md-on-surface-variant);">Kelola perangkat Anda dan pantau aktivitas login.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif
    
    @if(session('info'))
        <div class="mb-6">
            <x-alert type="info" :message="session('info')" />
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6">
            <x-alert type="danger" :message="$errors->first()" />
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in-up">
        
        {{-- Perangkat Saya --}}
        <div class="card p-6 sm:p-8 flex flex-col">
            <div class="flex items-center gap-3 mb-6 pb-4" style="border-bottom: 1px solid var(--md-outline-variant);">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--md-primary-container); color: var(--md-on-primary-container); border: 1px solid var(--md-primary-container);">
                    <span class="material-symbols-outlined">devices</span>
                </div>
                <h4 class="text-headline-md" style="color: var(--md-on-surface);">Perangkat Saya</h4>
            </div>

            <div class="space-y-4 flex-1">
                @forelse($trustedDevices as $device)
                <div class="p-4 rounded-xl flex items-center justify-between" style="border: 1px solid var(--md-outline-variant); background: {{ $device->device_id === $currentDeviceId ? 'var(--md-surface-variant)' : 'transparent' }};">
                    <div>
                        <p class="font-bold text-body-lg" style="color: var(--md-on-surface);">{{ $device->device_name ?: ($device->browser . ' - ' . $device->os) }}</p>
                        <p class="text-label-sm" style="color: var(--md-outline);">IP: {{ $device->last_ip }} &bull; Aktif: {{ $device->last_seen_at->diffForHumans() }}</p>
                        @if($device->device_id === $currentDeviceId)
                            <span class="inline-block mt-2 px-2 py-0.5 rounded text-xs" style="background: var(--md-primary); color: white;">Perangkat Saat Ini</span>
                        @endif
                    </div>
                    @if($device->device_id !== $currentDeviceId)
                    <form method="POST" action="{{ route('security.revoke-device', $device->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-10 h-10 rounded-full flex items-center justify-center hover:bg-red-50 text-red-600 transition-colors" title="Hapus Perangkat" onclick="return confirm('Yakin ingin menghapus perangkat ini?')">
                            <span class="material-symbols-outlined">delete</span>
                        </button>
                    </form>
                    @endif
                </div>
                @empty
                <p class="text-body-md text-center py-4" style="color: var(--md-outline);">Belum ada perangkat yang terdaftar.</p>
                @endforelse
            </div>
            
            <div class="mt-6 pt-4" style="border-top: 1px solid var(--md-outline-variant);">
                @if(session('show_logout_otp'))
                    <form method="POST" action="{{ route('security.logout-all.confirm') }}" class="space-y-3">
                        @csrf
                        <label class="form-label">Masukkan OTP untuk konfirmasi</label>
                        <div class="flex gap-2">
                            <input type="text" name="otp" class="input-field text-center font-mono" placeholder="000000" maxlength="6" required autofocus>
                            <button type="submit" class="btn-primary">Konfirmasi</button>
                        </div>
                    </form>
                @else
                    <form method="POST" action="{{ route('security.logout-all') }}" onsubmit="return confirm('Ini akan mengeluarkan Anda dari semua perangkat lain. Lanjutkan?')">
                        @csrf
                        <button type="submit" class="btn w-full flex items-center justify-center gap-2 py-3 rounded-lg font-bold" style="background: var(--md-error-container); color: var(--md-on-error-container);">
                            <span class="material-symbols-outlined">logout</span>
                            Logout Semua Perangkat
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Riwayat Login --}}
        <div class="card p-6 sm:p-8 flex flex-col">
            <div class="flex items-center gap-3 mb-6 pb-4" style="border-bottom: 1px solid var(--md-outline-variant);">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: var(--md-surface-container-high); color: var(--md-on-surface); border: 1px solid var(--md-outline-variant);">
                    <span class="material-symbols-outlined">history</span>
                </div>
                <h4 class="text-headline-md" style="color: var(--md-on-surface);">Riwayat Login Terbaru</h4>
            </div>

            <div class="space-y-4 max-h-[500px] overflow-y-auto pr-2">
                @forelse($loginHistories as $history)
                <div class="pb-4 mb-4" style="border-bottom: 1px dashed var(--md-outline-variant);">
                    <div class="flex items-center justify-between mb-1">
                        <p class="font-bold text-body-md" style="color: var(--md-on-surface);">
                            @if($history->activity_type === 'login_success')
                                Login Berhasil
                            @elseif($history->activity_type === 'login_failed')
                                <span class="text-red-600">Login Gagal</span>
                            @elseif($history->activity_type === 'logout_all_devices')
                                Logout Semua
                            @elseif($history->activity_type === 'device_removed')
                                Hapus Perangkat
                            @else
                                {{ str_replace('_', ' ', Str::title($history->activity_type)) }}
                            @endif
                        </p>
                        <span class="text-label-sm" style="color: var(--md-outline);">{{ $history->created_at->format('d M, H:i') }}</span>
                    </div>
                    <p class="text-body-sm" style="color: var(--md-on-surface-variant);">
                        IP: {{ $history->ip_address }} 
                        @if($history->browser)
                        &bull; {{ $history->browser }} on {{ $history->os }}
                        @endif
                    </p>
                    @if($history->failure_reason)
                        <p class="text-label-sm text-red-500 mt-1">Alasan: {{ $history->failure_reason }}</p>
                    @endif
                </div>
                @empty
                <p class="text-body-md text-center py-4" style="color: var(--md-outline);">Belum ada riwayat aktivitas.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
