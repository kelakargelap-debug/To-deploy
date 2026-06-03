@extends('app')

@section('content')
    <div class="p-6 max-w-4xl mx-auto">
        <!-- Material title -->
        <div class="mb-6">
            <a href="/materials"
                class="text-blue-600 dark:text-blue-400 hover:underline text-sm flex items-center gap-2 mb-4">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L11.25 15l4.5-4.5" />
                </svg>
                Kembali ke Daftar Materi
            </a>
            <h1 id="material-title" class="text-2xl font-bold text-gray-900 dark:text-gray-100">Loading...</h1>
            <div class="flex items-center gap-2 mt-2">
                <span id="material-tier-badge" class="badge badge-free">FREE</span>
                <span id="material-category-badge" class="text-xs text-gray-500 dark:text-gray-400"></span>
            </div>
        </div>

        <!-- Premium gate message -->
        <div id="premium-gate"
            class="hidden mb-6 p-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-xl text-center">
            <svg class="w-12 h-12 text-amber-500 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200">Materi Premium</h3>
            <p class="text-sm text-amber-600 dark:text-amber-400 mt-1">Upgrade ke membership Premium untuk mengakses materi
                ini</p>
        </div>

        <!-- Material content area -->
        <div id="material-content" class="card">
            <div id="material-body" class="text-gray-900 dark:text-gray-100 leading-relaxed">
                <!-- Content loaded via JS -->
                <div class="text-center py-8">
                    <svg class="w-8 h-8 text-gray-400 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mt-2">Memuat materi...</p>
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

                    document.getElementById('material-title').textContent = m.title || m.name || '';
                    var tierBadge = document.getElementById('material-tier-badge');
                    tierBadge.textContent = tier.toUpperCase();
                    tierBadge.className = 'badge ' + (tier === 'PREMIUM' ? 'badge-premium' : 'badge-free');

                    var catName = m.category_name || (m.category && m.category.name) || '';
                    document.getElementById('material-category-badge').textContent = catName ? 'Kategori: ' + catName : '';

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