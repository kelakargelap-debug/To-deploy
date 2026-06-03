@extends('app')

@section('content')
    <div class="space-y-8">
        {{-- Account Status Header --}}
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-[1.067rem] font-medium" style="color: var(--text-primary);">
                    {{ Auth::user()->name }}
                </h2>
                <span
                    class="{{ Auth::user()->membership_tier === 'PREMIUM' ? 'badge badge-premium' : 'badge badge-free' }}">
                    {{ Auth::user()->membership_tier === 'PREMIUM' ? 'PREMIUM' : 'GRATIS' }}
                </span>
            </div>
            <p class="text-[13px] mt-0.5" style="color: var(--text-secondary);">{{ Auth::user()->email }}</p>
            @if(Auth::user()->membership_status === 'ACTIVE' && Auth::user()->membership_tier === 'PREMIUM' && Auth::user()->membership_expiry)
                <p class="text-[12px] mt-0.5" style="color: var(--text-muted);">
                    Premium sampai {{ \Carbon\Carbon::parse(Auth::user()->membership_expiry)->format('d M Y') }}
                </p>
            @endif
        </div>

        {{-- Active Attempt Alert --}}
        <div id="active-attempt-alert" class="hidden">
            <div class="p-3 rounded-lg flex items-center gap-3"
                style="background: var(--warning-subtle); border: 1px solid var(--warning); color: var(--warning);">
                <svg class="w-5 h-5 shrink-0 animate-ping" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z" />
                </svg>
                <div>
                    <p class="text-[13px] font-medium">Kamu memiliki tryout yang sedang berjalan!</p>
                    <p class="text-[12px] opacity-75" id="active-attempt-name"></p>
                </div>
                <a href="#" id="active-attempt-resume" class="ml-auto btn-primary text-[13px] h-8 px-3">Lanjutkan</a>
            </div>
        </div>

        {{-- Quick Stats — flat rows, not cards --}}
        <div class="rounded-lg overflow-hidden" style="border: 1px solid var(--border-subtle);">
            <div class="list-row clickable">
                <span class="text-[13px]" style="color: var(--text-secondary);">Total Tryout Diselesaikan</span>
                <span class="text-[14px] font-medium font-mono" style="color: var(--text-primary);"
                    id="stat-completed">0</span>
            </div>
            <div class="list-row clickable">
                <span class="text-[13px]" style="color: var(--text-secondary);">Materi Dipelajari</span>
                <span class="text-[14px] font-medium font-mono" style="color: var(--text-primary);"
                    id="stat-materials">0</span>
            </div>
            <div class="list-row clickable">
                <span class="text-[13px]" style="color: var(--text-secondary);">Status Membership</span>
                <span class="{{ Auth::user()->isPremiumActive() ? 'badge badge-premium' : 'badge badge-free' }}">
                    {{ Auth::user()->isPremiumActive() ? 'PREMIUM AKTIF' : 'GRATIS' }}
                </span>
            </div>
        </div>

        {{-- Tryout Section --}}
        <div>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-[1rem] font-medium" style="color: var(--text-primary);">Tryout</h3>
                <a href="{{ route('tryouts') }}" class="text-[13px] font-medium hover:underline"
                    style="color: var(--accent);">Lihat Semua &rarr;</a>
            </div>
            <div class="rounded-lg p-8 text-center" style="border: 1px dashed var(--border-default);">
                <p class="text-[13px]" style="color: var(--text-muted);">Jelajahi tryout yang tersedia untuk menguji
                    kemampuan Anda.</p>
                <a href="{{ route('tryouts') }}" class="btn-primary mt-3 h-8 text-[13px] px-4 inline-flex">Lihat Tryout</a>
            </div>
        </div>

        {{-- Recent Attempts --}}
        <div id="recent-attempts" class="hidden">
            <h3 class="text-[1rem] font-medium mb-4" style="color: var(--text-primary);">Percobaan Terakhir</h3>
            <div class="rounded-lg overflow-hidden" style="border: 1px solid var(--border-subtle);"
                id="recent-attempts-list">
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
                }
            }).catch(function () { });

            // Load recent attempts
            apiFetch('/my-attempts?limit=5').then(function (data) {
                var attempts = data.data || data || [];
                if (Array.isArray(attempts) && attempts.length > 0) {
                    document.getElementById('recent-attempts').classList.remove('hidden');
                    var list = document.getElementById('recent-attempts-list');
                    list.innerHTML = attempts.slice(0, 5).map(function (a) {
                        var isSubmitted = a.status === 'SUBMITTED';
                        var score = a.score !== null && a.score !== undefined ? a.score + '%' : '-';
                        var statusBadge = isSubmitted
                            ? '<span class="badge badge-success">SELESAI</span>'
                            : '<span class="badge badge-warning">' + (a.status || '') + '</span>';
                        return '<div class="list-row">' +
                            '<div class="min-w-0 flex-1">' +
                            '<p class="text-[14px] font-medium truncate" style="color: var(--text-primary);">' + (a.tryoutTitle || 'Tryout') + '</p>' +
                            '<p class="text-[12px] mt-0.5" style="color: var(--text-muted);">' + (a.categoryName || '') + '</p>' +
                            '</div>' +
                            '<div class="flex items-center gap-3 ml-4 shrink-0">' +
                            statusBadge +
                            '<span class="text-[14px] font-mono font-medium" style="color: var(--text-primary);">' + score + '</span>' +
                            '<a href="/tryouts/' + (a.tryoutSlug || '') + '/result/' + a.id + '" class="text-[13px] font-medium hover:underline" style="color: var(--accent);">Lihat</a>' +
                            '</div>' +
                            '</div>';
                    }).join('');
                }
            }).catch(function () { });
        })();
    </script>
@endsection