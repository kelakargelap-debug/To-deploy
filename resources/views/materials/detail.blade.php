@extends('app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Material title -->
        <x-page-header title="Loading..." breadcrumb='<a href="/materials">Materi</a><span class="breadcrumb-sep">/</span><span>Detail</span>'>
            <x-slot:actions>
                <div class="flex items-center gap-2">
                    <span id="material-tier-badge" class="badge badge-free">FREE</span>
                    <span id="material-category-badge" class="badge badge-neutral hidden"></span>
                </div>
            </x-slot:actions>
        </x-page-header>

        <!-- Premium gate message -->
        <div id="premium-gate" class="hidden mb-6 premium-gate animate-fade-in-up">
            <svg class="premium-gate-icon mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            <h3 class="premium-gate-title mt-3">Materi Premium</h3>
            <p class="premium-gate-desc mt-1">Upgrade ke membership Premium untuk mengakses materi ini</p>
            <a href="{{ route('profile') }}" class="btn-primary mt-4 inline-flex">Upgrade ke Premium</a>
        </div>

        <!-- Material content area -->
        <div id="material-content" class="card mb-6 animate-fade-in-up">
            <div id="material-body" class="prose dark:prose-invert max-w-none text-[var(--text-primary)]">
                <!-- Content loaded via JS -->
                <div class="skeleton">
                    <div class="skeleton-heading w-1/2"></div>
                    <div class="skeleton-text w-full mt-4"></div>
                    <div class="skeleton-text w-full"></div>
                    <div class="skeleton-text w-3/4"></div>
                </div>
            </div>
        </div>

        <!-- Mark as completed button -->
        <div id="mark-complete-area" class="mt-4 flex justify-center">
            <button id="mark-complete-btn" onclick="markAsComplete()" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span id="mark-complete-text">Tandai Selesai</span>
            </button>
        </div>
    </div>

    <script>
        (function () {
            var materialSlug = '';
            var materialId = null;
            var isCompleted = false;

            // Parse route parameter
            materialSlug = '{{ $slug }}';

            // Copy protection
            function disableCopy(e) { e.preventDefault(); return false; }
            document.addEventListener('copy', disableCopy);
            document.addEventListener('cut', disableCopy);
            document.addEventListener('contextmenu', function (e) { e.preventDefault(); });
            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'c' || e.key === 'C' || e.key === 'u' || e.key === 'U')) {
                    e.preventDefault();
                    return false;
                }
            });

            function loadMaterial() {
                apiFetch('/materials/' + materialSlug).then(function (data) {
                    var m = data.material || data;
                    if (data.material) {
                        m.category_name = data.categoryName;
                        m.is_completed = !!data.completedAt;
                    }
                    materialId = m.id;
                    isCompleted = m.is_completed || m.completed || false;
                    var tier = m.required_tier || m.tier || m.membership_tier || 'FREE';
                    var userTier = (window.SKB && window.SKB.user) ? window.SKB.user.membership_tier : 'FREE';

                    document.querySelector('.page-header-title').textContent = m.title || m.name || '';
                    var tierBadge = document.getElementById('material-tier-badge');
                    tierBadge.textContent = tier.toUpperCase();
                    tierBadge.className = 'badge ' + (tier === 'PREMIUM' ? 'badge-premium' : 'badge-free');

                    var catName = m.category_name || (m.category && m.category.name) || '';
                    if (catName) {
                        var catBadge = document.getElementById('material-category-badge');
                        catBadge.textContent = catName;
                        catBadge.classList.remove('hidden');
                    }

                    // Premium gate
                    if (tier === 'PREMIUM' && userTier !== 'PREMIUM') {
                        document.getElementById('premium-gate').classList.remove('hidden');
                        document.getElementById('material-content').classList.add('hidden');
                        document.getElementById('mark-complete-area').classList.add('hidden');
                        return;
                    }

                    // Render content (HTML)
                    var content = m.content || m.body || m.html_content || '';
                    document.getElementById('material-body').innerHTML = content;

                    // Update complete button
                    if (isCompleted) {
                        document.getElementById('mark-complete-btn').classList.add('btn-secondary');
                        document.getElementById('mark-complete-btn').classList.remove('btn-primary');
                        document.getElementById('mark-complete-text').textContent = 'Sudah Selesai';
                        document.getElementById('mark-complete-btn').disabled = true;
                    }
                }).catch(function (err) {
                    console.error('Failed to load material:', err);
                    alert('Gagal memuat materi: ' + err.message);
                    navigateTo('/materials');
                });
            }

            window.markAsComplete = function () {
                var btn = document.getElementById('mark-complete-btn');
                btn.disabled = true;
                document.getElementById('mark-complete-text').textContent = 'Menyimpan...';

                apiFetch('/materials/' + materialSlug + '/complete', {
                    method: 'POST'
                }).then(function () {
                    document.getElementById('mark-complete-btn').classList.remove('btn-primary');
                    document.getElementById('mark-complete-btn').classList.add('btn-secondary');
                    document.getElementById('mark-complete-text').textContent = 'Sudah Selesai';
                    btn.disabled = true;
                    isCompleted = true;
                }).catch(function (err) {
                    console.error('Failed to mark complete:', err);
                    alert('Gagal: ' + err.message);
                    btn.disabled = false;
                    document.getElementById('mark-complete-text').textContent = 'Tandai Selesai';
                });
            };

            loadMaterial();
        })();
    </script>
@endsection