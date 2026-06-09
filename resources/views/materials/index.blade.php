@extends('app')

@section('content')
<div class="max-w-7xl mx-auto w-full">
    <!-- Header Section -->
    <div class="mb-8">
        <h1 class="text-display-lg mb-2" style="color: var(--md-on-surface);">Materi Belajar</h1>
        <p class="text-body-md" style="color: var(--md-on-surface-variant);">Pilih materi yang ingin kamu pelajari</p>
    </div>

    <!-- Category filter chips -->
    <div id="material-category-filters" class="flex flex-wrap gap-3 mb-8">
        <button onclick="filterMaterials('all')" class="filter-chip active" data-category="all">Semua</button>
    </div>

    <!-- Material cards grid -->
    <div id="material-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Loading state -->
        <div class="rounded-xl p-6 animate-pulse" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline-variant); box-shadow: 0 4px 15px rgba(0,74,198,0.04);">
            <div class="h-5 rounded w-3/4 mb-4" style="background: var(--md-surface-container-high);"></div>
            <div class="h-4 rounded w-1/2 mb-6" style="background: var(--md-surface-container-high);"></div>
            <div class="h-6 rounded w-1/3 mb-4" style="background: var(--md-surface-container);"></div>
            <div class="h-10 rounded-lg w-full" style="background: var(--md-surface-container-high);"></div>
        </div>
        <div class="rounded-xl p-6 animate-pulse" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline-variant); box-shadow: 0 4px 15px rgba(0,74,198,0.04);">
            <div class="h-5 rounded w-3/4 mb-4" style="background: var(--md-surface-container-high);"></div>
            <div class="h-4 rounded w-1/2 mb-6" style="background: var(--md-surface-container-high);"></div>
            <div class="h-6 rounded w-1/3 mb-4" style="background: var(--md-surface-container);"></div>
            <div class="h-10 rounded-lg w-full" style="background: var(--md-surface-container-high);"></div>
        </div>
        <div class="rounded-xl p-6 animate-pulse" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline-variant); box-shadow: 0 4px 15px rgba(0,74,198,0.04);">
            <div class="h-5 rounded w-3/4 mb-4" style="background: var(--md-surface-container-high);"></div>
            <div class="h-4 rounded w-1/2 mb-6" style="background: var(--md-surface-container-high);"></div>
            <div class="h-6 rounded w-1/3 mb-4" style="background: var(--md-surface-container);"></div>
            <div class="h-10 rounded-lg w-full" style="background: var(--md-surface-container-high);"></div>
        </div>
    </div>

    <!-- Empty state -->
    <div id="material-empty" class="hidden">
        <x-empty-state 
            icon='<svg class="w-16 h-16 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1" style="color: var(--md-outline);"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.168 18.477 17.683 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>' 
            title="Belum ada materi tersedia" 
            description="Coba kembali lagi nanti" 
        />
    </div>
</div>

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
        }).catch(function(err) {
            console.error('Failed to load materials:', err);
            var grid = document.getElementById('material-grid');
            var empty = document.getElementById('material-empty');
            if (grid) grid.innerHTML = '';
            if (empty) empty.classList.remove('hidden');
        });
    }

    function renderCategoryFilters() {
        var container = document.getElementById('material-category-filters');
        if (!container) return;

        var html = '<button onclick="filterMaterials(\'all\')" style="' +
            (activeCategory === 'all'
                ? 'background: var(--md-primary-fixed); color: var(--md-primary); border: 1px solid var(--md-primary);'
                : 'background: var(--md-surface-container); color: var(--md-on-surface-variant); border: 1px solid transparent;') +
            '" class="px-4 py-2 rounded-full text-label-md font-medium transition-colors">Semua</button>';

        categories.forEach(function(cat) {
            html += '<button onclick="filterMaterials(\'' + cat + '\')" style="' +
                (activeCategory === cat
                    ? 'background: var(--md-primary-fixed); color: var(--md-primary); border: 1px solid var(--md-primary);'
                    : 'background: var(--md-surface-container); color: var(--md-on-surface-variant); border: 1px solid transparent;') +
                '" class="px-4 py-2 rounded-full text-label-md font-medium transition-colors">' + cat + '</button>';
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
            if (container) container.innerHTML = '';
            if (emptyState) emptyState.classList.remove('hidden');
            return;
        }

        if (emptyState) emptyState.classList.add('hidden');
        if (container) container.innerHTML = filtered.map(function(m) {
            var tier = m.required_tier || m.tier || m.membership_tier || 'FREE';
            var isPremium = tier === 'PREMIUM';
            var isLocked = isPremium && userTier !== 'PREMIUM';
            var catName = m.categoryName || (m.category && m.category.name) || m.category_name || '';
            var isCompleted = !!m.completedAt || m.is_completed || m.completed;
            var slug = m.slug || m.id;

            // --- Lock Overlay ---
            var lockOverlay = isLocked ? '<div class="absolute inset-0 rounded-xl flex items-center justify-center z-10" style="background: rgba(0,0,0,0.45);"><div class="text-center"><span class="material-symbols-outlined text-3xl mb-2" style="color: #f59e0b;">lock</span><p class="text-white font-medium text-sm">Premium Only</p></div></div>' : '';

            // --- Tier Badge ---
            var tierBadge = isPremium
                ? '<span class="px-2 py-1 text-xs font-bold rounded shrink-0" style="background: #fef3c7; color: #92400e;">PREMIUM</span>'
                : '<span class="px-2 py-1 text-xs font-bold rounded shrink-0" style="background: #dcfce7; color: #166534;">FREE</span>';

            // --- Completion Badge ---
            var completionBadgeHtml = isCompleted
                ? '<span class="inline-flex items-center gap-1 px-2 py-1 rounded text-label-sm" style="background: #ecfdf5; color: #059669;"><span class="material-symbols-outlined text-sm">check_circle</span>Selesai</span>'
                : '<span class="inline-flex items-center gap-1 px-2 py-1 rounded text-label-sm" style="background: var(--md-surface-container-high); color: var(--md-on-surface-variant);">Belum Selesai</span>';

            // --- Action Button ---
            var actionText = isCompleted ? 'Baca Lagi' : 'Baca Materi';
            var actionBtn = isLocked
                ? '<button style="background: var(--md-surface-variant); color: var(--md-on-surface-variant); opacity: 0.75;" class="w-full py-2.5 px-4 cursor-not-allowed flex items-center justify-center gap-2 text-label-md font-semibold rounded-lg" disabled><span class="material-symbols-outlined text-lg">lock</span>Premium</button>'
                : '<a href="/materials/' + slug + '" style="background: var(--md-primary); color: var(--md-on-primary);" class="block w-full py-2.5 px-4 text-center text-label-md font-semibold rounded-lg hover:opacity-90 transition-opacity">' + actionText + '</a>';

            // --- Description ---
            var desc = m.description || m.excerpt || '';
            var descHtml = desc
                ? '<p class="text-body-md mb-6 flex-grow" style="color: var(--md-on-surface-variant); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">' + desc + '</p>'
                : '<div class="mb-6 flex-grow"></div>';

            // --- Card ---
            return '<div class="rounded-xl p-6 flex flex-col h-full hover:shadow-lg transition-shadow duration-300 relative overflow-hidden" style="background: var(--md-surface-container-lowest); border: 1px solid var(--md-outline-variant); box-shadow: 0 4px 15px rgba(0,74,198,0.04);">' +
                lockOverlay +
                '<div class="flex justify-between items-start mb-4">' +
                    '<h2 class="text-headline-sm pr-4" style="color: var(--md-on-surface);">' + (m.title || m.name) + '</h2>' +
                    tierBadge +
                '</div>' +
                (catName ? '<div class="mb-4"><span class="inline-block px-3 py-1 rounded-full text-label-sm" style="background: var(--md-surface-container-low); color: var(--md-on-surface-variant);">' + catName + '</span></div>' : '') +
                descHtml +
                '<div class="mt-auto pt-4" style="border-top: 1px solid var(--md-outline-variant);">' +
                    '<div class="mb-4 flex flex-wrap gap-2">' + completionBadgeHtml + '</div>' +
                    actionBtn +
                '</div>' +
            '</div>';
        }).join('');
    }
})();
</script>
@endsection