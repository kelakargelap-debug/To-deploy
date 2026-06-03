@extends('app')

@section('content')
    <div class="p-6 max-w-7xl mx-auto">
        <div class="flex items-center gap-4 mb-6">
            <button onclick="navigateTo('/admin/tryouts')"
                class="px-3 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                &larr; Back to Tryouts
            </button>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">Questions for: <span id="tryout-title"
                    class="text-indigo-600 dark:text-indigo-400">Loading...</span></h1>
        </div>

        <div class="flex items-center justify-between mb-4">
            <p class="text-sm text-gray-600 dark:text-gray-400" id="question-count">Loading questions...</p>
            <button onclick="openAddQuestion()"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium transition-colors">
                + Tambah Soal
            </button>
        </div>

        {{-- Questions List --}}
        <div id="questions-list" class="space-y-3">
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-8 text-center text-gray-500 dark:text-gray-400">
                Loading questions...
            </div>
        </div>
    </div>

    {{-- Delete Question Confirmation Modal --}}
    <div id="delete-question-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" onclick="closeDeleteQuestionModal()"></div>
        <div
            class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-full max-w-md mx-4 border border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-red-600 dark:text-red-400 mb-4">Delete Question</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Are you sure you want to delete this question? This
                action cannot be undone.</p>
            <div class="flex gap-3 justify-end">
                <button onclick="closeDeleteQuestionModal()"
                    class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg text-sm font-medium hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">Cancel</button>
                <button onclick="submitDeleteQuestion()"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-sm font-medium transition-colors">Delete</button>
            </div>
        </div>
    </div>
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
                    'SINGLE_CHOICE': 'bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300',
                    'MULTIPLE_CHOICE': 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300',
                    'TRUE_FALSE': 'bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300',
                };
                const cls = colors[type] || 'bg-gray-100 dark:bg-gray-600 text-gray-600 dark:text-gray-300';
                return '<span class="px-2 py-1 rounded text-xs font-medium ' + cls + '">' + type + '</span>';
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
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-8 text-center text-red-500">
                            Error: ${escHtml(err.message)}
                        </div>`;
                }
            }

            function renderQuestions() {
                const list = document.getElementById('questions-list');
                document.getElementById('question-count').textContent = questions.length + ' questions';

                if (questions.length === 0) {
                    list.innerHTML = `
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-8 text-center text-gray-500 dark:text-gray-400">
                            No questions yet. Click "Tambah Soal" to add one.
                        </div>`;
                    return;
                }

                list.innerHTML = questions.map(q => {
                    const correctCount = q.options ? q.options.filter(o => o.is_correct).length : 0;
                    return `
                        <div class="bg-white dark:bg-gray-800 rounded-lg shadow border border-gray-200 dark:border-gray-700 p-5">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        ${typeBadge(q.type)}
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Order: ${q.order}</span>
                                        <span class="text-xs font-medium text-indigo-600 dark:text-indigo-400">${q.points} pts</span>
                                    </div>
                                    <p class="text-sm text-gray-900 dark:text-gray-100 mb-1">${escHtml(truncate(q.content, 120))}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">${correctCount} correct answer(s)</p>
                                </div>
                                <div class="flex gap-2 shrink-0">
                                    <button onclick="navigateTo('/admin/questions/${q.id}/edit?tryout_id=${tryoutId}')" class="px-3 py-1.5 text-xs bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 rounded hover:bg-blue-200 dark:hover:bg-blue-800 transition-colors font-medium">Edit</button>
                                    <button onclick="openDeleteQuestionModal(${q.id})" class="px-3 py-1.5 text-xs bg-red-100 dark:bg-red-900 text-red-700 dark:text-red-300 rounded hover:bg-red-200 dark:hover:bg-red-800 transition-colors font-medium">Delete</button>
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
                document.getElementById('delete-question-modal').classList.remove('hidden');
            };

            window.closeDeleteQuestionModal = function () {
                document.getElementById('delete-question-modal').classList.add('hidden');
                deleteQuestionId = null;
            };

            window.submitDeleteQuestion = async function () {
                try {
                    await apiFetch('/admin/questions/' + deleteQuestionId, { method: 'DELETE' });
                    alert('Soal berhasil dihapus!');
                    closeDeleteQuestionModal();
                    loadData();
                } catch (err) {
                    alert('Error: ' + err.message);
                }
            };

            loadData();
        })();
    </script>
@endpush