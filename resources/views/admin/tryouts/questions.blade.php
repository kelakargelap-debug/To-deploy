@extends('app')

@section('content')
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <button onclick="navigateTo('/admin/tryouts')" class="btn-ghost mb-4">
                &larr; Back to Tryouts
            </button>
            <h1 class="text-2xl font-bold text-[var(--text-primary)]">Questions for: <span id="tryout-title" class="text-[var(--accent)]">Loading...</span></h1>
        </div>

        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-[var(--text-secondary)]" id="question-count">Loading questions...</p>
            <button onclick="openAddQuestion()" class="btn-primary">
                + Tambah Soal
            </button>
        </div>

        {{-- Questions List --}}
        <div id="questions-list" class="space-y-3">
            <div class="bg-[var(--bg-surface)] rounded-lg shadow-sm border border-[var(--border-color)] p-8 text-center text-[var(--text-secondary)]">
                Loading questions...
            </div>
        </div>
    </div>

    {{-- Delete Question Confirmation Modal --}}
    <x-modal id="delete-question-modal" title="Delete Question" size="sm">
        <p class="text-sm text-[var(--text-secondary)] mb-4">Are you sure you want to delete this question? This action cannot be undone.</p>
        <div class="flex gap-3 justify-end">
            <button data-modal-close="delete-question-modal" class="btn-secondary">Cancel</button>
            <button onclick="submitDeleteQuestion()" class="btn-danger">Delete</button>
        </div>
    </x-modal>
@endsection

@push('scripts')
    <script>
        (function () {
            const tryoutId = {!! isset($id) ? $id : '0' !!};
            let questions = [];
            let deleteQuestionId = null;

            function escHtml(str) {
                const div = document.createElement('div');
                div.textContent = str || '';
                return div.innerHTML;
            }

            function typeBadge(type) {
                const colors = {
                    'SINGLE_CHOICE': 'badge-primary',
                    'MULTIPLE_CHOICE': 'badge-warning',
                    'TRUE_FALSE': 'badge-success',
                };
                const cls = colors[type] || 'badge-free';
                return '<span class="badge ' + cls + '">' + type + '</span>';
            }

            function truncate(str, len) {
                if (!str) return '-';
                return str.length > len ? str.substring(0, len) + '...' : str;
            }

            async function loadData() {
                if (!tryoutId) {
                    document.getElementById('tryout-title').textContent = 'Error: Tryout ID not found';
                    return;
                }

                try {
                    // Load tryout info
                    const data = await apiFetch('/tryouts');
                    const tryouts = Array.isArray(data) ? data : (data.data || []);
                    const tryout = tryouts.find(t => t.id == tryoutId);
                    if (tryout) {
                        document.getElementById('tryout-title').textContent = tryout.title;
                    }

                    // Load questions
                    questions = await apiFetch('/admin/tryouts/' + tryoutId + '/questions');
                    renderQuestions();
                } catch (err) {
                    document.getElementById('questions-list').innerHTML = `
                        <div class="bg-[var(--bg-surface)] rounded-lg shadow-sm border border-[var(--border-color)] p-8 text-center text-[var(--danger)]">
                            Error: ${escHtml(err.message)}
                        </div>`;
                }
            }

            function renderQuestions() {
                const list = document.getElementById('questions-list');
                document.getElementById('question-count').textContent = questions.length + ' questions';

                if (questions.length === 0) {
                    list.innerHTML = `
                        <div class="bg-[var(--bg-surface)] rounded-lg shadow-sm border border-[var(--border-color)] p-8 text-center text-[var(--text-secondary)]">
                            No questions yet. Click "Tambah Soal" to add one.
                        </div>`;
                    return;
                }

                list.innerHTML = questions.map(q => {
                    const correctCount = q.options ? q.options.filter(o => o.is_correct).length : 0;
                    return `
                        <div class="bg-[var(--bg-surface)] rounded-lg shadow-sm border border-[var(--border-color)] p-5 transition-colors hover:bg-[var(--bg-hover)]">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        ${typeBadge(q.type)}
                                        <span class="text-xs text-[var(--text-secondary)]">Order: ${q.order}</span>
                                        <span class="text-xs font-medium text-[var(--accent)]">${q.points} pts</span>
                                    </div>
                                    <p class="text-sm text-[var(--text-primary)] mb-1">${escHtml(truncate(q.content, 120))}</p>
                                    <p class="text-xs text-[var(--text-secondary)]">${correctCount} correct answer(s)</p>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button onclick="navigateTo('/admin/questions/${q.id}/edit?tryout_id=${tryoutId}')" class="btn-ghost btn-sm text-[var(--info)]">Edit</button>
                                    <button onclick="openDeleteQuestionModal(${q.id})" class="btn-ghost btn-sm text-[var(--danger)]">Delete</button>
                                </div>
                            </div>
                        </div>`;
                }).join('');
            }

            // Add Question
            window.openAddQuestion = function () {
                navigateTo('/admin/questions/create?tryout_id=' + tryoutId);
            };

            // Delete Modal
            window.openDeleteQuestionModal = function (qId) {
                deleteQuestionId = qId;
                openModal('delete-question-modal');
            };

            window.submitDeleteQuestion = async function () {
                try {
                    await apiFetch('/admin/questions/' + deleteQuestionId, { method: 'DELETE' });
                    showToast('Soal berhasil dihapus!', 'success');
                    closeModal('delete-question-modal');
                    loadData();
                } catch (err) {
                    showToast(err.message, 'error');
                }
            };

            loadData();
        })();
    </script>
@endpush