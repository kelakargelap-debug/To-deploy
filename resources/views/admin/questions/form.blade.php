@extends('app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <button id="back-button" onclick="goBack()" class="btn-ghost mb-4">
                &larr; Back
            </button>
            <h1 id="page-title" class="text-2xl font-bold text-[var(--text-primary)]">Tambah Soal</h1>
        </div>

        <div class="bg-[var(--bg-surface)] rounded-lg shadow-sm border border-[var(--border-color)] p-6">
            <form id="question-form" class="space-y-5">
                <input type="hidden" id="edit-question-id" value="">
                <input type="hidden" id="tryout-id" value="">
                <input type="hidden" id="form-mode" value="create">

                {{-- Type Selector --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Question Type <span class="text-[var(--danger)]">*</span></label>
                    <div class="flex gap-3" id="type-selector">
                        <button type="button" onclick="selectType('SINGLE_CHOICE')"
                            class="type-btn px-4 py-2 rounded-lg border-2 border-[var(--border-color)] bg-[var(--bg-surface)] text-[var(--text-secondary)] text-sm font-medium transition-colors hover:border-[var(--accent)]"
                            data-type="SINGLE_CHOICE">
                            Single Choice
                        </button>
                        <button type="button" onclick="selectType('MULTIPLE_CHOICE')"
                            class="type-btn px-4 py-2 rounded-lg border-2 border-[var(--border-color)] bg-[var(--bg-surface)] text-[var(--text-secondary)] text-sm font-medium transition-colors hover:border-[var(--accent)]"
                            data-type="MULTIPLE_CHOICE">
                            Multiple Choice
                        </button>
                        <button type="button" onclick="selectType('TRUE_FALSE')"
                            class="type-btn px-4 py-2 rounded-lg border-2 border-[var(--border-color)] bg-[var(--bg-surface)] text-[var(--text-secondary)] text-sm font-medium transition-colors hover:border-[var(--accent)]"
                            data-type="TRUE_FALSE">
                            True / False
                        </button>
                    </div>
                </div>

                {{-- Content --}}
                <x-form-field 
                    id="input-content" 
                    label="Question Content" 
                    type="textarea" 
                    rows="4" 
                    required="true" 
                />

                {{-- Image Upload --}}
                <div>
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-1">Image (optional)</label>
                    <div id="image-upload-area"
                        class="border-2 border-dashed border-[var(--border-color)] rounded-lg p-4 text-center cursor-pointer hover:border-[var(--accent)] transition-colors"
                        onclick="document.getElementById('image-file-input').click()"
                        ondragover="event.preventDefault(); this.style.borderColor = 'var(--accent)';"
                        ondragleave="this.style.borderColor = 'var(--border-color)';"
                        ondrop="handleImageDrop(event)">
                        <input type="file" id="image-file-input" accept="image/*" class="hidden"
                            onchange="handleImageFileSelect(event)">
                        <svg class="mx-auto h-8 w-8 text-[var(--text-muted)]" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        <p class="text-sm text-[var(--text-secondary)] mt-2">Drag & drop or click to upload</p>
                        <p class="text-xs text-[var(--text-muted)] mt-1">Max 10MB</p>
                    </div>
                    <div id="image-preview-container" class="hidden mt-3">
                        <img id="image-preview" class="max-h-40 rounded-lg border border-[var(--border-color)]"
                            alt="Question image">
                        <button type="button" onclick="removeImage()"
                            class="mt-2 btn-ghost btn-sm text-[var(--danger)]">Remove Image</button>
                    </div>
                    <div class="mt-2">
                        <label class="text-xs text-[var(--text-secondary)]">Or enter image URL:</label>
                        <input type="text" id="input-image-url" placeholder="https://..." class="input-field mt-1 text-xs py-1.5">
                    </div>
                </div>

                {{-- Points --}}
                <x-form-field 
                    id="input-points" 
                    label="Points" 
                    type="number" 
                    value="1" 
                    min="1" 
                />

                {{-- Explanation --}}
                <x-form-field 
                    id="input-explanation" 
                    label="Explanation (optional)" 
                    type="textarea" 
                    rows="3" 
                />

                {{-- Dynamic Options Section --}}
                <div id="options-section">
                    <label class="block text-sm font-medium text-[var(--text-secondary)] mb-2">Options</label>
                    <div id="options-container" class="space-y-3">
                        {{-- Options rendered dynamically --}}
                    </div>
                    <button type="button" id="add-option-btn" onclick="addOptionRow()" class="mt-3 btn-secondary hidden">
                        + Add Option
                    </button>
                </div>

                {{-- Action Buttons --}}
                <div class="flex gap-3 pt-4 border-t border-[var(--border-color)]">
                    <button type="submit" class="btn-primary">Save Question</button>
                    <button type="button" onclick="goBack()" class="btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        (function () {
            let selectedType = '';
            let options = [];
            let questionId = null;
            let tryoutId = null;
            let isEditMode = false;
            let uploadedImageUrl = '';

            const LETTERS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

            function escHtml(str) {
                const div = document.createElement('div');
                div.textContent = str || '';
                return div.innerHTML;
            }

            // Parse route parameter
            function parseParams() {
                questionId = {!! isset($id) ? $id : 'null' !!};
                isEditMode = questionId !== null;
                tryoutId = {!! request('tryout_id', 'null') !!};

                document.getElementById('tryout-id').value = tryoutId || '';
                document.getElementById('form-mode').value = isEditMode ? 'edit' : 'create';
                if (isEditMode) {
                    document.getElementById('edit-question-id').value = questionId;
                    document.getElementById('page-title').textContent = 'Edit Soal';
                }
            }

            // Type Selector
            function selectType(type) {
                selectedType = type;
                document.querySelectorAll('.type-btn').forEach(btn => {
                    if (btn.dataset.type === type) {
                        btn.style.borderColor = 'var(--accent)';
                        btn.style.backgroundColor = 'var(--accent-light)';
                        btn.style.color = 'var(--accent)';
                    } else {
                        btn.style.borderColor = 'var(--border-color)';
                        btn.style.backgroundColor = 'var(--bg-surface)';
                        btn.style.color = 'var(--text-secondary)';
                    }
                });

                // Initialize options based on type
                if (type === 'TRUE_FALSE') {
                    options = [
                        { label: 'A', content: 'Benar', is_correct: false, order: 0 },
                        { label: 'B', content: 'Salah', is_correct: false, order: 1 },
                    ];
                    document.getElementById('add-option-btn').classList.add('hidden');
                } else if (type === 'SINGLE_CHOICE') {
                    options = [
                        { label: 'A', content: '', is_correct: false, order: 0 },
                        { label: 'B', content: '', is_correct: false, order: 1 },
                    ];
                    document.getElementById('add-option-btn').classList.remove('hidden');
                } else if (type === 'MULTIPLE_CHOICE') {
                    options = [
                        { label: 'A', content: '', is_correct: false, order: 0 },
                        { label: 'B', content: '', is_correct: false, order: 1 },
                    ];
                    document.getElementById('add-option-btn').classList.remove('hidden');
                }

                renderOptions();
            }

            window.selectType = selectType;

            // Render Options
            function renderOptions() {
                const container = document.getElementById('options-container');

                if (!selectedType) {
                    container.innerHTML = '<p class="text-sm text-gray-500 dark:text-gray-400">Select a question type first.</p>';
                    return;
                }

                container.innerHTML = options.map((opt, idx) => {
                    let correctInput = '';
                    if (selectedType === 'SINGLE_CHOICE') {
                        correctInput = `<input type="radio" name="correct-option" ${opt.is_correct ? 'checked' : ''} onchange="setCorrectSingle(${idx})" style="accent-color: var(--accent);" class="h-4 w-4">`;
                    } else if (selectedType === 'MULTIPLE_CHOICE') {
                        correctInput = `<input type="checkbox" ${opt.is_correct ? 'checked' : ''} onchange="toggleCorrectMultiple(${idx})" style="accent-color: var(--accent);" class="h-4 w-4 rounded">`;
                    } else if (selectedType === 'TRUE_FALSE') {
                        correctInput = `<input type="radio" name="correct-option" ${opt.is_correct ? 'checked' : ''} onchange="setCorrectSingle(${idx})" style="accent-color: var(--accent);" class="h-4 w-4">`;
                    }

                    const isFixed = selectedType === 'TRUE_FALSE';
                    const removeBtn = !isFixed && options.length > 2
                        ? `<button type="button" onclick="removeOptionRow(${idx})" class="btn-ghost btn-sm text-[var(--danger)]">Remove</button>`
                        : '';

                    return `
                    <div class="flex items-center gap-3 bg-[var(--bg-surface)] rounded-lg p-3 border border-[var(--border-color)]">
                        <span class="text-sm font-bold text-[var(--accent)] w-6">${escHtml(opt.label)}</span>
                        ${correctInput}
                        <input type="text" value="${escHtml(opt.content)}" onchange="updateOptionContent(${idx}, this.value)"
                            ${isFixed ? 'readonly' : ''}
                            class="flex-1 input-field ${isFixed ? 'bg-[var(--bg-hover)] cursor-default' : ''}"
                            placeholder="${isFixed ? opt.content : 'Option content...'}">
                        ${removeBtn}
                    </div>`;
                }).join('');
            }

            // Option management functions
            window.addOptionRow = function () {
                if (selectedType === 'TRUE_FALSE') return;
                const nextLetter = LETTERS[options.length] || LETTERS[LETTERS.length - 1];
                options.push({
                    label: nextLetter,
                    content: '',
                    is_correct: false,
                    order: options.length,
                });
                renderOptions();
            };

            window.removeOptionRow = function (idx) {
                if (options.length <= 2) return;
                options.splice(idx, 1);
                // Re-label
                options.forEach((opt, i) => {
                    opt.label = LETTERS[i];
                    opt.order = i;
                });
                renderOptions();
            };

            window.updateOptionContent = function (idx, value) {
                options[idx].content = value;
            };

            window.setCorrectSingle = function (idx) {
                options.forEach((opt, i) => { opt.is_correct = (i === idx); });
                renderOptions();
            };

            window.toggleCorrectMultiple = function (idx) {
                options[idx].is_correct = !options[idx].is_correct;
                renderOptions();
            };

            // Image handling
            window.handleImageDrop = async function (event) {
                event.preventDefault();
                event.currentTarget.style.borderColor = 'var(--border-color)';
                const file = event.dataTransfer.files[0];
                if (file && file.type.startsWith('image/')) {
                    await uploadImage(file);
                }
            };

            window.handleImageFileSelect = async function (event) {
                const file = event.target.files[0];
                if (file) {
                    await uploadImage(file);
                }
            };

            async function uploadImage(file) {
                try {
                    const formData = new FormData();
                    formData.append('image', file);

                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch('/api/admin/questions/upload-image', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });

                    if (!response.ok) {
                        const err = await response.json().catch(() => ({ message: 'Upload failed' }));
                        throw new Error(err.message || 'Upload failed');
                    }

                    const data = await response.json();
                    uploadedImageUrl = data.url;
                    document.getElementById('input-image-url').value = data.url;
                    document.getElementById('image-preview').src = data.url;
                    document.getElementById('image-preview-container').classList.remove('hidden');
                    document.getElementById('image-upload-area').classList.add('hidden');
                } catch (err) {
                    showToast('Image upload error: ' + err.message, 'error');
                }
            }

            window.removeImage = function () {
                uploadedImageUrl = '';
                document.getElementById('input-image-url').value = '';
                document.getElementById('image-preview-container').classList.add('hidden');
                document.getElementById('image-upload-area').classList.remove('hidden');
                document.getElementById('image-file-input').value = '';
            };

            // Go Back
            window.goBack = function () {
                if (tryoutId) {
                    navigateTo('/admin/tryouts/' + tryoutId + '/questions');
                } else {
                    navigateTo('/admin/tryouts');
                }
            };

            // Load existing question for edit
            async function loadQuestionData() {
                if (!isEditMode || !questionId) return;

                try {
                    // Fetch question - we need to find it from the tryout questions
                    if (!tryoutId) {
                        showToast('Tryout ID not found', 'error');
                        return;
                    }
                    const questions = await apiFetch('/admin/tryouts/' + tryoutId + '/questions');
                    const question = questions.find(q => q.id == questionId);
                    if (!question) {
                        showToast('Question not found', 'error');
                        goBack();
                        return;
                    }

                    selectedType = question.type;
                    selectType(question.type);

                    document.getElementById('input-content').value = question.content || '';
                    document.getElementById('input-points').value = question.points || 1;
                    document.getElementById('input-explanation').value = question.explanation || '';

                    if (question.image_url) {
                        uploadedImageUrl = question.image_url;
                        document.getElementById('input-image-url').value = question.image_url;
                        document.getElementById('image-preview').src = question.image_url;
                        document.getElementById('image-preview-container').classList.remove('hidden');
                        document.getElementById('image-upload-area').classList.add('hidden');
                    }

                    // Load options
                    if (question.options && question.options.length) {
                        options = question.options.map(o => ({
                            id: o.id,
                            label: o.label,
                            content: o.content,
                            is_correct: o.is_correct,
                            order: o.order,
                        }));
                        renderOptions();
                    }
                } catch (err) {
                    showToast('Error loading question: ' + err.message, 'error');
                }
            }

            // Form Submit
            document.getElementById('question-form').addEventListener('submit', async (e) => {
                e.preventDefault();

                if (!selectedType) {
                    showToast('Please select a question type', 'warning');
                    return;
                }

                // Validate options content (except TRUE_FALSE which is pre-filled)
                if (selectedType !== 'TRUE_FALSE') {
                    const emptyOptions = options.filter(o => !o.content.trim());
                    if (emptyOptions.length) {
                        showToast('All options must have content', 'warning');
                        return;
                    }
                }

                // Validate at least one correct answer
                const hasCorrect = options.some(o => o.is_correct);
                if (!hasCorrect) {
                    showToast('At least one option must be marked as correct', 'warning');
                    return;
                }

                const imageUrl = document.getElementById('input-image-url').value || uploadedImageUrl || null;

                const payload = {
                    tryout_id: parseInt(tryoutId),
                    type: selectedType,
                    content: document.getElementById('input-content').value,
                    image_url: imageUrl,
                    points: parseInt(document.getElementById('input-points').value) || 1,
                    explanation: document.getElementById('input-explanation').value || null,
                    options: options.map((opt, idx) => ({
                        id: opt.id || undefined,
                        label: opt.label || LETTERS[idx],
                        content: opt.content,
                        is_correct: opt.is_correct,
                        order: idx,
                    })),
                };

                // Remove undefined id from options
                payload.options = payload.options.map(o => {
                    const cleaned = { ...o };
                    if (!cleaned.id) delete cleaned.id;
                    return cleaned;
                });

                try {
                    if (isEditMode) {
                        await apiFetch('/admin/questions/' + questionId, {
                            method: 'PATCH',
                            body: JSON.stringify(payload),
                        });
                        showToast('Soal berhasil diupdate!', 'success');
                    } else {
                        await apiFetch('/admin/questions', {
                            method: 'POST',
                            body: JSON.stringify(payload),
                        });
                        showToast('Soal berhasil dibuat!', 'success');
                    }
                    goBack();
                } catch (err) {
                    showToast(err.message, 'error');
                }
            });

            // Initialize
            parseParams();
            if (isEditMode) {
                loadQuestionData();
            }
        })();
    </script>
@endpush