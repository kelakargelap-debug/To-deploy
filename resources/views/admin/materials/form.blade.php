@extends('app')

@section('content')
<div class="max-w-5xl mx-auto pb-12">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <button onclick="navigateTo('/admin/materials')" class="w-10 h-10 flex items-center justify-center rounded-full hover:bg-[var(--bg-surface-hover)] text-[var(--text-secondary)] transition-all">
                <span class="material-symbols-outlined">arrow_back</span>
            </button>
            <div>
                <h2 id="page-title" class="font-headline-md text-headline-md text-[var(--text-primary)]">Tambah Materi</h2>
                <p id="page-subtitle" class="text-label-md text-[var(--text-secondary)]">Isi detail materi di bawah ini</p>
            </div>
        </div>
        <div class="flex gap-3">
            <button onclick="navigateTo('/admin/materials')" class="px-6 py-2.5 rounded-lg border border-[var(--border-default)] text-[var(--text-secondary)] font-label-md hover:bg-[var(--bg-surface-hover)] transition-all">
                Discard Changes
            </button>
            <button id="btn-save-draft" onclick="submitMaterialForm(false)" class="px-6 py-2.5 rounded-lg border border-[var(--border-default)] text-[var(--text-primary)] font-bold hover:bg-[var(--bg-surface-hover)] transition-all hidden">Save as Draft</button>
            <button id="btn-publish" onclick="submitMaterialForm(true)" class="px-6 py-2.5 rounded-lg bg-[var(--primary)] text-white font-label-md hover:opacity-90 flex items-center gap-2 transition-all">
                <span class="material-symbols-outlined text-[18px]">save</span>
                <span id="btn-publish-text">Publish Changes</span>
            </button>
        </div>
    </div>

    <!-- Editor Workspace (Bento Style) -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6">
        <!-- Metadata & Settings (Left Column) -->
        <div class="md:col-span-4 space-y-6">
            <div class="bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl p-6 shadow-sm">
                <h3 class="font-label-md text-[var(--primary)] font-bold uppercase tracking-wider mb-4">Informasi Dasar</h3>
                
                <form id="material-form" class="space-y-4">
                    <input type="hidden" id="edit-material-id" value="">
                    <input type="hidden" id="form-mode" value="create">
                    
                    <div>
                        <label class="block text-label-sm font-semibold text-[var(--text-primary)] mb-2">Judul Materi</label>
                        <input id="input-title" oninput="autoGenerateSlug()" class="w-full px-4 py-2.5 bg-[var(--bg-default)] border border-[var(--border-default)] rounded-lg focus:ring-2 focus:ring-[var(--primary)] outline-none transition-all text-body-md" type="text" placeholder="Masukkan judul..." required/>
                    </div>
                    
                    <div>
                        <label class="block text-label-sm font-semibold text-[var(--text-primary)] mb-2">Slug (URL)</label>
                        <input id="input-slug" class="w-full px-4 py-2.5 bg-[var(--bg-default)] border border-[var(--border-default)] rounded-lg focus:ring-2 focus:ring-[var(--primary)] outline-none transition-all text-body-md text-[var(--text-secondary)]" type="text" placeholder="auto-generated-slug"/>
                    </div>

                    <div>
                        <label class="block text-label-sm font-semibold text-[var(--text-primary)] mb-2">Kategori</label>
                        <select id="input-category" required class="w-full px-4 py-2.5 bg-[var(--bg-default)] border border-[var(--border-default)] rounded-lg focus:ring-2 focus:ring-[var(--primary)] outline-none transition-all text-body-md">
                            <option value="">Pilih kategori...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-label-sm font-semibold text-[var(--text-primary)] mb-2">Tier Akses</label>
                        <div class="flex gap-2">
                            <button type="button" id="tier-free-btn" onclick="setTier('FREE')" class="flex-1 py-2 rounded-lg border-2 border-[var(--primary)] bg-[var(--primary-subtle)] text-[var(--primary-hover)] font-bold text-label-md transition-all">Free</button>
                            <button type="button" id="tier-premium-btn" onclick="setTier('PREMIUM')" class="flex-1 py-2 rounded-lg border border-[var(--border-default)] text-[var(--text-secondary)] font-medium text-label-md hover:border-[var(--primary)] transition-all">Premium</button>
                        </div>
                        <input type="hidden" id="input-tier" value="FREE">
                    </div>
                    
                    <div>
                        <label class="block text-label-sm font-semibold text-[var(--text-primary)] mb-2">Urutan (Opsional)</label>
                        <input id="input-order" class="w-full px-4 py-2.5 bg-[var(--bg-default)] border border-[var(--border-default)] rounded-lg focus:ring-2 focus:ring-[var(--primary)] outline-none transition-all text-body-md" type="number" value="0"/>
                    </div>
                </form>
            </div>

            <div class="bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl p-6 shadow-sm">
                <h3 class="font-label-md text-[var(--primary)] font-bold uppercase tracking-wider mb-4">Thumbnail Materi</h3>
                <div class="aspect-video bg-[var(--bg-default)] rounded-lg border-2 border-dashed border-[var(--border-default)] flex flex-col items-center justify-center text-[var(--text-secondary)] hover:border-[var(--primary)] transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-4xl mb-2">image</span>
                    <p class="text-label-sm">Belum tersedia (Coming soon)</p>
                </div>
            </div>
        </div>

        <!-- Content Editor (Right Column) -->
        <div class="md:col-span-8">
            <div class="bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl shadow-sm flex flex-col h-full overflow-hidden">
                <!-- Editor Toolbar -->
                <div class="flex items-center justify-between px-4 py-3 bg-[var(--bg-default)] border-b border-[var(--border-default)] flex-wrap gap-2">
                    <div class="flex items-center gap-1">
                        <button type="button" onclick="document.execCommand('bold', false, null)" class="p-2 hover:bg-[var(--bg-surface-hover)] rounded text-[var(--text-secondary)]" title="Bold"><span class="material-symbols-outlined text-[20px]">format_bold</span></button>
                        <button type="button" onclick="document.execCommand('italic', false, null)" class="p-2 hover:bg-[var(--bg-surface-hover)] rounded text-[var(--text-secondary)]" title="Italic"><span class="material-symbols-outlined text-[20px]">format_italic</span></button>
                        <button type="button" onclick="document.execCommand('underline', false, null)" class="p-2 hover:bg-[var(--bg-surface-hover)] rounded text-[var(--text-secondary)]" title="Underline"><span class="material-symbols-outlined text-[20px]">format_underlined</span></button>
                        <div class="w-px h-6 bg-[var(--border-default)] mx-1"></div>
                        <button type="button" onclick="document.execCommand('insertUnorderedList', false, null)" class="p-2 hover:bg-[var(--bg-surface-hover)] rounded text-[var(--text-secondary)]" title="Bullet List"><span class="material-symbols-outlined text-[20px]">format_list_bulleted</span></button>
                        <button type="button" onclick="document.execCommand('insertOrderedList', false, null)" class="p-2 hover:bg-[var(--bg-surface-hover)] rounded text-[var(--text-secondary)]" title="Numbered List"><span class="material-symbols-outlined text-[20px]">format_list_numbered</span></button>
                        <div class="w-px h-6 bg-[var(--border-default)] mx-1"></div>
                        <button type="button" onclick="insertPageBreak()" class="flex items-center gap-1 px-3 hover:bg-[var(--bg-surface-hover)] rounded text-[var(--text-secondary)] text-sm font-medium py-1.5" title="Page Break">
                            <span class="material-symbols-outlined text-[20px]">insert_page_break</span> Page Break
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="toggle-preview" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-label-sm font-bold bg-[var(--bg-surface-hover)] text-[var(--text-primary)] hover:bg-[var(--primary)] hover:text-white transition-all">
                            <span class="material-symbols-outlined text-[18px]">visibility</span> Preview
                        </button>
                    </div>
                </div>

                <!-- Editor Content Area -->
                <div class="relative flex-1 flex flex-col min-h-[500px]">
                    <div id="editor-field" class="flex-1 p-8 outline-none font-body-md text-body-lg text-[var(--text-primary)] leading-relaxed prose dark:prose-invert max-w-none focus:ring-inset focus:ring-2 focus:ring-[var(--primary)]" contenteditable="true" placeholder="Tulis konten materi di sini..."></div>
                    
                    <!-- Live Preview Overlay (Initially Hidden) -->
                    <div id="preview-overlay" class="absolute inset-0 bg-[var(--bg-surface)] z-10 p-8 overflow-y-auto hidden border-t border-[var(--border-default)]">
                        <div class="max-w-3xl mx-auto">
                            <div class="mb-6 pb-6 border-b border-[var(--border-default)]">
                                <span class="badge badge-neutral mb-2">Pratinjau E-book</span>
                                <h2 id="preview-title" class="text-3xl font-bold text-[var(--text-primary)]">Judul Materi</h2>
                            </div>
                            <div id="preview-content" class="prose dark:prose-invert max-w-none text-[var(--text-primary)]">
                                <!-- Cloned content -->
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Bar -->
                <div class="px-6 py-2 bg-[var(--bg-default)] border-t border-[var(--border-default)] flex justify-between text-label-sm text-[var(--text-secondary)]">
                    <div class="flex gap-4">
                        <span id="word-count">Words: 0</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <span id="save-indicator" class="w-2 h-2 bg-[var(--success)] rounded-full"></span>
                        <span id="save-status-text">Ready</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Set Tier Selection
    window.setTier = function(tier) {
        document.getElementById('input-tier').value = tier;
        if(tier === 'FREE') {
            document.getElementById('tier-free-btn').className = "flex-1 py-2 rounded-lg border-2 border-[var(--primary)] bg-[var(--primary-subtle)] text-[var(--primary-hover)] font-bold text-label-md transition-all";
            document.getElementById('tier-premium-btn').className = "flex-1 py-2 rounded-lg border border-[var(--border-default)] text-[var(--text-secondary)] font-medium text-label-md hover:border-[var(--primary)] transition-all";
        } else {
            document.getElementById('tier-premium-btn').className = "flex-1 py-2 rounded-lg border-2 border-[var(--primary)] bg-[var(--primary-subtle)] text-[var(--primary-hover)] font-bold text-label-md transition-all";
            document.getElementById('tier-free-btn').className = "flex-1 py-2 rounded-lg border border-[var(--border-default)] text-[var(--text-secondary)] font-medium text-label-md hover:border-[var(--primary)] transition-all";
        }
    };

    window.insertPageBreak = function() {
        // Insert page break html comment
        const html = '<hr class="border-t-2 border-dashed border-[var(--border-default)] my-8 relative before:content-[\'PAGE_BREAK\'] before:absolute before:left-1/2 before:-translate-x-1/2 before:-top-3 before:bg-[var(--bg-surface)] before:px-2 before:text-xs before:text-[var(--text-secondary)]">';
        document.execCommand('insertHTML', false, html);
    };

    window.autoGenerateSlug = function() {
        const title = document.getElementById('input-title').value;
        const slugInput = document.getElementById('input-slug');
        if (document.getElementById('form-mode').value === 'create') {
            slugInput.value = title.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        }
    };

    // Editor Preview Toggle
    const togglePreviewBtn = document.getElementById('toggle-preview');
    const previewOverlay = document.getElementById('preview-overlay');
    const editorField = document.getElementById('editor-field');
    const previewContent = document.getElementById('preview-content');

    togglePreviewBtn.addEventListener('click', () => {
        const isVisible = !previewOverlay.classList.contains('hidden');
        if (isVisible) {
            previewOverlay.classList.add('hidden');
            togglePreviewBtn.innerHTML = `<span class="material-symbols-outlined text-[18px]">visibility</span> Preview`;
            togglePreviewBtn.classList.remove('bg-[var(--primary)]', 'text-white');
        } else {
            document.getElementById('preview-title').textContent = document.getElementById('input-title').value || 'Judul Materi';
            let contentHtml = editorField.innerHTML;
            // Parse page breaks for preview
            contentHtml = contentHtml.replace(/<hr class="border-t-2[^>]*>/g, '<div class="my-8 text-center text-xs text-[var(--text-secondary)] uppercase tracking-widest">--- Page Break ---</div>');
            previewContent.innerHTML = contentHtml;
            
            previewOverlay.classList.remove('hidden');
            togglePreviewBtn.innerHTML = `<span class="material-symbols-outlined text-[18px]">edit</span> Edit`;
            togglePreviewBtn.classList.add('bg-[var(--primary)]', 'text-white');
        }
    });

    // Word Count
    editorField.addEventListener('input', () => {
        const text = editorField.innerText || '';
        const words = text.trim() ? text.trim().split(/\s+/).length : 0;
        document.getElementById('word-count').textContent = `Words: ${words}`;
        
        document.getElementById('save-indicator').className = "w-2 h-2 bg-[var(--warning)] rounded-full animate-pulse";
        document.getElementById('save-status-text').textContent = "Unsaved changes";
    });

    (function () {
        let isEditMode = false;
        let materialId = null;

        // Parse route parameter
        function parseParams() {
            const pathParts = window.location.pathname.split('/');
            const action = pathParts[pathParts.length - 1];
            if (action !== 'create' && action) {
                isEditMode = true;
                materialId = action;
                document.getElementById('form-mode').value = 'edit';
                document.getElementById('edit-material-id').value = materialId;
                document.getElementById('page-title').textContent = 'Edit Materi';
                document.getElementById('page-subtitle').textContent = 'Ubah detail materi';
                loadMaterialData(materialId);
            }
        }

        // Load Categories
        function loadCategories() {
            apiFetch('/admin/categories').then(data => {
                const select = document.getElementById('input-category');
                data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.id;
                    option.textContent = cat.name;
                    select.appendChild(option);
                });
            }).catch(err => {
                console.error("Gagal memuat kategori:", err);
            });
        }

        // Load existing material data
        function loadMaterialData(id) {
            apiFetch('/admin/materials/' + id).then(data => {
                document.getElementById('input-title').value = data.title;
                document.getElementById('input-slug').value = data.slug;
                document.getElementById('input-category').value = data.category_id;
                document.getElementById('input-order').value = data.order || 0;
                setTier(data.required_tier || 'FREE');
                
                // Convert <!-- PAGE_BREAK --> to visual HR
                let content = data.content || '';
                content = content.replace(/<!-- PAGE_BREAK -->/g, '<hr class="border-t-2 border-dashed border-[var(--border-default)] my-8 relative before:content-[\'PAGE_BREAK\'] before:absolute before:left-1/2 before:-translate-x-1/2 before:-top-3 before:bg-[var(--bg-surface)] before:px-2 before:text-xs before:text-[var(--text-secondary)]">');
                
                document.getElementById('editor-field').innerHTML = content;
                
                const words = editorField.innerText.trim() ? editorField.innerText.trim().split(/\s+/).length : 0;
                document.getElementById('word-count').textContent = `Words: ${words}`;
            }).catch(err => {
                alert('Gagal memuat data: ' + err.message);
                navigateTo('/admin/materials');
            });
        }

        // Form Submission
        window.submitMaterialForm = function(isPublishedFlag) {
            const btn = document.getElementById('btn-publish');
            btn.disabled = true;
            document.getElementById('btn-publish-text').textContent = 'Saving...';
            document.getElementById('save-indicator').className = "w-2 h-2 bg-[var(--warning)] rounded-full animate-pulse";
            document.getElementById('save-status-text').textContent = "Saving...";

            // Convert visual HR back to <!-- PAGE_BREAK -->
            let contentHtml = editorField.innerHTML;
            contentHtml = contentHtml.replace(/<hr class="border-t-2 border-dashed[^>]*>/g, '<!-- PAGE_BREAK -->');

            const payload = {
                title: document.getElementById('input-title').value,
                slug: document.getElementById('input-slug').value,
                category_id: document.getElementById('input-category').value,
                content: contentHtml,
                required_tier: document.getElementById('input-tier').value,
                order: document.getElementById('input-order').value,
                is_published: isPublishedFlag
            };

            const endpoint = isEditMode ? '/admin/materials/' + materialId : '/admin/materials';
            const method = isEditMode ? 'PATCH' : 'POST';

            apiFetch(endpoint, {
                method: method,
                body: JSON.stringify(payload)
            }).then(() => {
                document.getElementById('save-indicator').className = "w-2 h-2 bg-[var(--success)] rounded-full";
                document.getElementById('save-status-text').textContent = "Saved";
                setTimeout(() => navigateTo('/admin/materials'), 500);
            }).catch(err => {
                alert('Gagal menyimpan: ' + err.message);
                btn.disabled = false;
                document.getElementById('btn-publish-text').textContent = 'Publish Changes';
                document.getElementById('save-indicator').className = "w-2 h-2 bg-[var(--danger)] rounded-full";
                document.getElementById('save-status-text').textContent = "Save failed";
            });
        };

        // Initialize
        loadCategories();
        parseParams();
    })();
</script>
@endpush