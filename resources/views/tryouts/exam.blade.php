@extends('app')

@section('content')
    <div id="exam-page" class="min-h-screen bg-gray-50 dark:bg-gray-900">
        <!-- Top bar -->
        <div class="sticky top-0 z-20 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3">
            <div class="max-w-6xl mx-auto flex items-center justify-between">
                <h2 id="exam-title" class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate">Loading...</h2>
                <div class="flex items-center gap-4">
                    <div id="exam-timer"
                        class="flex items-center gap-2 px-4 py-2 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="timer-display" class="font-bold text-red-600 dark:text-red-400 text-lg">00:00:00</span>
                    </div>
                    <button id="submit-exam-btn" onclick="confirmSubmit()" class="btn-danger flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Selesai
                    </button>
                </div>
            </div>
        </div>

        <!-- Main content -->
        <div class="max-w-6xl mx-auto p-4 flex gap-4">
            <!-- Question number grid (left panel) -->
            <div id="question-grid-panel"
                class="w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 sticky top-20 h-fit">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Navigasi Soal</h3>
                <div id="question-grid" class="grid grid-cols-5 gap-2">
                    <!-- Question buttons loaded via JS -->
                </div>
                <div class="mt-3 flex items-center gap-2 text-xs">
                    <span class="w-4 h-4 rounded bg-green-500 inline-block"></span><span
                        class="text-gray-500 dark:text-gray-400">Terjawab</span>
                </div>
                <div class="mt-1 flex items-center gap-2 text-xs">
                    <span class="w-4 h-4 rounded bg-amber-500 inline-block"></span><span
                        class="text-gray-500 dark:text-gray-400">Ragu-ragu</span>
                </div>
                <div class="mt-1 flex items-center gap-2 text-xs">
                    <span class="w-4 h-4 rounded bg-gray-300 dark:bg-gray-600 inline-block"></span><span
                        class="text-gray-500 dark:text-gray-400">Belum dijawab</span>
                </div>
            </div>

            <!-- Question content (right panel) -->
            <div class="flex-1">
                <div id="question-content" class="card">
                    <!-- Loading state -->
                    <div id="question-loading" class="text-center py-8">
                        <svg class="w-8 h-8 text-gray-400 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400 mt-2">Memuat soal...</p>
                    </div>

                    <!-- Actual question content (hidden until loaded) -->
                    <div id="question-display" class="hidden">
                        <!-- Question number + type badge -->
                        <div class="flex items-center justify-between mb-4">
                            <h3 id="question-number" class="text-lg font-semibold text-gray-900 dark:text-gray-100">Soal 1
                            </h3>
                            <span id="question-type-badge" class="badge badge-free text-xs">SINGLE</span>
                        </div>

                        <!-- Question content (HTML rendered) -->
                        <div id="question-text" class="mb-6 text-gray-900 dark:text-gray-100 leading-relaxed"></div>

                        <!-- Question image (if any) -->
                        <div id="question-image" class="hidden mb-6">
                            <img id="question-img" class="max-w-full rounded-lg border border-gray-200 dark:border-gray-700"
                                alt="Gambar soal">
                        </div>

                        <!-- Options -->
                        <div id="question-options" class="space-y-3 mb-6">
                            <!-- Options loaded via JS -->
                        </div>

                        <!-- Ragu-ragu toggle -->
                        <div
                            class="flex items-center gap-3 mb-6 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">
                            <input type="checkbox" id="doubt-toggle" onchange="toggleDoubt()"
                                class="w-5 h-5 rounded text-amber-600 focus:ring-amber-500 border-amber-300 dark:border-amber-600">
                            <label for="doubt-toggle"
                                class="text-sm font-medium text-amber-700 dark:text-amber-300">Ragu-ragu dengan jawaban
                                ini</label>
                        </div>

                        <!-- Prev/Next navigation -->
                        <div class="flex items-center justify-between">
                            <button id="prev-btn" onclick="prevQuestion()" class="btn-secondary flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L11.25 15l4.5-4.5" />
                                </svg>
                                Sebelumnya
                            </button>
                            <button id="next-btn" onclick="nextQuestion()" class="btn-primary flex items-center gap-2">
                                Selanjutnya
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l4.5 4.5-4.5 4.5" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit confirmation modal -->
    <div id="submit-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/50" onclick="closeSubmitModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Konfirmasi Selesai</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Apakah kamu yakin ingin menyelesaikan ujian ini?</p>
            <div id="submit-summary" class="mb-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg text-sm">
                <p class="text-gray-700 dark:text-gray-300">Total soal: <span id="summary-total"
                        class="font-medium">0</span></p>
                <p class="text-green-600 dark:text-green-400">Terjawab: <span id="summary-answered"
                        class="font-medium">0</span></p>
                <p class="text-amber-600 dark:text-amber-400">Ragu-ragu: <span id="summary-doubt"
                        class="font-medium">0</span></p>
                <p class="text-gray-500 dark:text-gray-400">Belum dijawab: <span id="summary-unanswered"
                        class="font-medium">0</span></p>
            </div>
            <div class="flex gap-3 justify-end">
                <button onclick="closeSubmitModal()" class="btn-secondary">Batal</button>
                <button onclick="submitExam()" id="confirm-submit-btn" class="btn-danger flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Ya, Selesai
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var tryoutSlug = '{{ $slug }}';
            var attemptId = null;
            var questions = [];
            var currentQuestionIndex = 0;
            var answers = {};
            var timerInterval = null;
            var remainingSeconds = 0;
            var endTime = null;
            var STORAGE_KEY = 'skb_exam_answers_' + tryoutSlug;

            // Copy protection
            function disableCopy(e) { e.preventDefault(); return false; }
            document.addEventListener('copy', disableCopy);
            document.addEventListener('cut', disableCopy);
            document.addEventListener('contextmenu', function (e) { e.preventDefault(); });

            document.addEventListener('keydown', function (e) {
                if ((e.ctrlKey || e.metaKey) && (
                    e.key === 'c' || e.key === 'C' ||
                    e.key === 'u' || e.key === 'U' ||
                    e.key === 's' || e.key === 'S' ||
                    e.key === 'a' || e.key === 'A'
                )) {
                    e.preventDefault();
                    return false;
                }
                if (e.key === 'F12' || (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i' || e.key === 'J' || e.key === 'j'))) {
                    e.preventDefault();
                    return false;
                }
            });

            // Page leave warning
            window.onbeforeunload = function (e) {
                e.preventDefault();
                e.returnValue = 'Kamu memiliki ujian yang sedang berjalan. Yakin ingin keluar?';
            };

            // Load exam data
            function loadExam() {
                apiFetch('/tryouts/' + tryoutSlug + '/start', {
                    method: 'POST'
                }).then(function (data) {
                    attemptId = data.attemptId || data.attempt_id || data.id;
                    endTime = new Date(data.expiresAt || data.expires_at);

                    // Now fetch questions
                    return apiFetch('/tryouts/' + tryoutSlug + '/questions');
                }).then(function (data) {
                    questions = data.questions || [];
                    var snapshot = data.snapshot || {};
                    var savedAnswers = snapshot.savedAnswers || {};
                    var doubtAnswers = snapshot.doubtAnswers || {};

                    // Calculate remaining seconds
                    remainingSeconds = Math.max(0, Math.floor((new Date(data.expiresAt || data.expires_at) - new Date()) / 1000));

                    // Restore saved answers
                    questions.forEach(function (q) {
                        var qId = q.id;
                        var opts = savedAnswers[qId] || [];
                        var doubt = doubtAnswers[qId] || false;
                        answers[qId] = { optionIds: Array.isArray(opts) ? opts : (opts ? [opts] : []), doubt: doubt };
                    });

                    document.getElementById('exam-title').textContent = tryoutSlug.replace(/-/g, ' ').replace(/\b\w/g, function (l) { return l.toUpperCase(); });
                    document.getElementById('question-loading').classList.add('hidden');
                    document.getElementById('question-display').classList.remove('hidden');

                    startTimer();
                    renderQuestionGrid();
                    showQuestion(snapshot.currentIndex || 0);
                }).catch(function (err) {
                    console.error('Failed to start exam:', err);
                    alert('Gagal memuat ujian: ' + err.message);
                    navigateTo('{{ route("tryouts") }}');
                });
            }

            // Timer
            function startTimer() {
                updateTimerDisplay();
                timerInterval = setInterval(function () {
                    remainingSeconds--;
                    if (remainingSeconds <= 0) {
                        clearInterval(timerInterval);
                        autoSubmit();
                        return;
                    }
                    updateTimerDisplay();
                }, 1000);
            }

            function updateTimerDisplay() {
                var hours = Math.floor(remainingSeconds / 3600);
                var minutes = Math.floor((remainingSeconds % 3600) / 60);
                var seconds = remainingSeconds % 60;
                document.getElementById('timer-display').textContent =
                    String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(seconds).padStart(2, '0');

                // Change timer color when low
                var timerEl = document.getElementById('exam-timer');
                if (remainingSeconds <= 300) {
                    timerEl.classList.add('animate-pulse');
                }
            }

            function autoSubmit() {
                alert('Waktu ujian telah habis! Jawaban akan disimpan secara otomatis.');
                submitExam();
            }

            // Question navigation
            function renderQuestionGrid() {
                var grid = document.getElementById('question-grid');
                grid.innerHTML = questions.map(function (q, i) {
                    var state = getQuestionState(q.id);
                    var colorClass = '';
                    if (state === 'answered') colorClass = 'bg-green-500 text-white';
                    else if (state === 'doubt') colorClass = 'bg-amber-500 text-white';
                    else colorClass = 'bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300';

                    return '<button onclick="showQuestion(' + i + ')" class="w-8 h-8 rounded text-xs font-medium flex items-center justify-center ' + colorClass + '" id="qbtn-' + i + '">' + (i + 1) + '</button>';
                }).join('');
            }

            function getQuestionState(qId) {
                var a = answers[qId];
                if (!a) return 'unanswered';
                if (a.optionIds.length > 0 && a.doubt) return 'doubt';
                if (a.optionIds.length > 0) return 'answered';
                return 'unanswered';
            }

            window.showQuestion = function (index) {
                currentQuestionIndex = index;
                var q = questions[index];

                document.getElementById('question-number').textContent = 'Soal ' + (index + 1);
                var typeLabel = { 'SINGLE': 'Pilihan Ganda', 'MULTIPLE_CHOICE': 'Multi Jawaban', 'TRUE_FALSE': 'Benar/Salah' };
                document.getElementById('question-type-badge').textContent = typeLabel[q.type] || q.type || 'SINGLE';

                // Render question content (HTML)
                document.getElementById('question-text').innerHTML = q.content || q.question_text || '';

                // Show image if exists
                if (q.image || q.image_url) {
                    document.getElementById('question-image').classList.remove('hidden');
                    document.getElementById('question-img').src = q.image || q.image_url;
                } else {
                    document.getElementById('question-image').classList.add('hidden');
                }

                // Render options
                var options = q.options || q.choices || [];
                var isMultiple = q.type === 'MULTIPLE_CHOICE';
                var inputType = isMultiple ? 'checkbox' : 'radio';
                var currentAnswers = answers[q.id] ? answers[q.id].optionIds : [];

                var optionsHtml = options.map(function (opt, i) {
                    var optId = opt.id || i;
                    var isChecked = currentAnswers.indexOf(optId) !== -1;
                    var inputName = isMultiple ? 'option-' + q.id + '-' + optId : 'option-' + q.id;
                    var optionLabel = opt.label || opt.text || String.fromCharCode(65 + i);

                    return '<label class="flex items-start gap-3 p-3 border border-gray-200 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors cursor-pointer ' + (isChecked ? 'bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-600' : '') + '">' +
                        '<input type="' + inputType + '" name="' + inputName + '" value="' + optId + '" ' + (isChecked ? 'checked' : '') + ' onchange="handleOptionChange(' + q.id + ', \'' + optId + '\', ' + isMultiple + ')" class="w-5 h-5 mt-0.5 rounded text-blue-600 focus:ring-blue-500">' +
                        '<div>' +
                        '<span class="font-medium text-gray-700 dark:text-gray-300 mr-2">' + optionLabel + '</span>' +
                        '<span class="text-gray-900 dark:text-gray-100">' + (opt.content || opt.text || opt.description || '') + '</span>' +
                        '</div>' +
                        '</label>';
                }).join('');
                document.getElementById('question-options').innerHTML = optionsHtml;

                // Set doubt toggle
                var doubtToggle = document.getElementById('doubt-toggle');
                doubtToggle.checked = answers[q.id] ? answers[q.id].doubt : false;

                // Update navigation buttons
                document.getElementById('prev-btn').disabled = index === 0;
                document.getElementById('prev-btn').classList.toggle('opacity-50', index === 0);
                document.getElementById('next-btn').textContent = index === questions.length - 1 ? 'Selesai' : 'Selanjutnya';

                // Update grid button highlighting
                document.querySelectorAll('#question-grid button').forEach(function (btn, bi) {
                    btn.classList.toggle('ring-2', bi === index);
                    btn.classList.toggle('ring-blue-500', bi === index);
                });

                // Save answers to localStorage
                saveAnswers();
            };

            window.handleOptionChange = function (qId, optId, isMultiple) {
                if (!answers[qId]) answers[qId] = { optionIds: [], doubt: false };

                if (isMultiple) {
                    var idx = answers[qId].optionIds.indexOf(optId);
                    if (idx !== -1) {
                        answers[qId].optionIds.splice(idx, 1);
                    } else {
                        answers[qId].optionIds.push(optId);
                    }
                } else {
                    answers[qId].optionIds = [optId];
                }

                saveAnswers();
                renderQuestionGrid();
                showQuestion(currentQuestionIndex);
            };

            window.toggleDoubt = function () {
                var q = questions[currentQuestionIndex];
                if (!answers[q.id]) answers[q.id] = { optionIds: [], doubt: false };
                answers[q.id].doubt = document.getElementById('doubt-toggle').checked;
                saveAnswers();
                renderQuestionGrid();
            };

            window.prevQuestion = function () {
                if (currentQuestionIndex > 0) {
                    showQuestion(currentQuestionIndex - 1);
                }
            };

            window.nextQuestion = function () {
                if (currentQuestionIndex < questions.length - 1) {
                    showQuestion(currentQuestionIndex + 1);
                }
            };

            function saveAnswers() {
                // Save to server via API
                var q = questions[currentQuestionIndex];
                var a = answers[q.id] || { optionIds: [], doubt: false };
                apiFetch('/tryouts/' + tryoutSlug + '/save-answer', {
                    method: 'POST',
                    body: JSON.stringify({
                        attempt_id: attemptId,
                        question_id: q.id,
                        selectedOptionIds: a.optionIds,
                        isDoubt: a.doubt
                    })
                }).catch(function () { /* ignore save errors */ });
            }

            // Submit
            window.confirmSubmit = function () {
                var answered = 0, doubt = 0, unanswered = 0;
                questions.forEach(function (q) {
                    var state = getQuestionState(q.id);
                    if (state === 'answered') answered++;
                    else if (state === 'doubt') doubt++;
                    else unanswered++;
                });

                document.getElementById('summary-total').textContent = questions.length;
                document.getElementById('summary-answered').textContent = answered;
                document.getElementById('summary-doubt').textContent = doubt;
                document.getElementById('summary-unanswered').textContent = unanswered;
                document.getElementById('submit-modal').classList.remove('hidden');
            };

            window.closeSubmitModal = function () {
                document.getElementById('submit-modal').classList.add('hidden');
            };

            window.submitExam = function () {
                var submitBtn = document.getElementById('confirm-submit-btn');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Menyimpan...';

                clearInterval(timerInterval);
                window.onbeforeunload = null;

                apiFetch('/tryouts/' + tryoutSlug + '/submit', {
                    method: 'POST',
                    body: JSON.stringify({ attempt_id: attemptId })
                }).then(function (data) {
                    navigateTo('/tryouts/' + tryoutSlug + '/result/' + (data.attempt_id || attemptId));
                }).catch(function (err) {
                    console.error('Submit failed:', err);
                    alert('Gagal menyimpan jawaban: ' + err.message);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Ya, Selesai';
                    window.onbeforeunload = function (e) { e.preventDefault(); e.returnValue = ''; };
                });
            };

            // Start loading exam
            loadExam();
        })();
    </script>
@endsection