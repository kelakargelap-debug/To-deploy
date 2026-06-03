@extends('app')

@section('content')
    <div class="p-6 max-w-4xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <button onclick="navigateTo('/admin/materials')"
                class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                &larr; Back
            </button>
            <h1 id="page-title" class="text-2xl font-bold text-gray-900 dark:text-gray-100">Tambah Materi</h1>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6">
            <div id="loading-message" class="text-gray-500 dark:text-gray-400 text-center py-8 hidden">Loading...</div>
            <form id="material-form" class="space-y-5">
                <input type="hidden" id="edit-material-id" value="">
                <input type="hidden" id="form-mode" value="create">

                {{-- Title --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title <span
                            class="text-red-500">*</span></label>
                    <input type="text" id="input-title" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500"
                        oninput="autoGenerateSlug()">
                </div>

                {{-- Slug --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug</label>
                    <input type="text" id="input-slug"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Auto-generated from title. You can customize
                        it.</p>
                </div>

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category <span
                            class="text-red-500">*</span></label>
                    <select id="input-category" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="">Select category...</option>
                    </select>
                </div>

                {{-- Content --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content <span
                            class="text-red-500">*</span></label>
                    <textarea id="input-content" rows="15" required
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 resize-y"></textarea>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Use <code
                            class="px-1 py-0.5 bg-gray-100 dark:bg-gray-600 rounded text-xs">&lt;!-- PAGE_BREAK --&gt;</code>
                        to split content into multiple pages for readers.
                    </p>
                </div>

                {{-- Required Tier --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Required Tier</label>
                    <select id="input-tier"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="FREE">FREE</option>
                        <option value="PREMIUM">PREMIUM</option>
                    </select>
                </div>

                {{-- Order --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Order</label>
                    <input type="number" id="input-order" value="0"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Is Published Toggle --}}
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Is Published</label>
                    <button id="toggle-published" type="button" onclick="togglePublishedState()"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-gray-300 dark:bg-gray-600">
                        <span id="toggle-published-dot"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-0"></span>
                    </button>
                    <span id="published-label" class="text-sm text-gray-600 dark:text-gray-400">No (Draft)</span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">Save
                        Material</button>
                    <button type="button" onclick="navigateTo('/admin/materials')"
                        class="px-6 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">Cancel</button>
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
                    toggle.classList.remove('bg-gray-300', 'dark:bg-gray-600');
                    toggle.classList.add('bg-indigo-600');
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-5');
                    label.textContent = 'Yes (Published)';
                } else {
                    toggle.classList.remove('bg-indigo-600');
                    toggle.classList.add('bg-gray-300', 'dark:bg-gray-600');
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
                    toggle.classList.remove('bg-gray-300', 'dark:bg-gray-600');
                    toggle.classList.add('bg-indigo-600');
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-5');
                    label.textContent = 'Yes (Published)';
                } else {
                    toggle.classList.remove('bg-indigo-600');
                    toggle.classList.add('bg-gray-300', 'dark:bg-gray-600');
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
                    document.getElementById('loading-message').classList.add('text-red-500');
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
                    document.getElementById('loading-message').classList.add('text-red-500');
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
                        alert('Materi berhasil diupdate!');
                    } else {
                        await apiFetch('/admin/materials', {
                            method: 'POST',
                            body: JSON.stringify(payload),
                        });
                        alert('Materi berhasil dibuat!');
                    }
                    navigateTo('/admin/materials');
                } catch (err) {
                    alert('Error: ' + err.message);
                }
            });

            parseParams();
            loadCategories();
        })();
    </script>
@endpush