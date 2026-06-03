@extends('app')

@section('content')
    <div class="p-6 max-w-4xl mx-auto">
        <!-- Score display -->
        <div class="card mb-6 text-center">
            <div id="result-score-circle"
                class="w-24 h-24 rounded-full mx-auto flex items-center justify-center text-3xl font-bold mb-4">
                <span id="result-score">0%</span>
            </div>
            <h2 id="result-status" class="text-2xl font-bold mb-2">-</h2>
            <p class="text-gray-500 dark:text-gray-400">
                <span id="result-correct">0</span> / <span id="result-total">0</span> soal benar
            </p>
            <p id="result-tryout-title" class="text-sm text-gray-400 dark:text-gray-500 mt-2"></p>
        </div>

        <!-- Action buttons -->
        <div class="flex gap-3 mb-6">
            <a href="{{ route('tryouts') }}" class="btn-secondary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                Kembali ke Tryout
            </a>
            <a href="{{ route('my-attempts') }}" class="btn-primary flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                Lihat Semua Nilai
            </a>
        </div>

        <!-- Per-question review -->
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Review Soal</h2>
        </div>
        <div id="questions-review" class="space-y-4">
            <!-- Loaded via JS -->
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
                    scoreCircle.className = 'w-24 h-24 rounded-full mx-auto flex items-center justify-center text-3xl font-bold bg-green-100 dark:bg-green-900 text-green-600 dark:text-green-300';
                    document.getElementById('result-status').textContent = 'LULUS';
                    document.getElementById('result-status').className = 'text-2xl font-bold mb-2 text-green-600 dark:text-green-400';
                } else {
                    scoreCircle.className = 'w-24 h-24 rounded-full mx-auto flex items-center justify-center text-3xl font-bold bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-300';
                    document.getElementById('result-status').textContent = 'TIDAK LULUS';
                    document.getElementById('result-status').className = 'text-2xl font-bold mb-2 text-red-600 dark:text-red-400';
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
                            ? '<svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                            : '<svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';

                        var optionsHtml = options.map(function (opt, oi) {
                            var optId = opt.id || oi;
                            var isUserAnswer = Array.isArray(userAnswer) ? userAnswer.indexOf(optId) !== -1 : (userAnswer === optId);
                            var isCorrectOption = opt.isCorrect === true;
                            var optBgClass = 'border-gray-200 dark:border-gray-600';
                            if (isCorrectOption) optBgClass = 'border-green-500 bg-green-50 dark:bg-green-900/20 dark:border-green-600';
                            if (isUserAnswer && !isCorrectOption) optBgClass = 'border-red-500 bg-red-50 dark:bg-red-900/20 dark:border-red-600';
                            if (isUserAnswer && isCorrectOption) optBgClass = 'border-green-500 bg-green-50 dark:bg-green-900/20 dark:border-green-600';

                            var marker = '';
                            if (isCorrectOption) marker = '<span class="text-green-600 dark:text-green-400 font-medium text-xs">[Benar]</span>';
                            if (isUserAnswer && !isCorrectOption) marker = '<span class="text-red-600 dark:text-red-400 font-medium text-xs">[Jawaban Anda]</span>';
                            if (isUserAnswer && isCorrectOption) marker = '<span class="text-green-600 dark:text-green-400 font-medium text-xs">[Jawaban Anda - Benar]</span>';

                            return '<div class="p-2 border rounded-lg ' + optBgClass + ' flex items-center gap-2 text-sm">' +
                                '<span class="font-medium">' + (opt.label || String.fromCharCode(65 + oi)) + '</span>' +
                                '<span class="text-gray-700 dark:text-gray-300">' + (opt.content || opt.text || '') + '</span>' +
                                marker +
                                '</div>';
                        }).join('');

                        var explanationHtml = '';
                        if (hasExplanation) {
                            if (isPremiumGated) {
                                explanationHtml = '<div class="mt-3 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg">' +
                                    '<p class="text-sm text-amber-700 dark:text-amber-300 flex items-center gap-2">' +
                                    '<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75"/></svg>' +
                                    'Pembahasan hanya tersedia untuk member Premium' +
                                    '</p></div>';
                            } else {
                                explanationHtml = '<div class="mt-3 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">' +
                                    '<p class="text-sm font-medium text-blue-700 dark:text-blue-300 mb-1">Pembahasan:</p>' +
                                    '<p class="text-sm text-gray-700 dark:text-gray-300">' + q.explanation + '</p></div>';
                            }
                        }

                        return '<div class="card">' +
                            '<div class="flex items-center gap-3 mb-4">' + statusIcon +
                            '<h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Soal ' + (i + 1) + '</h3>' +
                            '<span class="badge ' + (isCorrect ? 'badge-success' : 'badge-danger') + '">' + (isCorrect ? 'Benar' : 'Salah') + '</span>' +
                            '</div>' +
                            '<div class="mb-4 text-gray-900 dark:text-gray-100">' + (q.content || q.question_text || '') + '</div>' +
                            '<div class="space-y-2">' + optionsHtml + '</div>' +
                            explanationHtml +
                            '</div>';
                    }).join('');

                    document.getElementById('questions-review').innerHTML = reviewHtml;
                } else {
                    document.getElementById('questions-review').innerHTML = '<div class="text-center py-8 text-gray-500 dark:text-gray-400">Detail review soal belum tersedia</div>';
                }
            }

            loadResult();
        })();
    </script>
@endsection