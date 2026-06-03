@extends('app')

@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <!-- Page header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Daftar Tryout</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih tryout yang ingin kamu kerjakan</p>
    </div>

    <!-- Category filter chips -->
    <div id="category-filters" class="mb-6 flex flex-wrap gap-2">
        <button onclick="filterByCategory('all')" class="filter-chip active" data-category="all">Semua</button>
        <!-- Category chips will be loaded via JS -->
    </div>

    <!-- Tryout grid -->
    <div id="tryout-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Cards loaded via JS -->
    </div>

    <!-- Empty state -->
    <div id="tryout-empty" class="hidden">
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Belum ada tryout tersedia</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Coba kembali lagi nanti</p>
        </div>
    </div>

    <!-- Loading state -->
    <div id="tryout-loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="card animate-pulse"><div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-3"></div><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-2"></div><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div></div>
        <div class="card animate-pulse"><div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-3"></div><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-2"></div><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div></div>
        <div class="card animate-pulse"><div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-3"></div><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2 mb-2"></div><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div></div>
    </div>
</div>

<style>
    .filter-chip {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.875rem;
        font-weight: 500;
        transition: all 150ms;
        background: #f3f4f6;
        color: #4b5563;
        border: 1px solid transparent;
    }
    .filter-chip:hover {
        background: #e5e7eb;
    }
    .filter-chip.active {
        background: #dbeafe;
        color: #1d4ed8;
        border-color: #93c5fd;
    }
    :root.dark .filter-chip {
        background: #374151;
        color: #9ca3af;
    }
    :root.dark .filter-chip:hover {
        background: #4b5563;
    }
    :root.dark .filter-chip.active {
        background: rgba(30, 58, 138, 0.3);
        color: #60a5fa;
        border-color: #1e3a8a;
    }
</style>

<script>
(function() {
    var allTryouts = [];
    var categories = [];
    var activeCategory = 'all';
    var userTier = 'FREE';

    // Load user info first
    apiFetch('/auth/me').then(function(user) {
        window.SKB.user = user;
        userTier = user.membership_tier || 'FREE';
        window.SKB.isAdmin = user.role === 'ADMIN' || user.role === 'SUPERADMIN';
        window.SKB.isSuperAdmin = user.role === 'SUPERADMIN';
    }).catch(function() {}).finally(function() {
        loadTryouts();
    });

    function loadTryouts() {
        apiFetch('/tryouts').then(function(data) {
            allTryouts = data.data || data || [];
            // Extract categories
            var catSet = {};
            allTryouts.forEach(function(t) {
                var catName = t.categoryName || (t.category && t.category.name) || t.category_name || '';
                if (catName) catSet[catName] = true;
            });
            categories = Object.keys(catSet);
            renderCategoryFilters();
            renderTryouts();
            document.getElementById('tryout-loading').classList.add('hidden');
        }).catch(function(err) {
            console.error('Failed to load tryouts:', err);
            document.getElementById('tryout-loading').classList.add('hidden');
            document.getElementById('tryout-empty').classList.remove('hidden');
        });
    }

    function renderCategoryFilters() {
        var container = document.getElementById('category-filters');
        if (!container) return;
        var html = '<button onclick="filterByCategory(\'all\')" class="filter-chip ' + (activeCategory === 'all' ? 'active' : '') + '" data-category="all">Semua</button>';
        categories.forEach(function(cat) {
            html += '<button onclick="filterByCategory(\'' + cat + '\')" class="filter-chip ' + (activeCategory === cat ? 'active' : '') + '" data-category="' + cat + '">' + cat + '</button>';
        });
        container.innerHTML = html;
    }

    window.filterByCategory = function(cat) {
        activeCategory = cat;
        renderCategoryFilters();
        renderTryouts();
    };

    function renderTryouts() {
        var filtered = activeCategory === 'all' ? allTryouts : allTryouts.filter(function(t) {
            var catName = t.categoryName || (t.category && t.category.name) || t.category_name || '';
            return catName === activeCategory;
        });

        var container = document.getElementById('tryout-grid');
        var emptyState = document.getElementById('tryout-empty');

        if (filtered.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        container.innerHTML = filtered.map(function(t) {
            var catBadge = t.categoryName || (t.category && t.category.name) || t.category_name || '';
            var tierBadge = t.tier || t.membership_tier || t.required_tier || 'FREE';
            var isPremium = tierBadge === 'PREMIUM';
            var isLocked = isPremium && userTier !== 'PREMIUM';
            var duration = t.duration || t.duration_minutes || '-';
            var questionCount = t.total_questions || t.question_count || t.totalQuestions || '-';
            var passingScore = t.passing_score || t.passingScore || '-';
            var prevAttempt = t.previous_attempt || t.lastAttempt || (t.attemptId ? { id: t.attemptId, status: t.attemptStatus, score: t.attemptScore } : null);
            var prevStatus = prevAttempt ? prevAttempt.status : null;

            var actionBtn = '';
            if (isLocked) {
                actionBtn = '<button class="btn-secondary w-full opacity-75 cursor-not-allowed flex items-center justify-center gap-2" disabled>' +
                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>' +
                    'Premium</button>';
            } else if (prevStatus === 'IN_PROGRESS' || prevStatus === 'STARTED') {
                actionBtn = '<a href="/tryouts/' + (t.slug || t.id) + '/exam" class="btn-primary w-full text-center">Lanjutkan</a>';
            } else if (prevStatus === 'COMPLETED' || prevStatus === 'SUBMITTED') {
                actionBtn = '<a href="/tryouts/' + (t.slug || t.id) + '/result/' + prevAttempt.id + '" class="btn-secondary w-full text-center">Lihat Hasil</a>';
            } else {
                actionBtn = '<a href="/tryouts/' + (t.slug || t.id) + '" class="btn-primary w-full text-center">Mulai</a>';
            }

            var lockOverlay = isLocked ? '<div class="absolute inset-0 bg-gray-900/50 dark:bg-gray-900/70 rounded-xl flex items-center justify-center z-10"><div class="text-center"><svg class="w-8 h-8 text-amber-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg><p class="text-white font-medium text-sm">Premium Only</p></div></div>' : '';

            var tierBadgeHtml = isPremium
                ? '<span class="badge badge-premium">PREMIUM</span>'
                : '<span class="badge badge-free">FREE</span>';

            var prevStatusBadge = '';
            if (prevStatus === 'COMPLETED' || prevStatus === 'SUBMITTED') {
                prevStatusBadge = '<span class="badge badge-success text-xs">Selesai</span>';
            } else if (prevStatus === 'IN_PROGRESS' || prevStatus === 'STARTED') {
                prevStatusBadge = '<span class="badge badge-danger text-xs">Sedang Berjalan</span>';
            }

            return '<div class="card relative overflow-hidden">' +
                lockOverlay +
                '<div class="flex items-center justify-between mb-3">' +
                    '<h3 class="font-semibold text-gray-900 dark:text-gray-100">' + (t.title || t.name) + '</h3>' +
                    tierBadgeHtml +
                '</div>' +
                (catBadge ? '<p class="text-xs text-gray-500 dark:text-gray-400 mb-3"><span class="bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded">' + catBadge + '</span></p>' : '') +
                '<div class="space-y-2 text-sm text-gray-600 dark:text-gray-400 mb-4">' +
                    '<div class="flex justify-between"><span>Durasi</span><span class="font-medium text-gray-900 dark:text-gray-100">' + duration + ' menit</span></div>' +
                    '<div class="flex justify-between"><span>Jumlah Soal</span><span class="font-medium text-gray-900 dark:text-gray-100">' + questionCount + '</span></div>' +
                    '<div class="flex justify-between"><span>Passing Score</span><span class="font-medium text-gray-900 dark:text-gray-100">' + passingScore + '%</span></div>' +
                '</div>' +
                (prevStatusBadge ? '<div class="mb-3">' + prevStatusBadge + '</div>' : '') +
                actionBtn +
            '</div>';
        }).join('');
    }
})();
</script>
@endsection