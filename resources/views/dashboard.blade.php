@extends('app')

@section('content')
    <div class="space-y-6 max-w-5xl mx-auto">
        {{-- Page Header --}}
        <div class="mb-2">
            <div class="flex flex-wrap items-center gap-3">
                <h1 class="text-display-lg" style="color: var(--md-on-surface);">{{ Auth::user()->name }}</h1>
                <span class="badge {{ Auth::user()->membership_tier === 'PREMIUM' ? 'badge-premium' : 'badge-free' }}">
                    {{ Auth::user()->membership_tier === 'PREMIUM' ? 'PREMIUM' : 'GRATIS' }}
                </span>
            </div>
            <p class="text-body-md mt-1" style="color: var(--md-on-surface-variant);">{{ Auth::user()->email }}</p>
            @if(Auth::user()->membership_status === 'ACTIVE' && Auth::user()->membership_tier === 'PREMIUM' && Auth::user()->membership_expiry)
                <p class="text-label-md mt-1" style="color: var(--success);">
                    <span class="material-symbols-outlined text-sm align-middle">verified</span>
                    Premium aktif sampai {{ \Carbon\Carbon::parse(Auth::user()->membership_expiry)->format('d M Y') }}
                </p>
            @endif
        </div>

        {{-- Active Attempt Alert --}}
        <div id="active-attempt-alert" class="hidden">
            <x-alert type="warning">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <p class="font-medium">Kamu memiliki tryout yang sedang berjalan!</p>
                        <p class="text-sm opacity-90 mt-0.5" id="active-attempt-name"></p>
                    </div>
                    <div class="flex gap-2">
                        <a href="#" id="active-attempt-resume" class="btn-primary btn-sm flex-shrink-0">
                            <span class="material-symbols-outlined text-lg">play_arrow</span>
                            Lanjutkan
                        </a>
                        <button id="active-attempt-end" class="btn-danger btn-sm flex-shrink-0">
                            <span class="material-symbols-outlined text-lg">stop</span>
                            Akhiri
                        </button>
                    </div>
                </div>
            </x-alert>
        </div>

        {{-- Quick Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="stat-card">
                <div class="flex items-center gap-3 mb-4">
                    <div class="stat-card-icon" style="background: var(--md-primary-fixed); color: var(--md-primary);">
                        <span class="material-symbols-outlined">check_circle</span>
                    </div>
                    <span class="stat-card-label">Total Tryout Selesai</span>
                </div>
                <div class="stat-card-value" id="stat-completed">0</div>
            </div>

            <div class="stat-card">
                <div class="flex items-center gap-3 mb-4">
                    <div class="stat-card-icon" style="background: var(--success-subtle); color: var(--success);">
                        <span class="material-symbols-outlined">menu_book</span>
                    </div>
                    <span class="stat-card-label">Materi Dipelajari</span>
                </div>
                <div class="stat-card-value" id="stat-materials">0</div>
            </div>

            <div class="stat-card sm:col-span-2 lg:col-span-2">
                <span class="stat-card-label">Status Membership</span>
                <div class="flex items-center gap-3 mt-4">
                    <span class="badge {{ Auth::user()->isPremiumActive() ? 'badge-premium' : 'badge-free' }} px-3 py-1 text-sm">
                        {{ Auth::user()->isPremiumActive() ? 'PREMIUM AKTIF' : 'GRATIS' }}
                    </span>
                    @if(!Auth::user()->isPremiumActive())
                        <a href="{{ route('profile') }}" class="text-label-md font-semibold hover:underline" style="color: var(--md-primary);">
                            Upgrade sekarang
                            <span class="material-symbols-outlined text-sm align-middle">arrow_forward</span>
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tryout CTA Section --}}
        <div class="card text-center p-8 border-dashed" style="background: var(--md-surface-container-low);">
            <div class="w-14 h-14 rounded-full flex items-center justify-center mx-auto mb-4"
                style="background: var(--md-primary-fixed); color: var(--md-primary);">
                <span class="material-symbols-outlined text-3xl">school</span>
            </div>
            <h3 class="text-headline-md mb-2" style="color: var(--md-on-surface);">Jelajahi Tryout SKB</h3>
            <p class="text-body-md max-w-md mx-auto mb-6" style="color: var(--md-on-surface-variant);">
                Uji kemampuanmu dengan berbagai tryout SKB yang tersedia. Dapatkan analisis nilai dan pembahasan lengkap.
            </p>
            <a href="{{ route('tryouts') }}" class="btn-primary">
                <span class="material-symbols-outlined text-lg">quiz</span>
                Lihat Daftar Tryout
            </a>
        </div>

        {{-- Recent Attempts --}}
        <div id="recent-attempts" class="hidden">
            <h3 class="text-headline-sm mb-4" style="color: var(--md-on-surface);">Percobaan Terakhir</h3>
            <div class="data-table-wrapper" id="recent-attempts-list">
                <!-- Loaded via JS -->
            </div>
        </div>
    </div>

    <script>
        (function () {
            // Load stats
            apiFetch('/dashboard').then(function (data) {
                var s = data.stats || data || {};
                document.getElementById('stat-completed').textContent = s.completedAttempts || 0;
                document.getElementById('stat-materials').textContent = s.completedMaterials || 0;
            }).catch(function () { });

            // Check active attempt
            apiFetch('/active-attempt').then(function (data) {
                if (data && data.active) {
                    var el = document.getElementById('active-attempt-alert');
                    el.classList.remove('hidden');
                    document.getElementById('active-attempt-name').textContent = data.tryoutTitle || data.tryoutSlug || '';
                    document.getElementById('active-attempt-resume').href = '/tryouts/' + (data.tryoutSlug || '') + '/exam';

                    var endBtn = document.getElementById('active-attempt-end');
                    if (endBtn) {
                        endBtn.onclick = function() {
                            if (!confirm('Anda yakin ingin mengakhiri tryout ini? Ujian akan dikumpulkan dan skor akan dihitung.')) {
                                return;
                            }
                            apiFetch('/tryouts/' + (data.tryoutSlug || '') + '/submit', {
                                method: 'POST',
                                body: JSON.stringify({ attempt_id: data.attemptId })
                            }).then(function() {
                                if (typeof showToast === 'function') {
                                    showToast('Tryout berhasil diakhiri.', 'success');
                                } else {
                                    alert('Tryout berhasil diakhiri.');
                                }
                                window.location.reload();
                            }).catch(function(err) {
                                alert('Gagal mengakhiri tryout: ' + err.message);
                            });
                        };
                    }
                }
            }).catch(function () { });

            // Load recent attempts
            apiFetch('/my-attempts?limit=5').then(function (data) {
                var attempts = data.data || data || [];
                if (Array.isArray(attempts) && attempts.length > 0) {
                    document.getElementById('recent-attempts').classList.remove('hidden');
                    var list = document.getElementById('recent-attempts-list');
                    var rowsHtml = attempts.slice(0, 5).map(function (a) {
                        var isSubmitted = a.status === 'SUBMITTED';
                        var score = a.score !== null && a.score !== undefined ? a.score + '%' : '-';
                        var statusBadge = isSubmitted
                            ? '<span class="badge badge-success">SELESAI</span>'
                            : '<span class="badge badge-warning">' + (a.status || '') + '</span>';
                        return '<div class="list-row">' +
                            '<div class="min-w-0 flex-1">' +
                            '<p class="font-medium truncate" style="color: var(--md-on-surface);">' + (a.tryoutTitle || 'Tryout') + '</p>' +
                            '<p class="text-label-sm mt-0.5" style="color: var(--md-outline);">' + (a.categoryName || '') + '</p>' +
                            '</div>' +
                            '<div class="flex items-center gap-4 shrink-0">' +
                            statusBadge +
                            '<span class="font-mono font-bold w-12 text-right">' + score + '</span>' +
                            '<a href="/tryouts/' + (a.tryoutSlug || '') + '/result/' + a.id + '" class="btn-ghost btn-sm" style="color: var(--md-primary);">Lihat</a>' +
                            '</div>' +
                            '</div>';
                    }).join('');
                    list.innerHTML = rowsHtml;
                }
            }).catch(function () { });
        })();
    </script>
@endsection