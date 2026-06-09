@extends('app')

@section('content')
    <div class="max-w-4xl mx-auto">
        <!-- Score display -->
        <div class="card mb-6 text-center animate-fade-in-up">
            <div id="result-score-circle"
                class="w-28 h-28 rounded-full mx-auto flex items-center justify-center text-4xl font-bold mb-4 shadow-sm transition-colors duration-500">
                <span id="result-score">0%</span>
            </div>
            <h2 id="result-status" class="text-2xl font-bold mb-2">-</h2>
            <p class="text-[var(--text-secondary)]">
                <span id="result-correct" class="font-medium text-[var(--text-primary)]">0</span> / <span id="result-total" class="font-medium text-[var(--text-primary)]">0</span> soal benar
            </p>
            <p id="result-tryout-title" class="text-sm text-[var(--text-muted)] mt-2"></p>
        </div>

        <!-- Action buttons -->
        <div class="flex flex-wrap gap-3 mb-8 animate-fade-in-up" style="animation-delay: 50ms;">
            <a href="{{ route('tryouts') }}" class="btn-secondary flex items-center gap-2 flex-1 sm:flex-none justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Kembali ke Tryout
            </a>
            <a href="{{ route('my-attempts') }}" class="btn-primary flex items-center gap-2 flex-1 sm:flex-none justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Lihat Semua Nilai
            </a>
        </div>

        <!-- Per-question review -->
        <div class="mb-4 animate-fade-in-up" style="animation-delay: 100ms;">
            <h2 class="text-lg font-semibold text-[var(--text-primary)]">Review Soal</h2>
        </div>
        <div id="questions-review" class="space-y-5 animate-fade-in-up" style="animation-delay: 150ms;">
            <!-- Loaded via JS -->
            <div class="card skeleton">
                <div class="skeleton-heading w-1/3"></div>
                <div class="skeleton-text w-full mt-4"></div>
                <div class="skeleton-text w-full"></div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            var tryoutSlug = '{{ $slug }}';
            var attemptId = '{{ $attemptId }}';
            var resultData = null;

            function loadResult() {
                apiFetch('/tryouts/' + tryoutSlug + '/result/' + attemptId).then(function (data) {
                    resultData = data;
                    renderResult(data);
                }).catch(function (err) {
                    console.error('Failed to load result:', err);
                    alert('Gagal memuat hasil: ' + err.message);
                    navigateTo('{{ route("tryouts") }}');
                });
            }

            function renderResult(data) {
                var attempt = data.attempt || {};
                var score = attempt.score || 0;
                var totalCorrect = attempt.total_correct || 0;
                var totalQuestions = data.questionsCount || data.questions_count || 0;
                var passingScore = data.passingScore || data.passing_score || 50;
                var isPassed = score >= passingScore;

                // Score circle
                var scoreCircle = document.getElementById('result-score-circle');
                if (isPassed) {
                    scoreCircle.className = 'w-28 h-28 rounded-full mx-auto flex items-center justify-center text-4xl font-bold bg-[var(--success-subtle)] text-[var(--success)] mb-4 shadow-sm transition-colors duration-500';
                    document.getElementById('result-status').textContent = 'LULUS';
                    document.getElementById('result-status').className = 'text-2xl font-bold mb-2 text-[var(--success)]';
                } else {
                    scoreCircle.className = 'w-28 h-28 rounded-full mx-auto flex items-center justify-center text-4xl font-bold bg-[var(--danger-subtle)] text-[var(--danger)] mb-4 shadow-sm transition-colors duration-500';
                    document.getElementById('result-status').textContent = 'TIDAK LULUS';
                    document.getElementById('result-status').className = 'text-2xl font-bold mb-2 text-[var(--danger)]';
                }

                document.getElementById('result-score').textContent = score + '%';
                document.getElementById('result-correct').textContent = totalCorrect;
                document.getElementById('result-total').textContent = totalQuestions;
                document.getElementById('result-tryout-title').textContent = data.tryoutTitle || data.tryout_title || data.title || '';

                // Per-question review
                var questions = data.questions || data.question_reviews || [];

                if (questions.length > 0) {
                    var reviewHtml = questions.map(function (q, i) {
                        var isCorrect = q.isCorrect || q.is_correct || q.correct || false;
                        var userAnswer = q.userSelected || q.user_answer || q.selectedOptions || q.selected_option_ids || [];
                        var options = q.options || q.choices || [];
                        var hasExplanation = q.explanation && q.explanation.trim().length > 0;
                        var isPremiumGated = !data.hasPremiumAccess && hasExplanation;

                        var statusIcon = isCorrect
                            ? '<svg class="w-6 h-6 text-[var(--success)] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                            : '<svg class="w-6 h-6 text-[var(--danger)] shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

                        var optionsHtml = options.map(function (opt, oi) {
                            var optId = opt.id || oi;
                            var isUserAnswer = Array.isArray(userAnswer) ? userAnswer.indexOf(optId) !== -1 : (userAnswer === optId);
                            var isCorrectOption = opt.isCorrect === true;
                            var optBgClass = 'border-[var(--border-default)]';
                            if (isCorrectOption) optBgClass = 'border-[var(--success)] bg-[var(--success-subtle)]';
                            if (isUserAnswer && !isCorrectOption) optBgClass = 'border-[var(--danger)] bg-[var(--danger-subtle)]';
                            if (isUserAnswer && isCorrectOption) optBgClass = 'border-[var(--success)] bg-[var(--success-subtle)]';

                            var marker = '';
                            if (isCorrectOption) marker = '<span class="text-[var(--success)] font-bold text-xs shrink-0">[Benar]</span>';
                            if (isUserAnswer && !isCorrectOption) marker = '<span class="text-[var(--danger)] font-bold text-xs shrink-0">[Jawaban Anda]</span>';
                            if (isUserAnswer && isCorrectOption) marker = '<span class="text-[var(--success)] font-bold text-xs shrink-0">[Jawaban Anda - Benar]</span>';

                            return '<div class="p-3 border rounded-xl ' + optBgClass + ' flex items-start gap-3 text-sm transition-colors">' +
                                '<span class="font-bold text-[var(--text-primary)] w-5 shrink-0">' + (opt.label || String.fromCharCode(65 + oi)) + '.</span>' +
                                '<span class="text-[var(--text-primary)] leading-relaxed flex-1">' + (opt.content || opt.text || '') + '</span>' +
                                marker +
                                '</div>';
                        }).join('');

                        var explanationHtml = '';
                        if (hasExplanation) {
                            if (isPremiumGated) {
                                explanationHtml = '<div class="mt-4 p-4 bg-[var(--warning-subtle)] border border-[var(--warning)] rounded-xl">' +
                                    '<p class="text-sm text-[var(--warning)] flex items-center gap-2 font-medium">' +
                                    '<svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75"/></svg>' +
                                    'Pembahasan hanya tersedia untuk member Premium' +
                                    '</p></div>';
                            } else {
                                explanationHtml = '<div class="mt-4 p-4 bg-[var(--info-subtle)] border border-[var(--info)] rounded-xl">' +
                                    '<p class="text-sm font-bold text-[var(--info)] mb-2 flex items-center gap-2"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>Pembahasan:</p>' +
                                    '<div class="text-sm text-[var(--text-primary)] leading-relaxed prose dark:prose-invert max-w-none">' + q.explanation + '</div></div>';
                            }
                        }

                        return '<div class="card">' +
                            '<div class="flex items-start gap-3 mb-5">' + statusIcon +
                            '<div class="flex-1"><h3 class="text-lg font-bold text-[var(--text-primary)] leading-tight">Soal ' + (i + 1) + '</h3></div>' +
                            '<span class="badge ' + (isCorrect ? 'badge-success' : 'badge-danger') + ' shrink-0">' + (isCorrect ? 'Benar' : 'Salah') + '</span>' +
                            '</div>' +
                            '<div class="mb-5 text-[var(--text-primary)] text-[1.067rem] leading-relaxed prose dark:prose-invert max-w-none">' + (q.content || q.question_text || '') + '</div>' +
                            '<div class="space-y-3">' + optionsHtml + '</div>' +
                            explanationHtml +
                            '</div>';
                    }).join('');

                    document.getElementById('questions-review').innerHTML = reviewHtml;
                } else {
                    document.getElementById('questions-review').innerHTML = '<x-empty-state icon=\'<svg class="w-16 h-16 text-[var(--text-muted)] mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>\' title="Detail review soal belum tersedia" />';
                }
            }

            loadResult();
        })();
    </script>
@endsection