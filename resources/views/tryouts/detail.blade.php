@extends('app')

@section('content')
<div class="p-6 max-w-4xl mx-auto">
    <x-page-header title="Detail Tryout" />

    <!-- Loading state -->
    <div id="detail-loading" class="card animate-pulse">
        <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-3"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-2"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3 mb-2"></div>
        <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/3"></div>
    </div>

    <!-- Premium gate message -->
    <div id="premium-gate" class="hidden mb-6 p-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl">
        <div class="flex items-center gap-4">
            <svg class="w-12 h-12 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
            </svg>
            <div>
                <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200">Tryout Premium</h3>
                <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">Tryout ini hanya tersedia untuk member Premium. Upgrade akun kamu untuk mengakses semua tryout premium.</p>
                <a href="#" class="btn-primary mt-3 inline-block text-sm">Upgrade ke Premium</a>
            </div>
        </div>
    </div>

    <!-- Tryout info card -->
    <div id="tryout-info" class="hidden">
        <div class="card mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 id="tryout-title" class="text-xl font-bold text-gray-900 dark:text-gray-100">-</h2>
                <div id="tryout-tier-badge"></div>
            </div>

            <p id="tryout-description" class="text-sm text-gray-600 dark:text-gray-400 mb-6">-</p>

            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Kategori</p>
                    <p id="tryout-category" class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">-</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Durasi</p>
                    <p id="tryout-duration" class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">- menit</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Soal</p>
                    <p id="tryout-questions" class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">-</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Passing Score</p>
                    <p id="tryout-passing-score" class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">-%</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Tier Required</p>
                    <p id="tryout-required-tier" class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">-</p>
                </div>
                <div class="p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                    <p class="text-xs text-gray-500 dark:text-gray-400">Status</p>
                    <p id="tryout-status" class="text-sm font-semibold text-gray-900 dark:text-gray-100 mt-1">-</p>
                </div>
            </div>
        </div>

        <!-- Action button -->
        <div id="tryout-action" class="mb-6">
            <button id="start-tryout-btn" onclick="startTryout()" class="btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.97l-11.54 6.347a1.125 1.125 0 01-1.667-.985V5.653z"/>
                </svg>
                <span id="start-btn-text">Mulai Tryout</span>
            </button>
        </div>

        <!-- Previous attempts -->
        <div id="previous-attempts-section" class="hidden mb-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Percobaan Sebelumnya</h3>
            <div class="card">
                <div id="previous-attempts-list" class="divide-y divide-gray-200 dark:divide-gray-700"></div>
            </div>
        </div>
    </div>

    <!-- Rules section -->
    <div id="tryout-rules" class="hidden">
        <div class="card">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Peraturan Tryout</h3>
            <div class="space-y-3">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Timer:</strong> Ujian memiliki batas waktu. Timer akan mulai berjalan saat kamu memulai ujian dan tidak bisa dihentikan.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/>
                    </svg>
                    <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Navigasi:</strong> Kamu bisa berpindah antar soal menggunakan panel navigasi atau tombol Sebelumnya/Selanjutnya.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Pengerjaan:</strong> Pastikan semua soal sudah dijawab sebelum menekan tombol Selesai. Soal yang belum dijawab akan dihitung sebagai jawaban salah.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L6.698 12.063c-.866-1.5.217-3.374 1.948-3.374h14.71"/>
                    </svg>
                    <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Ragu-ragu:</strong> Kamu bisa menandai soal yang kamu ragu dengan fitur ragu-ragu. Soal yang ditandai akan tetap terhitung sebagai jawaban.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                    </svg>
                    <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Keluar halaman:</strong> Jangan keluar dari halaman ujian saat sedang mengerjakan. Waktu akan terus berjalan meskipun kamu meninggalkan halaman.</p>
                </div>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-sm text-gray-700 dark:text-gray-300"><strong>Auto-submit:</strong> Ujian akan otomatis dikirim saat waktu habis. Jawaban yang sudah tersimpan akan dipakai sebagai jawaban final.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Error state -->
    <div id="detail-error" class="hidden">
        <div class="card text-center py-8">
            <svg class="w-16 h-16 text-red-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Gagal memuat detail tryout</h3>
            <p id="error-message" class="text-sm text-gray-500 dark:text-gray-400 mt-2">-</p>
            <a href="{{ route('tryouts') }}" class="btn-secondary mt-4 inline-block">Kembali ke Daftar Tryout</a>
        </div>
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
        previousAttempts = t.previous_attempts || t.attempts || t.user_attempts || [];

        previousAttempts.forEach(function(a) {
            var status = a.status || '';
            if (status === 'IN_PROGRESS' || status === 'STARTED') {
                activeAttempt = a;
            }
        });

        if (activeAttempt) {
            document.getElementById('start-btn-text').textContent = 'Lanjutkan';
            document.getElementById('start-tryout-btn').setAttribute('data-attempt-id', activeAttempt.id || activeAttempt.attempt_id);
        } else {
            document.getElementById('start-btn-text').textContent = 'Mulai Tryout';
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
                actionLink = '<a href="/tryouts/' + slug + '/exam" class="text-blue-600 dark:text-blue-400 hover:underline text-sm ml-3">Lanjutkan</a>';
            } else if (status === 'COMPLETED' || status === 'SUBMITTED') {
                actionLink = '<a href="/tryouts/' + slug + '/result/' + attemptId + '" class="text-blue-600 dark:text-blue-400 hover:underline text-sm ml-3">Lihat Hasil</a>';
            }

            return '<div class="py-3 flex items-center justify-between">' +
                '<div>' +
                    '<p class="font-medium text-gray-900 dark:text-gray-100">Percobaan #' + (a.attempt_number || a.number || attemptId) + '</p>' +
                    '<p class="text-sm text-gray-500 dark:text-gray-400">' + completedAt + '</p>' +
                '</div>' +
                '<div class="flex items-center gap-3">' +
                    statusBadge +
                    '<span class="font-semibold text-gray-900 dark:text-gray-100">' + scoreDisplay + '</span>' +
                    actionLink +
                '</div>' +
            '</div>';
        }).join('');
    }

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