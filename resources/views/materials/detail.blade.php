@extends('app')

@section('content')
<div class="max-w-7xl mx-auto pb-12">
    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h2 id="page-header-title" class="text-headline-md font-headline-md text-[var(--text-primary)]">Loading...</h2>
            <div class="flex items-center gap-3 mt-2">
                <span id="material-category-badge" class="text-label-md text-[var(--text-secondary)]">Kategori</span>
                <span class="text-[var(--text-muted)]">•</span>
                <span id="material-tier-badge" class="badge badge-free">FREE</span>
            </div>
        </div>
        <div class="flex items-center bg-[var(--bg-surface-hover)] p-1 rounded-lg border border-[var(--border-default)] ml-auto sm:ml-0">
            <button onclick="navigateTo('{{ route('materials') }}')" class="p-2 rounded text-[var(--text-secondary)] hover:bg-[var(--bg-default)] hover:text-[var(--primary)] flex items-center justify-center transition-all" title="Kembali ke Daftar">
                <span class="material-symbols-outlined text-sm">list</span> <span class="ml-2 text-sm font-medium">Daftar Materi</span>
            </button>
        </div>
    </div>

    <!-- Data Table Card / Main Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl shadow-sm overflow-hidden min-h-[600px]">
        
        <!-- Premium gate message -->
        <div id="premium-gate" class="hidden lg:col-span-4 p-12 flex flex-col items-center justify-center text-center animate-fade-in-up">
            <svg class="w-16 h-16 text-[var(--warning)] mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            <h3 class="text-2xl font-bold text-[var(--text-primary)] mt-3">Materi Premium</h3>
            <p class="text-[var(--text-secondary)] mt-2 max-w-md">Upgrade ke membership Premium untuk mengakses materi eksklusif ini dan tingkatkan peluang lulus SKB Anda.</p>
            <a href="{{ route('profile') }}" class="btn-primary mt-6 inline-flex px-8 py-3">Upgrade ke Premium</a>
        </div>

        <!-- Left: Paywall Content (E-book) -->
        <div id="material-content" class="lg:col-span-3 relative flex flex-col p-8 border-b lg:border-b-0 lg:border-r border-[var(--border-default)]">
            <div id="ebook-container" class="w-full max-w-3xl mx-auto flex flex-col gap-8 text-left flex-1">
                
                <!-- Skeleton Loader -->
                <div id="content-loading" class="w-full animate-pulse space-y-6 pt-4">
                    <div class="h-8 bg-[var(--bg-surface-hover)] rounded w-3/4"></div>
                    <div class="space-y-3">
                        <div class="h-4 bg-[var(--bg-surface-hover)] rounded w-full"></div>
                        <div class="h-4 bg-[var(--bg-surface-hover)] rounded w-full"></div>
                        <div class="h-4 bg-[var(--bg-surface-hover)] rounded w-5/6"></div>
                    </div>
                </div>

                <div id="content-display" class="hidden flex-1 flex flex-col">
                    <div class="flex items-center justify-between border-b border-[var(--border-default)] pb-6 mb-6">
                        <div>
                            <h3 id="page-title" class="text-headline-md font-headline-md text-[var(--text-primary)]">-</h3>
                        </div>
                        <span id="page-indicator" class="text-[var(--primary)] font-label-md bg-[var(--primary-subtle)] px-3 py-1 rounded-full whitespace-nowrap">Halaman 1 dari 1</span>
                    </div>

                    <article id="material-body" class="prose dark:prose-invert max-w-none text-[var(--text-primary)] flex-1 text-body-lg leading-relaxed">
                        <!-- Content loaded via JS -->
                    </article>

                    <!-- Pagination Navigation -->
                    <div class="mt-8 pt-8 border-t border-[var(--border-default)] flex items-center justify-between">
                        <button id="btn-prev-page" onclick="prevPage()" class="flex items-center gap-2 px-4 py-2 rounded-lg border border-[var(--border-default)] text-[var(--text-secondary)] hover:bg-[var(--bg-surface-hover)] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <span class="material-symbols-outlined">chevron_left</span>
                            <span class="font-label-md hidden sm:inline">Halaman Sebelumnya</span>
                        </button>
                        
                        <div id="page-dots" class="flex items-center gap-2">
                            <!-- Dots loaded via JS -->
                        </div>
                        
                        <button id="btn-next-page" onclick="nextPage()" class="flex items-center gap-2 px-6 py-2 rounded-lg bg-[var(--primary)] text-white hover:opacity-90 transition-opacity">
                            <span id="btn-next-text" class="font-label-md">Halaman Berikutnya</span>
                            <span class="material-symbols-outlined" id="btn-next-icon">chevron_right</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Curriculum Sidebar -->
        <div id="curriculum-sidebar" class="lg:col-span-1 bg-[var(--bg-default)] p-6 flex flex-col">
            <h4 class="text-label-md font-label-md font-bold text-[var(--text-primary)] mb-4 uppercase tracking-wider">Materi Terkait</h4>
            
            <div id="progress-card" class="mb-6 p-4 bg-[var(--primary-subtle)] border border-[var(--primary)] rounded-xl hidden">
                <div class="flex justify-between items-center mb-3">
                    <span class="text-sm font-bold text-[var(--primary-hover)]">Progress Belajar</span>
                    <span id="progress-text" class="text-sm font-bold text-[var(--primary-hover)]">100%</span>
                </div>
                <div class="w-full h-2 bg-white rounded-full overflow-hidden">
                    <div id="progress-bar" class="h-full bg-[var(--primary)] rounded-full transition-all duration-500" style="width: 100%;"></div>
                </div>
                <p id="completion-status" class="mt-3 text-xs text-center font-semibold text-[var(--success)]">Sudah Selesai</p>
            </div>

            <div class="flex-1 overflow-y-auto pr-2">
                <ul id="curriculum-list" class="space-y-2">
                    <!-- Loaded via JS -->
                    <div class="animate-pulse space-y-2">
                        <div class="h-12 bg-[var(--bg-surface-hover)] rounded"></div>
                        <div class="h-12 bg-[var(--bg-surface-hover)] rounded"></div>
                    </div>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        var materialSlug = '{{ $slug }}';
        var materialId = null;
        var isCompleted = false;
        var currentCategoryId = null;
        
        // Pagination state
        var contentPages = [];
        var currentPageIndex = 0;

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
                currentCategoryId = m.category_id;
                isCompleted = m.is_completed || m.completed || false;
                
                var tier = m.required_tier || m.tier || m.membership_tier || 'FREE';
                var userTier = (window.SKB && window.SKB.user) ? window.SKB.user.membership_tier : 'FREE';

                document.getElementById('page-header-title').textContent = m.title || m.name || '';
                document.getElementById('page-title').textContent = m.title || m.name || '';
                
                var tierBadge = document.getElementById('material-tier-badge');
                tierBadge.textContent = tier.toUpperCase();
                tierBadge.className = 'badge ' + (tier === 'PREMIUM' ? 'badge-premium' : 'badge-free');

                var catName = m.category_name || (m.category && m.category.name) || '';
                if (catName) {
                    document.getElementById('material-category-badge').textContent = catName;
                }

                // Premium gate
                if (tier === 'PREMIUM' && userTier !== 'PREMIUM') {
                    document.getElementById('premium-gate').classList.remove('hidden');
                    document.getElementById('material-content').classList.add('hidden');
                    document.getElementById('curriculum-sidebar').classList.add('hidden');
                    return;
                }

                // Parse content into pages
                var rawContent = m.content || m.body || m.html_content || '';
                contentPages = rawContent.split('<!-- PAGE_BREAK -->').filter(p => p.trim() !== '');
                if (contentPages.length === 0) contentPages = ['<p>Belum ada konten materi.</p>'];
                
                currentPageIndex = 0;
                
                document.getElementById('content-loading').classList.add('hidden');
                document.getElementById('content-display').classList.remove('hidden');
                
                renderPage();
                loadCurriculum();

                if (isCompleted) {
                    showCompletedStatus();
                }

            }).catch(function (err) {
                console.error('Failed to load material:', err);
                alert('Gagal memuat materi: ' + err.message);
                navigateTo('/materials');
            });
        }

        window.renderPage = function() {
            // Render HTML
            document.getElementById('material-body').innerHTML = contentPages[currentPageIndex];
            
            // Update Indicators
            document.getElementById('page-indicator').textContent = 'Halaman ' + (currentPageIndex + 1) + ' dari ' + contentPages.length;
            
            // Update Buttons
            document.getElementById('btn-prev-page').disabled = currentPageIndex === 0;
            
            var nextBtn = document.getElementById('btn-next-page');
            var nextText = document.getElementById('btn-next-text');
            var nextIcon = document.getElementById('btn-next-icon');
            
            if (currentPageIndex === contentPages.length - 1) {
                if (isCompleted) {
                    nextText.textContent = 'Materi Selesai';
                    nextIcon.textContent = 'check_circle';
                    nextBtn.className = 'flex items-center gap-2 px-6 py-2 rounded-lg border border-[var(--success)] bg-[var(--success-subtle)] text-[var(--success)] cursor-default';
                    nextBtn.disabled = true;
                } else {
                    nextText.textContent = 'Tandai Selesai';
                    nextIcon.textContent = 'task_alt';
                    nextBtn.className = 'flex items-center gap-2 px-6 py-2 rounded-lg bg-[var(--primary)] text-white hover:opacity-90 transition-opacity';
                    nextBtn.disabled = false;
                }
            } else {
                nextText.textContent = 'Halaman Berikutnya';
                nextIcon.textContent = 'chevron_right';
                nextBtn.className = 'flex items-center gap-2 px-6 py-2 rounded-lg bg-[var(--primary)] text-white hover:opacity-90 transition-opacity';
                nextBtn.disabled = false;
            }

            // Update Dots
            var dotsHtml = '';
            for(var i=0; i<contentPages.length; i++) {
                if(i === currentPageIndex) {
                    dotsHtml += '<span class="w-2 h-2 rounded-full bg-[var(--primary)]"></span>';
                } else {
                    dotsHtml += '<span class="w-2 h-2 rounded-full bg-[var(--border-default)] cursor-pointer hover:bg-[var(--text-muted)] transition-colors" onclick="goToPage(' + i + ')"></span>';
                }
            }
            document.getElementById('page-dots').innerHTML = dotsHtml;
            
            // Scroll to top of content
            document.getElementById('material-content').scrollIntoView({ behavior: 'smooth', block: 'start' });
        };

        window.goToPage = function(index) {
            if(index >= 0 && index < contentPages.length) {
                currentPageIndex = index;
                renderPage();
            }
        };

        window.prevPage = function() {
            if (currentPageIndex > 0) {
                currentPageIndex--;
                renderPage();
            }
        };

        window.nextPage = function() {
            if (currentPageIndex < contentPages.length - 1) {
                currentPageIndex++;
                renderPage();
            } else {
                markAsComplete();
            }
        };

        function showCompletedStatus() {
            document.getElementById('progress-card').classList.remove('hidden');
        }

        window.markAsComplete = function () {
            var btn = document.getElementById('btn-next-page');
            btn.disabled = true;
            document.getElementById('btn-next-text').textContent = 'Menyimpan...';

            apiFetch('/materials/' + materialSlug + '/complete', {
                method: 'POST'
            }).then(function () {
                isCompleted = true;
                showCompletedStatus();
                renderPage(); // Will update button to "Materi Selesai"
                
                // Refresh curriculum to show checkmark
                loadCurriculum();
            }).catch(function (err) {
                console.error('Failed to mark complete:', err);
                alert('Gagal: ' + err.message);
                btn.disabled = false;
                document.getElementById('btn-next-text').textContent = 'Tandai Selesai';
            });
        };

        function loadCurriculum() {
            apiFetch('/materials').then(function(data) {
                var materials = Array.isArray(data) ? data : (data.data || []);
                // Filter by same category
                var related = materials.filter(m => m.category_id === currentCategoryId);
                
                // Sort by order
                related.sort((a, b) => (a.order || 0) - (b.order || 0));

                var html = '';
                related.forEach(function(m) {
                    var isCurrent = m.id === materialId || m.slug === materialSlug;
                    var isMCompleted = m.is_completed || m.completed;
                    
                    var activeClass = isCurrent ? 'bg-[var(--primary-subtle)] border-[var(--primary)] text-[var(--primary)]' : 'bg-[var(--bg-surface)] border-[var(--border-default)] hover:border-[var(--text-muted)] text-[var(--text-secondary)]';
                    
                    var icon = 'play_circle';
                    var iconClass = 'text-[var(--primary)]';
                    if(isMCompleted) {
                        icon = 'check_circle';
                        iconClass = 'text-[var(--success)]';
                    } else if (!isCurrent) {
                        iconClass = 'text-[var(--text-muted)]';
                    }

                    html += `
                        <li class="p-3 rounded-lg border transition-all cursor-pointer ${activeClass}" onclick="navigateTo('/materials/${m.slug}')">
                            <div class="flex items-center gap-3">
                                <span class="material-symbols-outlined ${iconClass} text-[20px]">${icon}</span>
                                <span class="text-sm font-medium leading-tight truncate" title="${m.title}">${m.title}</span>
                            </div>
                        </li>
                    `;
                });

                document.getElementById('curriculum-list').innerHTML = html;
            }).catch(function(err) {
                console.error('Failed to load curriculum', err);
                document.getElementById('curriculum-list').innerHTML = '<p class="text-sm text-[var(--danger)]">Gagal memuat daftar materi.</p>';
            });
        }

        loadMaterial();
    })();
</script>
@endsection