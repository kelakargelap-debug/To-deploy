@extends('app')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="mb-6">
            <button onclick="navigateTo('/admin/tryouts')" class="btn-ghost mb-4">
                &larr; Back to Tryouts
            </button>
            <h1 id="page-title" class="text-2xl font-bold text-[var(--text-primary)]">Create Tryout</h1>
        </div>

        <div id="form-container" class="bg-[var(--bg-surface)] rounded-lg shadow-sm border border-[var(--border-color)] p-6">
            <div id="loading-message" class="text-[var(--text-secondary)] text-center py-8">Loading categories...</div>
            <form id="tryout-form" class="hidden space-y-5">
                <input type="hidden" id="edit-tryout-id" value="">
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

                {{-- Description --}}
                <x-form-field 
                    id="input-description" 
                    label="Description" 
                    type="textarea" 
                    rows="3" 
                />

                {{-- Category --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Category <span class="text-[var(--danger)]">*</span></label>
                    <select id="input-category" required class="input-field">
                        <option value="">Select category...</option>
                    </select>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Status</label>
                    <select id="input-status" class="input-field">
                        <option value="DRAFT">DRAFT</option>
                        <option value="PUBLISHED">PUBLISHED</option>
                        <option value="ARCHIVED">ARCHIVED</option>
                    </select>
                </div>

                {{-- Required Tier --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Required Tier</label>
                    <select id="input-tier" class="input-field">
                        <option value="FREE">FREE</option>
                        <option value="PREMIUM">PREMIUM</option>
                    </select>
                </div>

                {{-- Duration --}}
                <x-form-field 
                    id="input-duration" 
                    label="Duration (minutes)" 
                    type="number" 
                    required="true" 
                    min="1" 
                    value="60" 
                />

                {{-- Total Questions --}}
                <x-form-field 
                    id="input-total-questions" 
                    label="Total Questions" 
                    type="number" 
                    required="true" 
                    min="1" 
                    value="10" 
                    helpText="Set the expected total number of questions"
                />

                {{-- Passing Score --}}
                <x-form-field 
                    id="input-passing-score" 
                    label="Passing Score (0-100)" 
                    type="number" 
                    required="true" 
                    min="0" 
                    max="100" 
                    value="50" 
                />

                {{-- Randomize Toggle --}}
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-[var(--text-secondary)]">Randomize Question Order</label>
                    <button id="toggle-randomize" type="button" onclick="toggleRandomizeState()"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" style="background-color: var(--text-muted);">
                        <span id="toggle-randomize-dot" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-0"></span>
                    </button>
                    <span id="randomize-label" class="text-sm text-[var(--text-secondary)]">No</span>
                </div>

                {{-- Show Result Toggle --}}
                <div class="flex items-center gap-3">
                    <label class="text-sm font-medium text-[var(--text-secondary)]">Show Result After Submit</label>
                    <button id="toggle-show-result" type="button" onclick="toggleShowResultState()"
                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none" style="background-color: var(--accent);">
                        <span id="toggle-show-result-dot" class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform translate-x-5"></span>
                    </button>
                    <span id="show-result-label" class="text-sm text-[var(--text-secondary)]">Yes</span>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-[var(--border-color)]">
                    <button type="submit" class="btn-primary">Save Tryout</button>
                    <button type="button" onclick="navigateTo('/admin/tryouts')" class="btn-secondary">Cancel</button>
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
                    toggle.style.backgroundColor = 'var(--accent)';
                    dot.classList.remove('translate-x-0');
                    dot.classList.add('translate-x-5');
                    label.textContent = 'Yes';
                } else {
                    toggle.style.backgroundColor = 'var(--text-muted)';
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
                    document.getElementById('loading-message').classList.add('text-[var(--danger)]');
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
                    document.getElementById('loading-message').classList.add('text-[var(--danger)]');
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
                        showToast('Tryout berhasil diupdate!', 'success');
                    } else {
                        await apiFetch('/admin/tryouts', {
                            method: 'POST',
                            body: JSON.stringify(payload),
                        });
                        showToast('Tryout berhasil dibuat!', 'success');
                    }
                    navigateTo('/admin/tryouts');
                } catch (err) {
                    showToast(err.message, 'error');
                }
            });

            loadCategories();
        })();
    </script>
@endpush