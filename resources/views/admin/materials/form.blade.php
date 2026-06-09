@extends('app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <button onclick="navigateTo('/admin/materials')" class="btn-ghost mb-4">
                &larr; Back to Materials
            </button>
            <h1 id="page-title" class="text-2xl font-bold text-[var(--text-primary)]">Tambah Materi</h1>
        </div>

        <div class="bg-[var(--bg-surface)] rounded-lg shadow-sm border border-[var(--border-color)] p-6">
            <div id="loading-message" class="text-[var(--text-secondary)] text-center py-8 hidden">Loading...</div>
            <form id="material-form" class="space-y-5">
                <input type="hidden" id="edit-material-id" value="">
                <input type="hidden" id="form-mode" value="create">

                {{-- Title --}}
                <x-form-field 
                    id="input-title" 
                    label="Title" 
                    type="text" 
                    required="true" 
                    oninput="autoGenerateSlug()"
                />

                {{-- Slug --}}
                <x-form-field 
                    id="input-slug" 
                    label="Slug" 
                    type="text" 
                    helpText="Auto-generated from title. You can customize it."
                />

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Category <span class="text-[var(--danger)]">*</span></label>
                    <select id="input-category" required class="input-field">
                        <option value="">Select category...</option>
                    </select>
                </div>

                {{-- Content --}}
                <x-form-field 
                    id="input-content" 
                    label="Content" 
                    type="textarea" 
                    rows="15" 
                    required="true" 
                    helpText='Use <code class="px-1 py-0.5 bg-[var(--bg-hover)] rounded text-xs">&lt;!-- PAGE_BREAK --&gt;</code> to split content into multiple pages for readers.'
                />

                {{-- Required Tier --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Required Tier</label>
                    <select id="input-tier" class="input-field">
                        <option value="FREE">FREE</option>
                        <option value="PREMIUM">PREMIUM</option>
                    </select>
                </div>

                {{-- Order --}}
                <x-form-field 
                    id="input-order" 
                    label="Order" 
                    type="number" 
                    value="0" 
                />

                {{-- Is Published Toggle --}}
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-[var(--text-secondary)]">Is Published</label>
                    <button id="toggle-published" type="button" onclick="togglePublishedState()"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" style="background-color: var(--text-muted);">
                        <span id="toggle-published-dot"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-0"></span>
                    </button>
                    <span id="published-label" class="text-sm text-[var(--text-secondary)]">No (Draft)</span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-[var(--border-color)]">
                    <button type="submit" class="btn-primary">Save Material</button>
                    <button type="button" onclick="navigateTo('/admin/materials')" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            let isPublished = false;
            let categories = [];
            let isEditMode = false;
            let materialId = null;

            // Parse route parameter
            function parseParams() {
                materialId = {!! isset($id) ? $id : 'null' !!};
                isEditMode = materialId !== null;
                if (isEditMode) {
                    document.getElementById('page-title').textContent = 'Edit Materi';
                    document.getElementById('form-mode').value = 'edit';
                    document.getElementById('edit-material-id').value = materialId;
                }
            }

            // Auto-generate slug
            window.autoGenerateSlug = function () {
                if (isEditMode) return;
                const title = document.getElementById('input-title').value;
                const slug = title.toLowerCase()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-')
                    .trim();
                document.getElementById('input-slug').value = slug;
            };

            // Published toggle
            function togglePublishedState() {
                isPublished = !isPublished;
                const toggle = document.getElementById('toggle-published');
                const dot = document.getElementById('toggle-published-dot');
                const label = document.getElementById('published-label');
                if (isPublished) {
                    toggle.style.backgroundColor = 'var(--accent)';
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-5');
                    label.textContent = 'Yes (Published)';
                } else {
                    toggle.style.backgroundColor = 'var(--text-muted)';
                    dot.classList.remove('translate-x-5');
                    dot.classList.add('translate-x-0');
                    label.textContent = 'No (Draft)';
                }
            }

            window.togglePublishedState = togglePublishedState;

            function setPublishedToggleUI() {
                const toggle = document.getElementById('toggle-published');
                const dot = document.getElementById('toggle-published-dot');
                const label = document.getElementById('published-label');
                if (isPublished) {
                    toggle.style.backgroundColor = 'var(--accent)';
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-5');
                    label.textContent = 'Yes (Published)';
                } else {
                    toggle.style.backgroundColor = 'var(--text-muted)';
                    dot.classList.remove('translate-x-5');
                    dot.classList.add('translate-x-0');
                    label.textContent = 'No (Draft)';
                }
            }

            async function loadCategories() {
                try {
                    categories = await apiFetch('/admin/categories');
                    const select = document.getElementById('input-category');
                    categories.forEach(c => {
                        const opt = document.createElement('option');
                        opt.value = c.id;
                        opt.textContent = c.name;
                        select.appendChild(opt);
                    });

                    if (isEditMode) {
                        await loadMaterialData();
                    }
                } catch (err) {
                    document.getElementById('loading-message').textContent = 'Error: ' + err.message;
                    document.getElementById('loading-message').classList.remove('hidden');
                    document.getElementById('loading-message').classList.add('text-[var(--danger)]');
                }
            }

            async function loadMaterialData() {
                try {
                    // First, get the slug from the materials list
                    const listData = await apiFetch('/materials');
                    const materials = Array.isArray(listData) ? listData : (listData.data || []);
                    const listItem = materials.find(m => m.id == materialId);

                    if (!listItem) {
                        document.getElementById('loading-message').textContent = 'Material not found';
                        document.getElementById('loading-message').classList.remove('hidden');
                        return;
                    }

                    // Now fetch the full detail (including content) via slug
                    const detailData = await apiFetch('/materials/' + listItem.slug);
                    const material = detailData.material || detailData;

                    document.getElementById('input-title').value = material.title || '';
                    document.getElementById('input-slug').value = material.slug || '';
                    document.getElementById('input-category').value = material.category_id || '';
                    document.getElementById('input-content').value = material.content || '';
                    document.getElementById('input-tier').value = material.required_tier || listItem.required_tier || 'FREE';
                    document.getElementById('input-order').value = material.order || listItem.order || 0;

                    isPublished = material.is_published || listItem.is_published || false;
                    setPublishedToggleUI();
                } catch (err) {
                    document.getElementById('loading-message').textContent = 'Error: ' + err.message;
                    document.getElementById('loading-message').classList.remove('hidden');
                    document.getElementById('loading-message').classList.add('text-[var(--danger)]');
                }
            }

            // Form Submit
            document.getElementById('material-form').addEventListener('submit', async (e) => {
                e.preventDefault();

                const payload = {
                    title: document.getElementById('input-title').value,
                    category_id: parseInt(document.getElementById('input-category').value),
                    content: document.getElementById('input-content').value,
                    required_tier: document.getElementById('input-tier').value,
                    order: parseInt(document.getElementById('input-order').value) || 0,
                    is_published: isPublished,
                };

                const slug = document.getElementById('input-slug').value;
                if (slug) payload.slug = slug;

                try {
                    if (isEditMode) {
                        await apiFetch('/admin/materials/' + materialId, {
                            method: 'PATCH',
                            body: JSON.stringify(payload),
                        });
                        showToast('Materi berhasil diupdate!', 'success');
                    } else {
                        await apiFetch('/admin/materials', {
                            method: 'POST',
                            body: JSON.stringify(payload),
                        });
                        showToast('Materi berhasil dibuat!', 'success');
                    }
                    navigateTo('/admin/materials');
                } catch (err) {
                    showToast(err.message, 'error');
                }
            });

            parseParams();
            loadCategories();
        })();
    </script>
@endpush