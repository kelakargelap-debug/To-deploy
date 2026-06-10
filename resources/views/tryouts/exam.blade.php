@extends('app')

@section('content')
    <div id="exam-page" class="min-h-screen bg-[var(--bg-default)]">
        <!-- Top bar -->
        <div class="sticky top-0 z-20 bg-[var(--bg-surface)] border-b border-[var(--border-default)] px-4 py-3 shadow-sm">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <h2 id="exam-title" class="text-lg font-semibold text-[var(--text-primary)] truncate">Loading...</h2>
                <div class="flex items-center gap-4">
                    <div id="exam-timer"
                        class="flex items-center gap-2 px-4 py-2 bg-[var(--danger-subtle)] border border-[var(--danger)] rounded-lg text-[var(--danger)]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span id="timer-display" class="font-bold text-lg font-mono">00:00:00</span>
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
        <div class="max-w-7xl mx-auto p-4 flex flex-col md:flex-row gap-6">
            <!-- Question content (left panel) -->
            <div class="flex-1 min-w-0 order-1">
                <div id="question-content" class="card min-h-[400px]">
                    <!-- Loading state -->
                    <div id="question-loading" class="text-center py-12">
                        <svg class="w-8 h-8 text-[var(--text-muted)] animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        <p class="text-[var(--text-secondary)] mt-3">Memuat soal...</p>
                    </div>

                    <!-- Actual question content (hidden until loaded) -->
                    <div id="question-display" class="hidden animate-fade-in-up">
                        <!-- Question number + type badge -->
                        <div class="flex items-center justify-between mb-6 pb-4 border-b border-[var(--border-subtle)]">
                            <h3 id="question-number" class="text-xl font-bold text-[var(--text-primary)]">Soal 1
                            </h3>
                            <span id="question-type-badge" class="badge badge-neutral">SINGLE</span>
                        </div>

                        <!-- Question content (HTML rendered) -->
                        <div id="question-text" class="mb-6 text-[var(--text-primary)] text-[1.067rem] leading-relaxed prose dark:prose-invert max-w-none"></div>

                        <!-- Question image (if any) -->
                        <div id="question-image" class="hidden mb-6">
                            <img id="question-img" class="max-w-full rounded-lg border border-[var(--border-default)]"
                                alt="Gambar soal">
                        </div>

                        <!-- Options -->
                        <div id="question-options" class="space-y-3 mb-8">
                            <!-- Options loaded via JS -->
                        </div>

                        <!-- Ragu-ragu toggle -->
                        <div
                            class="flex items-center gap-3 mb-8 p-4 bg-[var(--warning-subtle)] border border-[var(--warning)] rounded-lg text-[var(--warning)]">
                            <input type="checkbox" id="doubt-toggle" onchange="toggleDoubt()"
                                class="w-5 h-5 rounded text-[var(--warning)] focus:ring-[var(--warning)] border-[var(--warning)]">
                            <label for="doubt-toggle"
                                class="text-sm font-medium cursor-pointer">Ragu-ragu dengan jawaban ini</label>
                        </div>

                        <!-- Prev/Next navigation -->
                        <div class="flex items-center justify-between pt-4 border-t border-[var(--border-subtle)]">
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

            <!-- Question number grid (right panel) -->
            <div id="question-grid-panel"
                class="w-full md:w-80 flex-none order-2 card md:sticky top-20 h-fit">
                <h3 class="text-sm font-semibold text-[var(--text-primary)] mb-4">Navigasi Soal</h3>
                <div id="question-grid" class="grid grid-cols-5 gap-2">
                    <!-- Question buttons loaded via JS -->
                </div>
                
                <div class="mt-6 pt-4 border-t border-[var(--border-subtle)] space-y-2">
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-4 h-4 rounded bg-[var(--success)] inline-block"></span>
                        <span class="text-[var(--text-secondary)]">Terjawab</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-4 h-4 rounded bg-[var(--warning)] inline-block"></span>
                        <span class="text-[var(--text-secondary)]">Ragu-ragu</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="w-4 h-4 rounded bg-[var(--bg-subtle)] border border-[var(--border-default)] inline-block"></span>
                        <span class="text-[var(--text-secondary)]">Belum dijawab</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit confirmation modal -->
    <x-modal id="submit-modal" title="Konfirmasi Selesai">
        <p class="text-sm text-[var(--text-secondary)] mb-4">Apakah kamu yakin ingin menyelesaikan ujian ini?</p>
        <div id="submit-summary" class="mb-6 p-4 bg-[var(--bg-subtle)] rounded-lg text-sm border border-[var(--border-subtle)] space-y-2">
            <div class="flex justify-between"><span class="text-[var(--text-secondary)]">Total soal:</span> <span id="summary-total" class="font-bold text-[var(--text-primary)]">0</span></div>
            <div class="flex justify-between"><span class="text-[var(--text-secondary)]">Terjawab:</span> <span id="summary-answered" class="font-bold text-[var(--success)]">0</span></div>
            <div class="flex justify-between"><span class="text-[var(--text-secondary)]">Ragu-ragu:</span> <span id="summary-doubt" class="font-bold text-[var(--warning)]">0</span></div>
            <div class="flex justify-between"><span class="text-[var(--text-secondary)]">Belum dijawab:</span> <span id="summary-unanswered" class="font-bold text-[var(--danger)]">0</span></div>
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
    </x-modal>

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
            window.examSubmitted = false;

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

            // Tab / Reload close protection
            window.addEventListener('beforeunload', function (e) {
                if (!window.examSubmitted) {
                    e.preventDefault();
                    e.returnValue = 'Kamu memiliki ujian yang sedang berjalan. Yakin ingin keluar?';
                    return e.returnValue;
                }
            });

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
                    startHeartbeat();
                    renderQuestionGrid();
                    showQuestion(snapshot.currentIndex || 0);
                }).catch(function (err) {
                    console.error('Failed to start exam:', err);
                    showToast('Gagal memuat ujian: ' + err.message, 'error');
                    setTimeout(function() { navigateTo('{{ route("tryouts") }}'); }, 2000);
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

            // Heartbeat to keep session alive and check takeover
            var heartbeatInterval = null;
            function startHeartbeat() {
                heartbeatInterval = setInterval(function() {
                    apiFetch('/tryouts/' + tryoutSlug + '/heartbeat', { method: 'POST' })
                        .catch(function(err) {
                            if (err.message === 'SESSION_TAKEN_OVER') {
                                clearInterval(heartbeatInterval);
                                clearInterval(timerInterval);
                            }
                        });
                }, 60000); // 1 minute
            }

            function autoSubmit() {
                showToast('Waktu ujian telah habis! Jawaban akan disimpan secara otomatis.', 'warning');
                submitExam();
            }

            // Question navigation
            function renderQuestionGrid() {
                var grid = document.getElementById('question-grid');
                grid.innerHTML = questions.map(function (q, i) {
                    var state = getQuestionState(q.id);
                    var colorClass = '';
                    if (state === 'answered') colorClass = 'bg-[var(--success)] text-white';
                    else if (state === 'doubt') colorClass = 'bg-[var(--warning)] text-white';
                    else colorClass = 'bg-[var(--bg-subtle)] border border-[var(--border-default)] text-[var(--text-secondary)]';

                    return '<button onclick="showQuestion(' + i + ')" class="w-9 h-9 rounded-lg text-sm font-medium flex items-center justify-center transition-all ' + colorClass + '" id="qbtn-' + i + '">' + (i + 1) + '</button>';
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

                    var activeClass = isChecked ? 'bg-[var(--accent-subtle)] border-[var(--accent)]' : 'border-[var(--border-default)] hover:bg-[var(--bg-subtle)]';

                    return '<label class="flex items-start gap-3 p-4 border rounded-xl transition-colors cursor-pointer ' + activeClass + '">' +
                        '<input type="' + inputType + '" name="' + inputName + '" value="' + optId + '" ' + (isChecked ? 'checked' : '') + ' onchange="handleOptionChange(' + q.id + ', \'' + optId + '\', ' + isMultiple + ')" class="w-5 h-5 mt-0.5 rounded text-[var(--accent)] focus:ring-[var(--accent)]">' +
                        '<div>' +
                        '<span class="font-bold text-[var(--text-primary)] mr-2">' + optionLabel + '.</span>' +
                        '<span class="text-[var(--text-primary)] leading-relaxed">' + (opt.content || opt.text || opt.description || '') + '</span>' +
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
                    btn.classList.toggle('ring-[var(--accent)]', bi === index);
                    btn.classList.toggle('ring-offset-2', bi === index);
                    if(document.documentElement.classList.contains('dark')) {
                        btn.classList.toggle('ring-offset-[var(--bg-surface)]', bi === index);
                    }
                });

                // Save answers to localStorage
                saveAnswers();
            };

            window.handleOptionChange = function (qId, optId, isMultiple) {
                optId = Number(optId) || optId; // ensure consistent type
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
                } else {
                    // Jika di soal terakhir, tombol "Selesai" memanggil konfirmasi submit
                    confirmSubmit();
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
                openModal('submit-modal');
            };

            window.closeSubmitModal = function () {
                closeModal('submit-modal');
            };

            window.submitExam = function () {
                var submitBtn = document.getElementById('confirm-submit-btn');
                submitBtn.disabled = true;
                submitBtn.textContent = 'Menyimpan...';

                clearInterval(timerInterval);


                apiFetch('/tryouts/' + tryoutSlug + '/submit', {
                    method: 'POST',
                    body: JSON.stringify({ attempt_id: attemptId })
                }).then(function (data) {
                    window.examSubmitted = true; // Set flag to true to disable exit warnings
                    navigateTo('/tryouts/' + tryoutSlug + '/result/' + (data.attempt_id || attemptId));
                }).catch(function (err) {
                    console.error('Submit failed:', err);
                    showToast('Gagal menyimpan jawaban: ' + err.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Ya, Selesai';

                });
            };

            // Start loading exam
            loadExam();
        })();
    </script>
@endsection