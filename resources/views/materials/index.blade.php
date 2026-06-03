@extends('app')

@section('content')
<div class="p-6 max-w-6xl mx-auto">
    <!-- Page header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Materi Belajar</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih materi yang ingin kamu pelajari</p>
    </div>

    <!-- Category filter chips -->
    <div id="material-category-filters" class="mb-6 flex flex-wrap gap-2">
        <button onclick="filterMaterials('all')" class="filter-chip active" data-category="all">Semua</button>
    </div>

    <!-- Material cards grid -->
    <div id="material-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <!-- Loading state -->
        <div class="card animate-pulse"><div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-3"></div><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div></div>
        <div class="card animate-pulse"><div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-3"></div><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div></div>
        <div class="card animate-pulse"><div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-3"></div><div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div></div>
    </div>

    <!-- Empty state -->
    <div id="material-empty" class="hidden">
        <div class="text-center py-12">
            <svg class="w-16 h-16 text-gray-300 dark:text-gray-600 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.168 18.477 17.683 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300">Belum ada materi tersedia</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Coba kembali lagi nanti</p>
        </div>
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
    var allMaterials = [];
    var categories = [];
    var activeCategory = 'all';
    var userTier = 'FREE';

    apiFetch('/auth/me').then(function(user) {
        window.SKB.user = user;
        userTier = user.membership_tier || 'FREE';
        window.SKB.isAdmin = user.role === 'ADMIN' || user.role === 'SUPERADMIN';
        window.SKB.isSuperAdmin = user.role === 'SUPERADMIN';
    }).catch(function() {}).finally(function() {
        loadMaterials();
    });

    function loadMaterials() {
        apiFetch('/materials').then(function(data) {
            allMaterials = data.data || data || [];
            var catSet = {};
            allMaterials.forEach(function(m) {
                var catName = m.categoryName || (m.category && m.category.name) || m.category_name || '';
                if (catName) catSet[catName] = true;
            });
            categories = Object.keys(catSet);
            renderCategoryFilters();
            renderMaterials();
            // Remove loading placeholders
            document.getElementById('material-grid').innerHTML = '';
        }).catch(function(err) {
            console.error('Failed to load materials:', err);
            document.getElementById('material-grid').innerHTML = '';
            document.getElementById('material-empty').classList.remove('hidden');
        });
    }

    function renderCategoryFilters() {
        var container = document.getElementById('material-category-filters');
        if (!container) return;
        var html = '<button onclick="filterMaterials(\'all\')" class="filter-chip ' + (activeCategory === 'all' ? 'active' : '') + '" data-category="all">Semua</button>';
        categories.forEach(function(cat) {
            html += '<button onclick="filterMaterials(\'' + cat + '\')" class="filter-chip ' + (activeCategory === cat ? 'active' : '') + '" data-category="' + cat + '">' + cat + '</button>';
        });
        container.innerHTML = html;
    }

    window.filterMaterials = function(cat) {
        activeCategory = cat;
        renderCategoryFilters();
        renderMaterials();
    };

    function renderMaterials() {
        var filtered = activeCategory === 'all' ? allMaterials : allMaterials.filter(function(m) {
            var catName = m.categoryName || (m.category && m.category.name) || m.category_name || '';
            return catName === activeCategory;
        });

        var container = document.getElementById('material-grid');
        var emptyState = document.getElementById('material-empty');

        if (filtered.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }

        emptyState.classList.add('hidden');
        container.innerHTML = filtered.map(function(m) {
            var tier = m.required_tier || m.tier || m.membership_tier || 'FREE';
            var isPremium = tier === 'PREMIUM';
            var isLocked = isPremium && userTier !== 'PREMIUM';
            var catName = m.categoryName || (m.category && m.category.name) || m.category_name || '';
            var isCompleted = !!m.completedAt || m.is_completed || m.completed;
            var completionStatus = isCompleted ? 'Selesai' : 'Belum Selesai';
            var completionBadge = isCompleted ? 'badge-success' : 'badge-free';
            var slug = m.slug || m.id;

            var lockOverlay = isLocked ? '<div class="absolute inset-0 bg-gray-900/50 dark:bg-gray-900/70 rounded-xl flex items-center justify-center z-10"><div class="text-center"><svg class="w-8 h-8 text-amber-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75"/></svg><p class="text-white font-medium text-sm">Premium Only</p></div></div>' : '';

            var tierBadge = isPremium
                ? '<span class="badge badge-premium">PREMIUM</span>'
                : '<span class="badge badge-free">FREE</span>';

            var actionBtn = isLocked
                ? '<button class="btn-secondary w-full opacity-75 cursor-not-allowed" disabled>Premium Only</button>'
                : '<a href="/materials/' + slug + '" class="btn-primary w-full text-center">Baca Materi</a>';

            return '<div class="card relative overflow-hidden">' +
                lockOverlay +
                '<div class="flex items-center justify-between mb-2">' +
                    '<h3 class="font-semibold text-gray-900 dark:text-gray-100">' + (m.title || m.name) + '</h3>' +
                    tierBadge +
                '</div>' +
                (catName ? '<p class="text-xs text-gray-500 dark:text-gray-400 mb-2"><span class="bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded">' + catName + '</span></p>' : '') +
                '<div class="mb-3">' +
                    '<span class="badge ' + completionBadge + '">' + completionStatus + '</span>' +
                '</div>' +
                actionBtn +
            '</div>';
        }).join('');
    }
})();
</script>
@endsection