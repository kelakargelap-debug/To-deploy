@extends('app')

@section('content')
    <div class="p-6 max-w-3xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <button onclick="navigateTo('/admin/tryouts')"
                class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                &larr; Back
            </button>
            <h1 id="page-title" class="text-2xl font-bold text-gray-900 dark:text-gray-100">Create Tryout</h1>
        </div>

        <div id="form-container"
            class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-6">
            <div id="loading-message" class="text-gray-500 dark:text-gray-400 text-center py-8">Loading categories...</div>
            <form id="tryout-form" class="hidden space-y-5">
                <input type="hidden" id="edit-tryout-id" value="">
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

                {{-- Description --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea id="input-description" rows="3"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500 resize-y"></textarea>
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

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="input-status"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                        <option value="DRAFT">DRAFT</option>
                        <option value="PUBLISHED">PUBLISHED</option>
                        <option value="ARCHIVED">ARCHIVED</option>
                    </select>
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

                {{-- Duration --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Duration (minutes) <span
                            class="text-red-500">*</span></label>
                    <input type="number" id="input-duration" required min="1" value="60"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Total Questions --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Total Questions <span
                            class="text-red-500">*</span></label>
                    <input type="number" id="input-total-questions" required min="1" value="10"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Set the expected total number of questions</p>
                </div>

                {{-- Passing Score --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Passing Score (0-100)
                        <span class="text-red-500">*</span></label>
                    <input type="number" id="input-passing-score" required min="0" max="100" value="50"
                        class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>

                {{-- Randomize Toggle --}}
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Randomize Question Order</label>
                    <button id="toggle-randomize" type="button" onclick="toggleRandomizeState()"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-gray-300 dark:bg-gray-600">
                        <span id="toggle-randomize-dot"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-0"></span>
                    </button>
                    <span id="randomize-label" class="text-sm text-gray-600 dark:text-gray-400">No</span>
                </div>

                {{-- Show Result Toggle --}}
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Show Result After Submit</label>
                    <button id="toggle-show-result" type="button" onclick="toggleShowResultState()"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none bg-indigo-600">
                        <span id="toggle-show-result-dot"
                            class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-5"></span>
                    </button>
                    <span id="show-result-label" class="text-sm text-gray-600 dark:text-gray-400">Yes</span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <button type="submit"
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">Save
                        Tryout</button>
                    <button type="button" onclick="navigateTo('/admin/tryouts')"
                        class="px-6 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            let isRandomize = false;
            let isShowResult = true;
            let categories = [];

            // Determine mode from route parameter
            const tryoutId = {!! isset($id) ? $id : 'null' !!};
            const isEditMode = tryoutId !== null;

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

            function toggleRandomizeState() {
                isRandomize = !isRandomize;
                updateToggle('randomize', isRandomize);
            }

            function toggleShowResultState() {
                isShowResult = !isShowResult;
                updateToggle('show-result', isShowResult);
            }

            function updateToggle(name, state) {
                const toggle = document.getElementById('toggle-' + name);
                const dot = document.getElementById('toggle-' + name + '-dot');
                const label = document.getElementById(name.replace('-', '-') + '-label');
                if (state) {
                    toggle.classList.remove('bg-gray-300', 'dark:bg-gray-600');
                    toggle.classList.add('bg-indigo-600');
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-5');
                    label.textContent = 'Yes';
                } else {
                    toggle.classList.remove('bg-indigo-600');
                    toggle.classList.add('bg-gray-300', 'dark:bg-gray-600');
                    dot.classList.remove('translate-x-5');
                    dot.classList.add('translate-x-0');
                    label.textContent = 'No';
                }
            }

            window.toggleRandomizeState = toggleRandomizeState;
            window.toggleShowResultState = toggleShowResultState;

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
                        document.getElementById('page-title').textContent = 'Edit Tryout';
                        document.getElementById('form-mode').value = 'edit';
                        document.getElementById('edit-tryout-id').value = tryoutId;
                        await loadTryoutData(tryoutId);
                    } else {
                        document.getElementById('loading-message').classList.add('hidden');
                        document.getElementById('tryout-form').classList.remove('hidden');
                    }
                } catch (err) {
                    document.getElementById('loading-message').textContent = 'Error loading categories: ' + err.message;
                    document.getElementById('loading-message').classList.add('text-red-500');
                }
            }

            async function loadTryoutData(id) {
                try {
                    const data = await apiFetch('/tryouts');
                    const tryouts = Array.isArray(data) ? data : (data.data || []);
                    const tryout = tryouts.find(t => t.id == id);

                    if (!tryout) {
                        document.getElementById('loading-message').textContent = 'Tryout not found';
                        return;
                    }

                    document.getElementById('input-title').value = tryout.title || '';
                    document.getElementById('input-slug').value = tryout.slug || '';
                    document.getElementById('input-description').value = tryout.description || '';
                    document.getElementById('input-category').value = tryout.category_id;
                    document.getElementById('input-status').value = tryout.status || 'DRAFT';
                    document.getElementById('input-tier').value = tryout.required_tier || 'FREE';
                    document.getElementById('input-duration').value = tryout.duration_minutes;
                    document.getElementById('input-total-questions').value = tryout.total_questions;
                    document.getElementById('input-passing-score').value = tryout.passing_score;

                    isRandomize = tryout.randomize_order || false;
                    isShowResult = tryout.show_result !== undefined ? tryout.show_result : true;
                    updateToggle('randomize', isRandomize);
                    updateToggle('show-result', isShowResult);

                    document.getElementById('loading-message').classList.add('hidden');
                    document.getElementById('tryout-form').classList.remove('hidden');
                } catch (err) {
                    document.getElementById('loading-message').textContent = 'Error: ' + err.message;
                    document.getElementById('loading-message').classList.add('text-red-500');
                }
            }

            document.getElementById('tryout-form').addEventListener('submit', async (e) => {
                e.preventDefault();

                const payload = {
                    title: document.getElementById('input-title').value,
                    category_id: parseInt(document.getElementById('input-category').value),
                    description: document.getElementById('input-description').value || null,
                    status: document.getElementById('input-status').value,
                    required_tier: document.getElementById('input-tier').value,
                    duration_minutes: parseInt(document.getElementById('input-duration').value),
                    total_questions: parseInt(document.getElementById('input-total-questions').value),
                    passing_score: parseInt(document.getElementById('input-passing-score').value),
                    randomize_order: isRandomize,
                    show_result: isShowResult,
                };

                const slug = document.getElementById('input-slug').value;
                if (slug) payload.slug = slug;

                try {
                    if (isEditMode) {
                        await apiFetch('/admin/tryouts/' + tryoutId, {
                            method: 'PATCH',
                            body: JSON.stringify(payload),
                        });
                        alert('Tryout berhasil diupdate!');
                    } else {
                        await apiFetch('/admin/tryouts', {
                            method: 'POST',
                            body: JSON.stringify(payload),
                        });
                        alert('Tryout berhasil dibuat!');
                    }
                    navigateTo('/admin/tryouts');
                } catch (err) {
                    alert('Error: ' + err.message);
                }
            });

            loadCategories();
        })();
    </script>
@endpush