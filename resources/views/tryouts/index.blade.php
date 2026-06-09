@extends('app')

@section('content')
<div class="max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-display-lg mb-2" style="color: var(--md-on-surface);">Daftar Tryout</h1>
        <p class="text-body-md" style="color: var(--md-on-surface-variant);">Pilih tryout yang ingin kamu kerjakan</p>
    </div>

    <!-- Filters & View Toggle -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div id="category-filters" class="flex flex-wrap gap-3">
            <button onclick="filterByCategory('all')" class="filter-chip active" data-category="all">Semua</button>
        </div>
        <div class="flex items-center bg-[var(--md-surface-container-low)] p-1 rounded-lg border border-[var(--md-outline-variant)]">
            <button id="btn-view-grid" onclick="setViewMode('grid')" class="p-2 rounded bg-[var(--md-surface-container-lowest)] text-[var(--md-primary)] shadow-sm flex items-center justify-center" title="Grid View">
                <span class="material-symbols-outlined text-sm">grid_view</span>
            </button>
            <button id="btn-view-list" onclick="setViewMode('list')" class="p-2 rounded text-[var(--md-on-surface-variant)] hover:bg-[var(--md-surface-container-high)] flex items-center justify-center" title="List View">
                <span class="material-symbols-outlined text-sm">list</span>
            </button>
        </div>
    </div>

    <!-- Tryout Grid -->
    <div id="tryout-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Cards loaded via JS -->
    </div>

    <!-- Empty state -->
    <div id="tryout-empty" class="hidden">
        <x-empty-state 
            icon='<svg class="w-16 h-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" style="color: var(--md-outline);"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>' 
            title="Belum ada tryout tersedia" 
            description="Coba kembali lagi nanti" 
        />
    </div>

    <!-- Loading state -->
    <div id="tryout-loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div class="rounded-xl p-6 animate-pulse" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline-variant); box-shadow: 0 4px 15px rgba(0,74,198,0.04);">
            <div class="h-5 rounded w-3/4 mb-4" style="background: var(--md-surface-container-high);"></div>
            <div class="h-4 rounded w-1/2 mb-6" style="background: var(--md-surface-container-high);"></div>
            <div class="space-y-3 mb-6"><div class="h-4 rounded w-full" style="background: var(--md-surface-container);"></div><div class="h-4 rounded w-full" style="background: var(--md-surface-container);"></div><div class="h-4 rounded w-full" style="background: var(--md-surface-container);"></div></div>
            <div class="h-10 rounded-lg w-full" style="background: var(--md-surface-container-high);"></div>
        </div>
        <div class="rounded-xl p-6 animate-pulse" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline-variant); box-shadow: 0 4px 15px rgba(0,74,198,0.04);">
            <div class="h-5 rounded w-3/4 mb-4" style="background: var(--md-surface-container-high);"></div>
            <div class="h-4 rounded w-1/2 mb-6" style="background: var(--md-surface-container-high);"></div>
            <div class="space-y-3 mb-6"><div class="h-4 rounded w-full" style="background: var(--md-surface-container);"></div><div class="h-4 rounded w-full" style="background: var(--md-surface-container);"></div><div class="h-4 rounded w-full" style="background: var(--md-surface-container);"></div></div>
            <div class="h-10 rounded-lg w-full" style="background: var(--md-surface-container-high);"></div>
        </div>
        <div class="rounded-xl p-6 animate-pulse" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline-variant); box-shadow: 0 4px 15px rgba(0,74,198,0.04);">
            <div class="h-5 rounded w-3/4 mb-4" style="background: var(--md-surface-container-high);"></div>
            <div class="h-4 rounded w-1/2 mb-6" style="background: var(--md-surface-container-high);"></div>
            <div class="space-y-3 mb-6"><div class="h-4 rounded w-full" style="background: var(--md-surface-container);"></div><div class="h-4 rounded w-full" style="background: var(--md-surface-container);"></div><div class="h-4 rounded w-full" style="background: var(--md-surface-container);"></div></div>
            <div class="h-10 rounded-lg w-full" style="background: var(--md-surface-container-high);"></div>
        </div>
    </div>
</div>

<script>
(function() {
    var allTryouts = [];
    var categories = [];
    var activeCategory = 'all';
    var userTier = 'FREE';
    var viewMode = 'grid'; // 'grid' or 'list'

    window.setViewMode = function(mode) {
        viewMode = mode;
        
        // Update button styles
        var btnGrid = document.getElementById('btn-view-grid');
        var btnList = document.getElementById('btn-view-list');
        
        if (mode === 'grid') {
            btnGrid.className = 'p-2 rounded bg-[var(--md-surface-container-lowest)] text-[var(--md-primary)] shadow-sm flex items-center justify-center';
            btnList.className = 'p-2 rounded text-[var(--md-on-surface-variant)] hover:bg-[var(--md-surface-container-high)] flex items-center justify-center';
        } else {
            btnList.className = 'p-2 rounded bg-[var(--md-surface-container-lowest)] text-[var(--md-primary)] shadow-sm flex items-center justify-center';
            btnGrid.className = 'p-2 rounded text-[var(--md-on-surface-variant)] hover:bg-[var(--md-surface-container-high)] flex items-center justify-center';
        }
        
        renderTryouts();
    };

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

        var html = '<button onclick="filterByCategory(\'all\')" style="' +
            (activeCategory === 'all'
                ? 'background: var(--md-primary-fixed); color: var(--md-primary); border: 1px solid var(--md-primary);'
                : 'background: var(--md-surface-container); color: var(--md-on-surface-variant); border: 1px solid transparent;') +
            '" class="px-4 py-2 rounded-full text-label-md font-medium transition-colors">Semua</button>';

        categories.forEach(function(cat) {
            html += '<button onclick="filterByCategory(\'' + cat + '\')" style="' +
                (activeCategory === cat
                    ? 'background: var(--md-primary-fixed); color: var(--md-primary); border: 1px solid var(--md-primary);'
                    : 'background: var(--md-surface-container); color: var(--md-on-surface-variant); border: 1px solid transparent;') +
                '" class="px-4 py-2 rounded-full text-label-md font-medium transition-colors">' + cat + '</button>';
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
        
        // Update container layout based on viewMode
        if (viewMode === 'grid') {
            container.className = 'grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6';
            
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
                var attemptCount = t.attemptCount || 0;
                var slug = t.slug || t.id;

                // --- Action Button ---
                var actionBtn = '';
                if (isLocked) {
                    actionBtn = '<button style="background: var(--md-surface-variant); color: var(--md-on-surface-variant); opacity: 0.75;" class="w-full py-2.5 px-4 cursor-not-allowed flex items-center justify-center gap-2 text-label-md font-semibold rounded-lg" disabled>' +
                        '<span class="material-symbols-outlined text-lg">lock</span>Premium</button>';
                } else if (prevStatus === 'IN_PROGRESS' || prevStatus === 'STARTED') {
                    actionBtn = '<a href="/tryouts/' + slug + '/exam" style="background: var(--md-primary); color: var(--md-on-primary);" class="block w-full py-2.5 px-4 text-center text-label-md font-semibold rounded-lg hover:opacity-90 transition-opacity">Lanjutkan</a>';
                } else if (prevStatus === 'COMPLETED' || prevStatus === 'SUBMITTED') {
                    actionBtn = '<div class="flex gap-2">' +
                        '<a href="/tryouts/' + slug + '/result/' + prevAttempt.id + '" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline); color: var(--md-on-surface);" class="block flex-1 py-2.5 px-4 text-center text-label-md font-semibold rounded-lg hover:opacity-90 transition-opacity">Lihat Hasil</a>' +
                        '<a href="/tryouts/' + slug + '" style="background: var(--md-primary); color: var(--md-on-primary);" class="block flex-1 py-2.5 px-4 text-center text-label-md font-semibold rounded-lg hover:opacity-90 transition-opacity">Coba Lagi</a>' +
                        '</div>';
                } else {
                    actionBtn = '<a href="/tryouts/' + slug + '" style="background: var(--md-primary); color: var(--md-on-primary);" class="block w-full py-2.5 px-4 text-center text-label-md font-semibold rounded-lg hover:opacity-90 transition-opacity">Mulai</a>';
                }

                // --- Lock Overlay ---
                var lockOverlay = isLocked ? '<div class="absolute inset-0 rounded-xl flex items-center justify-center z-10" style="background: rgba(0,0,0,0.45);"><div class="text-center"><span class="material-symbols-outlined text-3xl mb-2" style="color: #f59e0b;">lock</span><p class="text-white font-medium text-sm">Premium Only</p></div></div>' : '';

                // --- Tier Badge ---
                var tierBadgeHtml = isPremium
                    ? '<span class="px-2 py-1 text-xs font-bold rounded shrink-0" style="background: #fef3c7; color: #92400e;">PREMIUM</span>'
                    : '<span class="px-2 py-1 text-xs font-bold rounded shrink-0" style="background: #dcfce7; color: #166534;">FREE</span>';

                // --- Previous Status Badge ---
                var prevStatusBadge = '';
                if (prevStatus === 'COMPLETED' || prevStatus === 'SUBMITTED') {
                    prevStatusBadge = '<span class="inline-flex items-center gap-1 px-2 py-1 rounded text-label-sm" style="background: #ecfdf5; color: #059669;">Selesai</span>';
                    if (attemptCount > 1) {
                        prevStatusBadge += ' <span class="inline-flex items-center gap-1 px-2 py-1 rounded text-label-sm" style="background: var(--md-surface-container-high); color: var(--md-on-surface-variant);">' + attemptCount + 'x percobaan</span>';
                    }
                } else if (prevStatus === 'IN_PROGRESS' || prevStatus === 'STARTED') {
                    prevStatusBadge = '<span class="inline-flex items-center gap-1 px-2 py-1 rounded text-label-sm" style="background: #fef2f2; color: #dc2626;">Sedang Berjalan</span>';
                }

                return '<div class="rounded-xl p-6 flex flex-col h-full hover:shadow-lg transition-shadow duration-300 relative overflow-hidden" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline-variant); box-shadow: 0 4px 15px rgba(0,74,198,0.04);">' +
                    lockOverlay +
                    '<div class="flex justify-between items-start mb-4">' +
                        '<h2 class="text-headline-sm pr-4" style="color: var(--md-on-surface);">' + (t.title || t.name) + '</h2>' +
                        tierBadgeHtml +
                    '</div>' +
                    (catBadge ? '<div class="mb-6"><span class="inline-block px-3 py-1 rounded-full text-label-sm" style="background: var(--md-surface-container-low); color: var(--md-on-surface-variant);">' + catBadge + '</span></div>' : '') +
                    '<div class="space-y-3 mb-6 flex-grow">' +
                        '<div class="flex justify-between items-center text-body-md" style="color: var(--md-on-surface-variant);"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">schedule</span><span>Durasi</span></div><span class="font-semibold" style="color: var(--md-on-surface);">' + duration + ' menit</span></div>' +
                        '<div class="flex justify-between items-center text-body-md" style="color: var(--md-on-surface-variant);"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">assignment</span><span>Jumlah Soal</span></div><span class="font-semibold" style="color: var(--md-on-surface);">' + questionCount + '</span></div>' +
                        '<div class="flex justify-between items-center text-body-md" style="color: var(--md-on-surface-variant);"><div class="flex items-center gap-2"><span class="material-symbols-outlined text-lg">check_circle</span><span>Passing Score</span></div><span class="font-semibold" style="color: var(--md-on-surface);">' + passingScore + '%</span></div>' +
                    '</div>' +
                    '<div class="mt-auto pt-4">' +
                        (prevStatusBadge ? '<div class="mb-4 flex flex-wrap gap-2">' + prevStatusBadge + '</div>' : '') +
                        actionBtn +
                    '</div>' +
                '</div>';
            }).join('');
            
        } else {
            container.className = 'flex flex-col gap-3';
            
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
                var attemptCount = t.attemptCount || 0;
                var slug = t.slug || t.id;

                // --- Action Button (Compact) ---
                var actionBtn = '';
                if (isLocked) {
                    actionBtn = '<button style="background: var(--md-surface-variant); color: var(--md-on-surface-variant); opacity: 0.75;" class="py-1.5 px-4 cursor-not-allowed flex items-center justify-center gap-1.5 text-label-sm font-semibold rounded-lg" disabled>' +
                        '<span class="material-symbols-outlined text-sm">lock</span>Premium</button>';
                } else if (prevStatus === 'IN_PROGRESS' || prevStatus === 'STARTED') {
                    actionBtn = '<a href="/tryouts/' + slug + '/exam" style="background: var(--md-primary); color: var(--md-on-primary);" class="py-1.5 px-4 text-center text-label-sm font-semibold rounded-lg hover:opacity-90 transition-opacity">Lanjutkan</a>';
                } else if (prevStatus === 'COMPLETED' || prevStatus === 'SUBMITTED') {
                    actionBtn = '<div class="flex gap-2">' +
                        '<a href="/tryouts/' + slug + '/result/' + prevAttempt.id + '" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline); color: var(--md-on-surface);" class="py-1.5 px-3 text-center text-label-sm font-semibold rounded-lg hover:opacity-90 transition-opacity">Hasil</a>' +
                        '<a href="/tryouts/' + slug + '" style="background: var(--md-primary); color: var(--md-on-primary);" class="py-1.5 px-3 text-center text-label-sm font-semibold rounded-lg hover:opacity-90 transition-opacity">Ulang</a>' +
                        '</div>';
                } else {
                    actionBtn = '<a href="/tryouts/' + slug + '" style="background: var(--md-primary); color: var(--md-on-primary);" class="py-1.5 px-4 text-center text-label-sm font-semibold rounded-lg hover:opacity-90 transition-opacity">Mulai</a>';
                }

                // --- Tier Badge ---
                var tierBadgeHtml = isPremium
                    ? '<span class="px-2 py-0.5 text-[10px] font-bold rounded" style="background: #fef3c7; color: #92400e;">PREMIUM</span>'
                    : '<span class="px-2 py-0.5 text-[10px] font-bold rounded" style="background: #dcfce7; color: #166534;">FREE</span>';

                // --- Previous Status Badge ---
                var prevStatusBadge = '';
                if (prevStatus === 'COMPLETED' || prevStatus === 'SUBMITTED') {
                    prevStatusBadge = '<span class="px-2 py-0.5 rounded text-[11px]" style="background: #ecfdf5; color: #059669;">Selesai</span>';
                } else if (prevStatus === 'IN_PROGRESS' || prevStatus === 'STARTED') {
                    prevStatusBadge = '<span class="px-2 py-0.5 rounded text-[11px]" style="background: #fef2f2; color: #dc2626;">Berjalan</span>';
                }

                return '<div class="rounded-lg p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 hover:shadow-md transition-shadow duration-200 relative overflow-hidden" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline-variant); box-shadow: 0 2px 6px rgba(0,74,198,0.02);">' +
                    '<div class="flex items-center gap-3 flex-grow min-w-0">' +
                        (isLocked ? '<div class="flex items-center justify-center w-8 h-8 rounded-full bg-amber-50 text-amber-500 shrink-0"><span class="material-symbols-outlined text-lg">lock</span></div>' : '<div class="flex items-center justify-center w-8 h-8 rounded-full bg-blue-50 text-blue-600 shrink-0"><span class="material-symbols-outlined text-lg">assignment</span></div>') +
                        '<div class="min-w-0 flex-grow">' +
                            '<div class="flex flex-wrap items-center gap-2 mb-1">' +
                                '<h3 class="font-semibold text-body-lg truncate" style="color: var(--md-on-surface);">' + (t.title || t.name) + '</h3>' +
                                tierBadgeHtml +
                                (prevStatusBadge ? prevStatusBadge : '') +
                            '</div>' +
                            '<div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-body-sm" style="color: var(--md-on-surface-variant);">' +
                                (catBadge ? '<span class="px-2 py-0.5 rounded-full bg-[var(--md-surface-container-low)]">' + catBadge + '</span>' : '') +
                                '<span>•</span>' +
                                '<span>' + duration + ' menit</span>' +
                                '<span>•</span>' +
                                '<span>' + questionCount + ' soal</span>' +
                                '<span>•</span>' +
                                '<span>Passing: ' + passingScore + '%</span>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="shrink-0 self-end sm:self-center">' +
                        actionBtn +
                    '</div>' +
                '</div>';
            }).join('');
        }
    }
    }
})();
</script>
@endsection