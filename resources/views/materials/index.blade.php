@extends('app')

@section('content')
<div class="max-w-7xl mx-auto pb-12">
    <!-- Header Section -->
    <div class="mb-10 text-center md:text-left">
        <h1 class="text-display-lg font-bold text-[var(--text-primary)] mb-3">Materi Belajar</h1>
        <p class="text-body-lg text-[var(--text-secondary)] max-w-2xl">Tingkatkan pemahaman Anda dengan materi komprehensif yang dirancang khusus.</p>
    </div>

    <!-- Category filter chips -->
    <div class="flex items-center gap-3 mb-8 overflow-x-auto pb-2 scrollbar-hide">
        <div class="flex items-center gap-2" id="material-category-filters">
            <!-- Loaded via JS -->
            <div class="h-10 w-24 bg-[var(--bg-surface-hover)] animate-pulse rounded-full"></div>
            <div class="h-10 w-32 bg-[var(--bg-surface-hover)] animate-pulse rounded-full"></div>
        </div>
    </div>

    <!-- Material cards grid -->
    <div id="material-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Loading state -->
        <div class="bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-2xl p-6 animate-pulse shadow-sm">
            <div class="w-12 h-12 bg-[var(--bg-surface-hover)] rounded-xl mb-6"></div>
            <div class="h-6 bg-[var(--bg-surface-hover)] rounded w-3/4 mb-3"></div>
            <div class="h-4 bg-[var(--bg-surface-hover)] rounded w-full mb-6"></div>
            <div class="flex items-center gap-2 mb-6">
                <div class="h-6 w-16 bg-[var(--bg-surface-hover)] rounded-full"></div>
                <div class="h-6 w-20 bg-[var(--bg-surface-hover)] rounded-full"></div>
            </div>
            <div class="h-12 bg-[var(--bg-surface-hover)] rounded-xl w-full mt-auto"></div>
        </div>
        <div class="bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-2xl p-6 animate-pulse shadow-sm hidden md:block">
            <div class="w-12 h-12 bg-[var(--bg-surface-hover)] rounded-xl mb-6"></div>
            <div class="h-6 bg-[var(--bg-surface-hover)] rounded w-3/4 mb-3"></div>
            <div class="h-4 bg-[var(--bg-surface-hover)] rounded w-full mb-6"></div>
            <div class="flex items-center gap-2 mb-6">
                <div class="h-6 w-16 bg-[var(--bg-surface-hover)] rounded-full"></div>
                <div class="h-6 w-20 bg-[var(--bg-surface-hover)] rounded-full"></div>
            </div>
            <div class="h-12 bg-[var(--bg-surface-hover)] rounded-xl w-full mt-auto"></div>
        </div>
        <div class="bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-2xl p-6 animate-pulse shadow-sm hidden lg:block">
            <div class="w-12 h-12 bg-[var(--bg-surface-hover)] rounded-xl mb-6"></div>
            <div class="h-6 bg-[var(--bg-surface-hover)] rounded w-3/4 mb-3"></div>
            <div class="h-4 bg-[var(--bg-surface-hover)] rounded w-full mb-6"></div>
            <div class="flex items-center gap-2 mb-6">
                <div class="h-6 w-16 bg-[var(--bg-surface-hover)] rounded-full"></div>
                <div class="h-6 w-20 bg-[var(--bg-surface-hover)] rounded-full"></div>
            </div>
            <div class="h-12 bg-[var(--bg-surface-hover)] rounded-xl w-full mt-auto"></div>
        </div>
    </div>

    <!-- Empty state -->
    <div id="material-empty" class="hidden py-16">
        <div class="flex flex-col items-center justify-center text-center">
            <div class="w-24 h-24 bg-[var(--bg-surface-hover)] rounded-full flex items-center justify-center mb-6">
                <span class="material-symbols-outlined text-5xl text-[var(--text-muted)]">menu_book</span>
            </div>
            <h3 class="text-headline-sm font-headline-sm text-[var(--text-primary)] mb-2">Belum ada materi</h3>
            <p class="text-body-md text-[var(--text-secondary)] max-w-md mx-auto">Materi untuk kategori ini belum tersedia. Silakan pilih kategori lain atau periksa kembali nanti.</p>
            <button onclick="filterMaterials('all')" class="mt-8 px-6 py-2.5 rounded-lg border border-[var(--border-default)] text-[var(--text-secondary)] font-label-md hover:bg-[var(--bg-surface-hover)] transition-all">Lihat Semua Materi</button>
        </div>
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
            
            // Extract unique categories
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

        var html = `<button onclick="filterMaterials('all')" class="px-5 py-2.5 rounded-full text-label-md font-bold transition-all whitespace-nowrap border ${activeCategory === 'all' ? 'bg-[var(--primary)] text-white border-[var(--primary)] shadow-sm' : 'bg-[var(--bg-surface)] text-[var(--text-secondary)] border-[var(--border-default)] hover:bg-[var(--bg-surface-hover)]'}">Semua Materi</button>`;

        categories.forEach(function(cat) {
            var isActive = activeCategory === cat;
            html += `<button onclick="filterMaterials('${cat}')" class="px-5 py-2.5 rounded-full text-label-md font-bold transition-all whitespace-nowrap border ${isActive ? 'bg-[var(--primary)] text-white border-[var(--primary)] shadow-sm' : 'bg-[var(--bg-surface)] text-[var(--text-secondary)] border-[var(--border-default)] hover:bg-[var(--bg-surface-hover)]'}">${cat}</button>`;
        });

        container.innerHTML = html;
    }

    window.filterMaterials = function(cat) {
        activeCategory = cat;
        renderCategoryFilters();
        renderMaterials();
    };

    function renderMaterials() {
        var grid = document.getElementById('material-grid');
        var empty = document.getElementById('material-empty');
        if (!grid || !empty) return;

        var filtered = allMaterials;
        if (activeCategory !== 'all') {
            filtered = allMaterials.filter(function(m) {
                var catName = m.categoryName || (m.category && m.category.name) || m.category_name || '';
                return catName === activeCategory;
            });
        }

        if (filtered.length === 0) {
            grid.innerHTML = '';
            empty.classList.remove('hidden');
            return;
        }

        empty.classList.add('hidden');
        var html = '';

        filtered.forEach(function(m) {
            var catName = m.categoryName || (m.category && m.category.name) || m.category_name || '-';
            var isPremium = (m.required_tier || m.tier || m.membership_tier) === 'PREMIUM';
            var canAccess = !isPremium || userTier === 'PREMIUM' || window.SKB.isAdmin;
            var isCompleted = m.is_completed || m.completed || false;
            
            var btnText = isCompleted ? 'Pelajari Ulang' : (canAccess ? 'Mulai Belajar' : 'Upgrade Premium');
            var btnIcon = isCompleted ? 'replay' : (canAccess ? 'auto_stories' : 'lock');
            
            var btnClass = canAccess 
                ? 'w-full px-4 py-3 rounded-xl bg-[var(--primary-subtle)] text-[var(--primary)] font-bold text-label-md hover:bg-[var(--primary)] hover:text-white transition-all flex items-center justify-center gap-2 group-hover:shadow-md'
                : 'w-full px-4 py-3 rounded-xl bg-[var(--warning)]/10 text-[var(--warning)] font-bold text-label-md hover:bg-[var(--warning)] hover:text-white transition-all flex items-center justify-center gap-2';

            var titleHtml = m.title ? m.title.replace(/'/g, "&#39;") : '-';
            
            var iconClass = isCompleted ? 'bg-[var(--success-subtle)] text-[var(--success)]' : 'bg-[var(--bg-default)] text-[var(--primary)] border border-[var(--border-default)]';
            var iconSymbol = isCompleted ? 'check_circle' : 'menu_book';

            html += `
                <div class="group bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-2xl p-6 shadow-[0_4px_15px_rgba(0,74,198,0.04)] hover:shadow-xl transition-all duration-300 flex flex-col hover:-translate-y-1 relative overflow-hidden">
                    
                    ${isPremium ? '<div class="absolute top-0 right-0 w-16 h-16 overflow-hidden"><div class="absolute top-3 -right-6 bg-gradient-to-r from-[var(--warning)] to-amber-600 text-white text-[10px] font-bold tracking-wider uppercase py-1 px-8 rotate-45 shadow-sm text-center">PREMIUM</div></div>' : ''}

                    <div class="flex items-start gap-4 mb-5">
                        <div class="w-14 h-14 rounded-xl flex items-center justify-center shadow-sm shrink-0 ${iconClass}">
                            <span class="material-symbols-outlined text-[28px]">${iconSymbol}</span>
                        </div>
                        <div>
                            <span class="inline-block px-2.5 py-1 bg-[var(--bg-surface-hover)] text-[var(--text-secondary)] text-[10px] font-bold tracking-wider uppercase rounded-md mb-2 border border-[var(--border-default)]">${catName}</span>
                            <h3 class="text-headline-sm font-headline-sm text-[var(--text-primary)] group-hover:text-[var(--primary)] transition-colors line-clamp-2 leading-tight">${titleHtml}</h3>
                        </div>
                    </div>
                    
                    <p class="text-body-md text-[var(--text-secondary)] line-clamp-3 mb-8 flex-1">
                        Pelajari materi lengkap tentang ${titleHtml}. ${isCompleted ? 'Anda telah menyelesaikan materi ini.' : 'Tingkatkan pemahaman Anda untuk persiapan SKB.'}
                    </p>
                    
                    <button onclick="navigateTo('/materials/${m.slug}')" class="${btnClass}">
                        <span class="material-symbols-outlined text-[20px]">${btnIcon}</span>
                        ${btnText}
                    </button>
                </div>
            `;
        });

        grid.innerHTML = html;
    }
})();
</script>
@endsection