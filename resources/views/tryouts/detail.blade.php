@extends('app')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-page-header title="Detail Tryout" breadcrumb='<a href="/tryouts">Tryouts</a><span class="breadcrumb-sep">/</span><span>Detail</span>' />

    <!-- Loading state -->
    <div id="detail-loading" class="card skeleton">
        <div class="skeleton-heading w-1/3"></div>
        <div class="skeleton-text w-full"></div>
        <div class="skeleton-text w-3/4"></div>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-6">
            <div class="h-16 bg-[var(--bg-subtle)] rounded-lg"></div>
            <div class="h-16 bg-[var(--bg-subtle)] rounded-lg"></div>
            <div class="h-16 bg-[var(--bg-subtle)] rounded-lg"></div>
        </div>
    </div>

    <!-- Premium gate message -->
    <div id="premium-gate" class="hidden mb-6 premium-gate animate-fade-in-up">
        <svg class="premium-gate-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
        </svg>
        <h3 class="premium-gate-title">Tryout Premium</h3>
        <p class="premium-gate-desc">Tryout ini hanya tersedia untuk member Premium. Upgrade akun kamu untuk mengakses semua tryout premium.</p>
        <a href="{{ route('profile') }}" class="btn-primary mt-4 text-sm">Upgrade ke Premium</a>
    </div>

    <!-- Tryout info card -->
    <div id="tryout-info" class="hidden animate-fade-in-up">
        <div class="card mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 id="tryout-title" class="text-xl font-bold text-[var(--text-primary)]">-</h2>
                <div id="tryout-tier-badge"></div>
            </div>

            <p id="tryout-description" class="text-sm text-[var(--text-secondary)] mb-6 leading-relaxed">-</p>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="info-grid-item">
                    <p class="info-grid-label">Kategori</p>
                    <p id="tryout-category" class="info-grid-value">-</p>
                </div>
                <div class="info-grid-item">
                    <p class="info-grid-label">Durasi</p>
                    <p id="tryout-duration" class="info-grid-value">- menit</p>
                </div>
                <div class="info-grid-item">
                    <p class="info-grid-label">Total Soal</p>
                    <p id="tryout-questions" class="info-grid-value">-</p>
                </div>
                <div class="info-grid-item">
                    <p class="info-grid-label">Passing Score</p>
                    <p id="tryout-passing-score" class="info-grid-value">-%</p>
                </div>
                <div class="info-grid-item">
                    <p class="info-grid-label">Tier Required</p>
                    <p id="tryout-required-tier" class="info-grid-value">-</p>
                </div>
                <div class="info-grid-item">
                    <p class="info-grid-label">Status</p>
                    <p id="tryout-status" class="info-grid-value">-</p>
                </div>
            </div>
        </div>

        <!-- Action button -->
        <div id="tryout-action" class="mb-8 flex justify-center">
            <button id="start-tryout-btn" onclick="startTryout()" class="btn-primary flex items-center gap-2 px-8 py-3 text-lg">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.97l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                </svg>
                <span id="start-btn-text" class="font-bold">Mulai Tryout</span>
            </button>
        </div>

        <!-- Previous attempts -->
        <div id="previous-attempts-section" class="hidden mb-6 animate-fade-in-up" style="animation-delay: 50ms;">
            <h3 class="text-lg font-semibold mb-4">Percobaan Sebelumnya</h3>
            <div class="data-table-wrapper" id="previous-attempts-list"></div>
        </div>
    </div>

    <!-- Rules section -->
    <div id="tryout-rules" class="hidden mb-6 animate-fade-in-up" style="animation-delay: 100ms;">
        <div class="card">
            <h3 class="text-lg font-semibold mb-4">Peraturan Tryout</h3>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-[var(--info-subtle)] text-[var(--info)] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[var(--text-primary)]">Timer Ujian</p>
                        <p class="text-sm text-[var(--text-secondary)] mt-0.5">Ujian memiliki batas waktu. Timer akan mulai berjalan saat kamu memulai ujian dan tidak bisa dihentikan.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-[var(--success-subtle)] text-[var(--success)] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[var(--text-primary)]">Pengerjaan & Auto-submit</p>
                        <p class="text-sm text-[var(--text-secondary)] mt-0.5">Pastikan semua soal sudah dijawab sebelum menekan tombol Selesai. Ujian akan otomatis dikirim saat waktu habis.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-[var(--danger-subtle)] text-[var(--danger)] flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-[var(--text-primary)]">Jangan Keluar Halaman</p>
                        <p class="text-sm text-[var(--text-secondary)] mt-0.5">Jangan keluar dari halaman ujian saat sedang mengerjakan. Waktu akan terus berjalan meskipun kamu meninggalkan halaman.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error state -->
    <div id="detail-error" class="hidden">
        <x-empty-state 
            icon='<svg class="w-16 h-16 text-[var(--danger)] mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>' 
            title="Gagal memuat detail tryout" 
        >
            <x-slot:description>
                <span id="error-message">-</span>
            </x-slot:description>
            <x-slot:action>
                <a href="{{ route('tryouts') }}" class="btn-secondary mt-4">Kembali ke Daftar Tryout</a>
            </x-slot:action>
        </x-empty-state>
    </div>
</div>

<script>
(function() {
    var slug = '{{ $slug }}';
    var tryoutData = null;
    var previousAttempts = [];
    var userTier = '{{ auth()->user()->membership_tier }}';
    var isPremiumLocked = false;

    function loadTryoutDetail() {
        apiFetch('/tryouts/' + slug).then(function(data) {
            tryoutData = data.tryout || data;
            if (data.tryout) {
                tryoutData.category_name = data.categoryName;
                tryoutData.previous_attempts = data.attempts || (data.attempt ? [data.attempt] : []);
            }
            renderTryoutDetail(tryoutData);
            document.getElementById('detail-loading').classList.add('hidden');
        }).catch(function(err) {
            console.error('Failed to load tryout detail:', err);
            document.getElementById('detail-loading').classList.add('hidden');
            document.getElementById('detail-error').classList.remove('hidden');
            document.getElementById('error-message').textContent = err.message || 'Terjadi kesalahan saat memuat data.';
        });
    }

    function renderTryoutDetail(t) {
        var requiredTier = t.tier || t.membership_tier || t.required_tier || 'FREE';
        isPremiumLocked = requiredTier === 'PREMIUM' && userTier !== 'PREMIUM';

        // Title & description
        document.getElementById('tryout-title').textContent = t.title || t.name || '-';
        document.getElementById('tryout-description').textContent = t.description || t.desc || 'Tidak ada deskripsi.';

        // Tier badge
        var tierBadgeHtml = requiredTier === 'PREMIUM'
            ? '<span class="badge badge-premium">PREMIUM</span>'
            : '<span class="badge badge-free">FREE</span>';
        document.getElementById('tryout-tier-badge').innerHTML = tierBadgeHtml;

        // Info fields
        var categoryName = (t.category && t.category.name) || t.category_name || '-';
        var duration = t.duration || t.duration_minutes || '-';
        var questionCount = t.question_count || t.totalQuestions || t.total_questions || '-';
        var passingScore = t.passing_score || t.passingScore || '-';
        var status = t.status || t.is_active ? 'Aktif' : 'Tidak Aktif';

        document.getElementById('tryout-category').textContent = categoryName;
        document.getElementById('tryout-duration').textContent = duration + ' menit';
        document.getElementById('tryout-questions').textContent = questionCount;
        document.getElementById('tryout-passing-score').textContent = passingScore + '%';
        document.getElementById('tryout-required-tier').textContent = requiredTier;
        document.getElementById('tryout-status').textContent = status;

        // Show tryout info card
        document.getElementById('tryout-info').classList.remove('hidden');

        // Premium gate
        if (isPremiumLocked) {
            document.getElementById('premium-gate').classList.remove('hidden');
            document.getElementById('tryout-action').classList.add('hidden');
        } else {
            document.getElementById('premium-gate').classList.add('hidden');
            document.getElementById('tryout-action').classList.remove('hidden');
        }

        // Determine start/resume button
        var activeAttempt = null;
        var hasCompletedAttempt = false;
        previousAttempts = t.previous_attempts || t.attempts || t.user_attempts || [];

        previousAttempts.forEach(function(a) {
            var status = a.status || '';
            if (status === 'IN_PROGRESS' || status === 'STARTED') {
                activeAttempt = a;
            } else if (status === 'COMPLETED' || status === 'SUBMITTED') {
                hasCompletedAttempt = true;
            }
        });

        if (activeAttempt) {
            document.getElementById('start-btn-text').textContent = 'Lanjutkan';
            document.getElementById('start-tryout-btn').setAttribute('data-attempt-id', activeAttempt.id || activeAttempt.attempt_id);
        } else {
            document.getElementById('start-btn-text').textContent = hasCompletedAttempt ? 'Coba Lagi' : 'Mulai Tryout';
            document.getElementById('start-tryout-btn').removeAttribute('data-attempt-id');
        }

        // Previous attempts section
        if (previousAttempts.length > 0) {
            document.getElementById('previous-attempts-section').classList.remove('hidden');
            renderPreviousAttempts(previousAttempts);
        }

        // Rules section
        document.getElementById('tryout-rules').classList.remove('hidden');
    }

    function renderPreviousAttempts(attempts) {
        var list = document.getElementById('previous-attempts-list');
        list.innerHTML = attempts.map(function(a) {
            var status = a.status || '';
            var statusBadge = '';
            if (status === 'COMPLETED' || status === 'SUBMITTED') {
                statusBadge = '<span class="badge badge-success">Selesai</span>';
            } else if (status === 'IN_PROGRESS' || status === 'STARTED') {
                statusBadge = '<span class="badge badge-danger">Sedang Berjalan</span>';
            } else {
                statusBadge = '<span class="badge badge-free">' + status + '</span>';
            }

            var scoreDisplay = (a.score !== null && a.score !== undefined) ? a.score + '%' : '-';
            var completedAt = a.completed_at || a.submitted_at || a.created_at || '-';
            var attemptId = a.id || a.attempt_id || '';

            var actionLink = '';
            if (status === 'IN_PROGRESS' || status === 'STARTED') {
                actionLink = '<a href="/tryouts/' + slug + '/exam" class="btn-ghost btn-sm text-[var(--accent)]">Lanjutkan</a>' +
                             '<button onclick="endTryout(\'' + attemptId + '\')" class="btn-ghost btn-sm text-[var(--danger)] ml-2">Akhiri</button>';
            } else if (status === 'COMPLETED' || status === 'SUBMITTED') {
                actionLink = '<a href="/tryouts/' + slug + '/result/' + attemptId + '" class="btn-ghost btn-sm text-[var(--accent)]">Lihat Hasil</a>';
            }

            return '<div class="list-row">' +
                '<div>' +
                    '<p class="font-medium text-[var(--text-primary)]">Percobaan #' + (a.attempt_number || a.number || attemptId) + '</p>' +
                    '<p class="text-xs text-[var(--text-secondary)] mt-0.5">' + completedAt + '</p>' +
                '</div>' +
                '<div class="flex items-center gap-4">' +
                    statusBadge +
                    '<span class="font-mono font-bold w-12 text-right">' + scoreDisplay + '</span>' +
                    actionLink +
                '</div>' +
            '</div>';
        }).join('');
    }

    window.endTryout = function(attemptId) {
        if (!confirm('Anda yakin ingin mengakhiri tryout ini? Ujian akan dikumpulkan dan skor akan dihitung.')) {
            return;
        }
        apiFetch('/tryouts/' + slug + '/submit', {
            method: 'POST',
            body: JSON.stringify({ attempt_id: attemptId })
        }).then(function() {
            if (typeof showToast === 'function') {
                showToast('Tryout berhasil diakhiri.', 'success');
            } else {
                alert('Tryout berhasil diakhiri.');
            }
            loadTryoutDetail();
        }).catch(function(err) {
            alert('Gagal mengakhiri tryout: ' + err.message);
        });
    };

    window.startTryout = function() {
        var btn = document.getElementById('start-tryout-btn');
        var btnText = document.getElementById('start-btn-text');
        var attemptId = btn.getAttribute('data-attempt-id');

        // If there's an active attempt, just redirect to exam
        if (attemptId) {
            window.location.href = '/tryouts/' + slug + '/exam';
            return;
        }

        // Otherwise, create a new attempt via POST
        btn.disabled = true;
        btnText.textContent = 'Memulai...';

        apiFetch('/tryouts/' + slug + '/start', {
            method: 'POST'
        }).then(function(data) {
            window.location.href = '/tryouts/' + slug + '/exam';
        }).catch(function(err) {
            console.error('Failed to start tryout:', err);
            alert('Gagal memulai tryout: ' + err.message);
            btn.disabled = false;
            btnText.textContent = 'Mulai Tryout';
        });
    };

    // Load user info first then tryout detail
    apiFetch('/auth/me').then(function(user) {
        window.SKB.user = user;
        window.SKB.isAdmin = user.role === 'ADMIN' || user.role === 'SUPERADMIN';
        window.SKB.isSuperAdmin = user.role === 'SUPERADMIN';
    }).catch(function() {}).finally(function() {
        loadTryoutDetail();
    });
})();
</script>
@endsection