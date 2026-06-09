<?php

namespace App\Http\Controllers;

use App\Models\Answer;
use App\Models\Attempt;
use App\Models\Category;
use App\Models\LearningProgress;
use App\Models\Material;
use App\Models\Option;
use App\Models\Question;
use App\Models\Tryout;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserDashboardController extends Controller
{
    /**
     * Return view with user info for dashboard.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeAttempt = Attempt::where('user_id', $user->id)
            ->where('status', 'IN_PROGRESS')
            ->where('expires_at', '>', now())
            ->first();

        $completedAttempts = Attempt::where('user_id', $user->id)
            ->whereIn('status', ['SUBMITTED', 'EXPIRED'])
            ->count();

        $completedMaterials = LearningProgress::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->count();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'membership_tier' => $user->membership_tier,
                'membership_status' => $user->membership_status,
                'membership_expiry' => $user->membership_expiry ? $user->membership_expiry->toIso8601String() : null,
            ],
            'stats' => [
                'completedAttempts' => $completedAttempts,
                'completedMaterials' => $completedMaterials,
                'hasActiveAttempt' => $activeAttempt !== null,
            ],
        ]);
    }

    /**
     * Check for any IN_PROGRESS non-expired attempt for the current user.
     */
    public function checkActiveAttempt(Request $request): JsonResponse
    {
        $user = $request->user();

        $activeAttempt = Attempt::where('user_id', $user->id)
            ->where('status', 'IN_PROGRESS')
            ->where('expires_at', '>', now())
            ->first();

        if ($activeAttempt) {
            $tryout = $activeAttempt->tryout;
            return response()->json([
                'active' => true,
                'attemptId' => $activeAttempt->id,
                'tryoutSlug' => $tryout ? $tryout->slug : null,
                'tryoutTitle' => $tryout ? $tryout->title : 'Tryout',
                'expiresAt' => $activeAttempt->expires_at->toIso8601String(),
            ]);
        }

        return response()->json(['active' => false]);
    }

    /**
     * Return all categories sorted by order.
     */
    public function categories(): JsonResponse
    {
        $categories = Category::orderBy('order')->get();

        return response()->json($categories);
    }

    /**
     * Return tryouts filtered by category_id and membership_tier.
     * Hide DRAFT/ARCHIVED for non-admins.
     */
    public function tryouts(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Tryout::with('category');

        // Non-admins only see published tryouts
        if ($user->role !== 'ADMIN' && $user->role !== 'SUPERADMIN') {
            $query->where('status', 'PUBLISHED');
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('membership_tier')) {
            $query->where('required_tier', $request->membership_tier);
        }

        $tryouts = $query->get();

        $enriched = $tryouts->map(function ($tryout) use ($user) {
            $latestAttempt = Attempt::where('user_id', $user->id)
                ->where('tryout_id', $tryout->id)
                ->orderByDesc('started_at')
                ->first();

            $attemptCount = Attempt::where('user_id', $user->id)
                ->where('tryout_id', $tryout->id)
                ->count();

            return [
                'id' => $tryout->id,
                'title' => $tryout->title,
                'slug' => $tryout->slug,
                'description' => $tryout->description,
                'category_id' => $tryout->category_id,
                'categoryName' => $tryout->category ? $tryout->category->name : 'Default',
                'status' => $tryout->status,
                'required_tier' => $tryout->required_tier,
                'duration_minutes' => $tryout->duration_minutes,
                'total_questions' => $tryout->total_questions,
                'passing_score' => $tryout->passing_score,
                'randomize_order' => $tryout->randomize_order,
                'show_result' => $tryout->show_result,
                'attemptStatus' => $latestAttempt ? $latestAttempt->status : null,
                'attemptScore' => $latestAttempt ? $latestAttempt->score : null,
                'attemptId' => $latestAttempt ? $latestAttempt->id : null,
                'attemptCount' => $attemptCount,
            ];
        });

        return response()->json($enriched);
    }

    /**
     * Return tryout detail with category and user's previous attempts.
     */
    public function tryoutDetail($slug): JsonResponse
    {
        $user = request()->user();

        $tryout = Tryout::where('slug', $slug)->first();
        if (!$tryout) {
            return response()->json(['error' => 'Tryout tidak ditemukan.'], 404);
        }

        $category = $tryout->category;
        $previousAttempts = Attempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->orderByDesc('started_at')
            ->get();

        return response()->json([
            'tryout' => $tryout,
            'categoryName' => $category ? $category->name : 'Kategori Dasar',
            'attempts' => $previousAttempts->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'status' => $attempt->status,
                    'score' => $attempt->score,
                    'started_at' => $attempt->started_at->toIso8601String(),
                    'submitted_at' => $attempt->submitted_at ? $attempt->submitted_at->toIso8601String() : null,
                    'expires_at' => $attempt->expires_at->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Start a new attempt for a tryout.
     * Check premium tier, existing active attempts, auto-submit expired attempts.
     */
    public function startAttempt(Request $request, $slug): JsonResponse
    {
        $user = $request->user();

        $tryout = Tryout::where('slug', $slug)->first();
        if (!$tryout || $tryout->status !== 'PUBLISHED') {
            return response()->json(['error' => 'Tryout tidak ditemukan atau belum dirilis.'], 404);
        }

        // Premium access wall
        if ($tryout->required_tier === 'PREMIUM' && $user->role !== 'ADMIN' && $user->role !== 'SUPERADMIN') {
            if (!$user->isPremiumActive()) {
                return response()->json(['error' => 'Akses Terkunci. Konten tryout ini hanya untuk member PREMIUM aktif.'], 403);
            }
        }

        // Check for other active attempt in a different tryout
        $otherActive = Attempt::where('user_id', $user->id)
            ->where('status', 'IN_PROGRESS')
            ->where('tryout_id', '!=', $tryout->id)
            ->where('expires_at', '>', now())
            ->first();

        if ($otherActive) {
            $activeTryout = $otherActive->tryout;
            return response()->json([
                'error' => 'Anda sedang memiliki ujian berlangsung ("' . ($activeTryout ? $activeTryout->title : 'Lainnya') . '"). Selesaikan ujian tersebut terlebih dahulu sebelum memulai ujian baru.',
            ], 400);
        }

        // Auto-submit any expired IN_PROGRESS attempts for this tryout
        $expiredAttempts = Attempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->where('status', 'IN_PROGRESS')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredAttempts as $expired) {
            $this->autoSubmitExpiredAttempt($expired, $tryout);
        }

        // Check for an active IN_PROGRESS attempt to resume
        $activeAttempt = Attempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->where('status', 'IN_PROGRESS')
            ->where('expires_at', '>', now())
            ->first();

        if ($activeAttempt) {
            // Resume running session
            $savedAnswers = [];
            $doubtAnswers = [];
            $attemptAnswers = Answer::where('attempt_id', $activeAttempt->id)->get();
            foreach ($attemptAnswers as $ans) {
                $savedAnswers[$ans->question_id] = $ans->selected_opts;
                $doubtAnswers[$ans->question_id] = $ans->is_doubt;
            }

            return response()->json([
                'attemptId' => $activeAttempt->id,
                'expiresAt' => $activeAttempt->expires_at->toIso8601String(),
                'isResume' => true,
                'snapshot' => [
                    'attemptId' => $activeAttempt->id,
                    'questionOrder' => $activeAttempt->snapshot ? $activeAttempt->snapshot['questionOrder'] : [],
                    'currentIndex' => $activeAttempt->snapshot ? $activeAttempt->snapshot['currentIndex'] : 0,
                    'savedAnswers' => $savedAnswers,
                    'doubtAnswers' => $doubtAnswers,
                    'expiresAt' => $activeAttempt->expires_at->toIso8601String(),
                ],
            ]);
        }

        // No active attempt — user can start a new one (even if they have past SUBMITTED attempts)

        // Verify tryout has questions
        $questionsList = Question::where('tryout_id', $tryout->id)->get();
        if ($questionsList->isEmpty()) {
            return response()->json(['error' => 'Tryout belum memiliki daftar soal ujian.'], 400);
        }

        // Build question order
        $orderedQuestionIds = $questionsList->pluck('id')->toArray();
        if ($tryout->randomize_order) {
            shuffle($orderedQuestionIds);
        }

        $expiresAt = now()->addMinutes($tryout->duration_minutes);

        $newAttempt = Attempt::create([
            'user_id' => $user->id,
            'tryout_id' => $tryout->id,
            'status' => 'IN_PROGRESS',
            'started_at' => now(),
            'expires_at' => $expiresAt,
            'total_answered' => 0,
            'snapshot' => [
                'questionOrder' => $orderedQuestionIds,
                'currentIndex' => 0,
            ],
        ]);

        return response()->json([
            'attemptId' => $newAttempt->id,
            'expiresAt' => $expiresAt->toIso8601String(),
            'isResume' => false,
            'snapshot' => [
                'attemptId' => $newAttempt->id,
                'questionOrder' => $orderedQuestionIds,
                'currentIndex' => 0,
                'savedAnswers' => [],
                'doubtAnswers' => [],
                'expiresAt' => $expiresAt->toIso8601String(),
            ],
        ], 201);
    }

    /**
     * Get questions for active attempt, stripping is_correct from options.
     */
    public function getQuestions($slug): JsonResponse
    {
        $user = request()->user();

        $tryout = Tryout::where('slug', $slug)->first();
        if (!$tryout) {
            return response()->json(['error' => 'Tryout tidak ditemukan.'], 404);
        }

        $attempt = Attempt::where('user_id', $user->id)
            ->where('tryout_id', $tryout->id)
            ->where('status', 'IN_PROGRESS')
            ->first();

        if (!$attempt) {
            return response()->json(['error' => 'Anda tidak memiliki sesi tryout yang sedang berlangsung.'], 403);
        }

        $questions = Question::where('tryout_id', $tryout->id)->get();
        $options = Option::whereIn('question_id', $questions->pluck('id'))
            ->orderBy('order')
            ->get();

        $safeQuestions = $questions->map(function ($q) use ($options) {
            $qOpts = $options->where('question_id', $q->id)->map(function ($o) {
                return [
                    'id' => $o->id,
                    'label' => $o->label,
                    'content' => $o->content,
                    'order' => $o->order,
                ];
            })->values()->toArray();

            return [
                'id' => $q->id,
                'type' => $q->type,
                'content' => $q->content,
                'image_url' => $q->image_url,
                'points' => $q->points,
                'options' => $qOpts,
            ];
        })->values()->toArray();

        // Gather saved answers
        $savedAnswers = [];
        $doubtAnswers = [];
        $attemptAnswers = Answer::where('attempt_id', $attempt->id)->get();
        foreach ($attemptAnswers as $ans) {
            $savedAnswers[$ans->question_id] = $ans->selected_opts;
            $doubtAnswers[$ans->question_id] = $ans->is_doubt;
        }

        return response()->json([
            'questions' => $safeQuestions,
            'snapshot' => [
                'questionOrder' => $attempt->snapshot ? $attempt->snapshot['questionOrder'] : [],
                'currentIndex' => $attempt->snapshot ? $attempt->snapshot['currentIndex'] : 0,
                'savedAnswers' => $savedAnswers,
                'doubtAnswers' => $doubtAnswers,
            ],
            'expiresAt' => $attempt->expires_at->toIso8601String(),
        ]);
    }

    /**
     * Save/update answer for active attempt.
     */
    public function saveAnswer(Request $request, $slug): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
            'question_id' => 'required|exists:questions,id',
            'selectedOptionIds' => 'sometimes|array',
            'isDoubt' => 'sometimes|boolean',
        ]);

        $attempt = Attempt::where('id', $validated['attempt_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$attempt || $attempt->status !== 'IN_PROGRESS') {
            return response()->json(['error' => 'Sesi ujian sudah ditutup atau tidak ditemukan.'], 403);
        }

        if ($attempt->expires_at < now()) {
            return response()->json(['error' => 'Waktu ujian Anda telah habis.'], 403);
        }

        $answer = Answer::where('attempt_id', $attempt->id)
            ->where('question_id', $validated['question_id'])
            ->first();

        $selectedOptionIds = $request->has('selectedOptionIds') ? $validated['selectedOptionIds'] : ($answer ? $answer->selected_opts : []);
        $isDoubt = $request->has('isDoubt') ? $validated['isDoubt'] : ($answer ? $answer->is_doubt : false);

        if ($answer) {
            $answer->update([
                'selected_opts' => $selectedOptionIds,
                'is_doubt' => $isDoubt,
                'answered_at' => now(),
            ]);
        } else {
            Answer::create([
                'attempt_id' => $attempt->id,
                'question_id' => $validated['question_id'],
                'selected_opts' => $selectedOptionIds,
                'is_doubt' => $isDoubt,
                'is_correct' => null,
                'answered_at' => now(),
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Save current question index position in snapshot.
     */
    public function savePosition(Request $request, $slug): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
            'currentIndex' => 'required|integer',
        ]);

        $attempt = Attempt::where('id', $validated['attempt_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$attempt || $attempt->status !== 'IN_PROGRESS') {
            return response()->json(['error' => 'Sesi tidak valid.'], 403);
        }

        $snapshot = $attempt->snapshot ?? [];
        $snapshot['currentIndex'] = $validated['currentIndex'];
        $attempt->update(['snapshot' => $snapshot]);

        return response()->json(['success' => true]);
    }

    /**
     * Submit attempt: auto-grade, calculate score, check passing.
     */
    public function submitAttempt(Request $request, $slug): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'attempt_id' => 'required|exists:attempts,id',
        ]);

        $attempt = Attempt::where('id', $validated['attempt_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$attempt || $attempt->status !== 'IN_PROGRESS') {
            return response()->json(['error' => 'Sesi tryout tidak ditemukan atau sudah dikumpulkan.'], 400);
        }

        $tryout = $attempt->tryout;
        if (!$tryout) {
            return response()->json(['error' => 'Data Tryout terkait hilang.'], 404);
        }

        // Grade all answers
        $attemptAnswers = Answer::where('attempt_id', $attempt->id)->get();
        $questions = Question::where('tryout_id', $tryout->id)->get();

        $aggregateScore = 0;
        $correctAnswers = 0;

        foreach ($questions as $q) {
            $ans = $attemptAnswers->where('question_id', $q->id)->first();
            $correctOptionIds = Option::where('question_id', $q->id)
                ->where('is_correct', true)
                ->pluck('id')
                ->toArray();

            $userSelections = $ans ? $ans->selected_opts : [];

            $isCorrect = count($userSelections) > 0
                && count($userSelections) === count($correctOptionIds)
                && empty(array_diff($userSelections, $correctOptionIds));

            if ($isCorrect) {
                $aggregateScore += $q->points;
                $correctAnswers++;
                if ($ans) {
                    $ans->update(['is_correct' => true]);
                }
            } else {
                if ($ans) {
                    $ans->update(['is_correct' => false]);
                }
            }
        }

        $totalMaxPoints = $questions->sum('points') ?: 100;
        $score = round(($aggregateScore / $totalMaxPoints) * 100);

        $attempt->update([
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
            'score' => $score,
            'total_correct' => $correctAnswers,
            'total_answered' => $attemptAnswers->filter(function ($a) {
                return !empty($a->selected_opts);
            })->count(),
        ]);

        return response()->json([
            'success' => true,
            'score' => $score,
            'totalCorrect' => $correctAnswers,
            'totalQuestions' => $questions->count(),
        ]);
    }

    /**
     * Get attempt result with questions, answers, and correct answers.
     * Explanations only for premium users or admins.
     */
    public function attemptResult(Request $request, $slug, $attemptId): JsonResponse
    {
        $user = $request->user();

        $attempt = Attempt::find($attemptId);
        if (!$attempt) {
            return response()->json(['error' => 'Hasil Ujian tidak ditemukan.'], 404);
        }

        // User owns this attempt or is admin
        if ($attempt->user_id !== $user->id && $user->role !== 'ADMIN' && $user->role !== 'SUPERADMIN') {
            return response()->json(['error' => 'Akses Dilarang.'], 403);
        }

        $tryout = $attempt->tryout;
        if (!$tryout) {
            return response()->json(['error' => 'Ujian terkait tidak ditemukan.'], 404);
        }

        $questions = Question::where('tryout_id', $tryout->id)->get();
        $options = Option::whereIn('question_id', $questions->pluck('id'))->orderBy('order')->get();
        $answers = Answer::where('attempt_id', $attempt->id)->get();

        // Premium access for explanations
        $hasPremium = $user->isPremiumActive() || $user->role === 'ADMIN' || $user->role === 'SUPERADMIN';

        $evaluationQuestions = $questions->map(function ($q) use ($answers, $options, $hasPremium) {
            $qAns = $answers->where('question_id', $q->id)->first();
            $qOptions = $options->where('question_id', $q->id)->values()->map(function ($o) use ($hasPremium) {
                return [
                    'id' => $o->id,
                    'label' => $o->label,
                    'content' => $o->content,
                    'isCorrect' => $hasPremium ? $o->is_correct : null,
                ];
            })->toArray();

            return [
                'id' => $q->id,
                'content' => $q->content,
                'image_url' => $q->image_url,
                'type' => $q->type,
                'points' => $q->points,
                'explanation' => $hasPremium ? $q->explanation : null,
                'options' => $qOptions,
                'userSelected' => $qAns ? $qAns->selected_opts : [],
                'isCorrect' => $qAns ? $qAns->is_correct : null,
            ];
        })->values()->toArray();

        return response()->json([
            'attempt' => [
                'id' => $attempt->id,
                'status' => $attempt->status,
                'score' => $attempt->score,
                'total_correct' => $attempt->total_correct,
                'total_answered' => $attempt->total_answered,
                'started_at' => $attempt->started_at->toIso8601String(),
                'submitted_at' => $attempt->submitted_at ? $attempt->submitted_at->toIso8601String() : null,
                'expires_at' => $attempt->expires_at->toIso8601String(),
            ],
            'tryoutTitle' => $tryout->title,
            'requiredTier' => $tryout->required_tier,
            'passingScore' => $tryout->passing_score,
            'questionsCount' => $questions->count(),
            'questions' => $evaluationQuestions,
            'hasPremiumAccess' => $hasPremium,
        ]);
    }

    /**
     * List user's attempts with tryout info.
     */
    public function myAttempts(Request $request): JsonResponse
    {
        $user = $request->user();

        $attempts = Attempt::where('user_id', $user->id)
            ->with('tryout.category')
            ->orderByDesc('started_at')
            ->get();

        $list = $attempts->map(function ($a) {
            $tryout = $a->tryout;
            $category = $tryout ? $tryout->category : null;

            return [
                'id' => $a->id,
                'tryout_id' => $a->tryout_id,
                'status' => $a->status,
                'score' => $a->score,
                'total_correct' => $a->total_correct,
                'total_answered' => $a->total_answered,
                'started_at' => $a->started_at->toIso8601String(),
                'submitted_at' => $a->submitted_at ? $a->submitted_at->toIso8601String() : null,
                'expires_at' => $a->expires_at->toIso8601String(),
                'tryoutTitle' => $tryout ? $tryout->title : 'Ujian',
                'tryoutSlug' => $tryout ? $tryout->slug : '',
                'categoryName' => $category ? $category->name : '',
            ];
        });

        return response()->json($list);
    }

    /**
     * List published materials.
     */
    public function materials(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Material::with('category');

        // Normal users only see published materials
        if ($user->role !== 'ADMIN' && $user->role !== 'SUPERADMIN') {
            $query->where('is_published', true);
        }

        $materials = $query->orderBy('order')->get();

        $enriched = $materials->map(function ($m) use ($user) {
            $progress = LearningProgress::where('user_id', $user->id)
                ->where('material_id', $m->id)
                ->first();

            return [
                'id' => $m->id,
                'title' => $m->title,
                'slug' => $m->slug,
                'category_id' => $m->category_id,
                'categoryName' => $m->category ? $m->category->name : 'Umum',
                'required_tier' => $m->required_tier,
                'order' => $m->order,
                'is_published' => $m->is_published,
                'completedAt' => $progress ? ($progress->completed_at ? $progress->completed_at->toIso8601String() : null) : null,
            ];
        });

        return response()->json($enriched);
    }

    /**
     * Get material detail and record learning progress (last_opened_at).
     */
    public function materialDetail(Request $request, $slug): JsonResponse
    {
        $user = $request->user();

        $material = Material::where('slug', $slug)->first();
        if (!$material) {
            return response()->json(['error' => 'Materi tidak ditemukan.'], 404);
        }

        // Non-admins cannot see unpublished materials
        if (!$material->is_published && $user->role !== 'ADMIN' && $user->role !== 'SUPERADMIN') {
            return response()->json(['error' => 'Materi belum diterbitkan.'], 403);
        }

        // Premium access wall
        if ($material->required_tier === 'PREMIUM' && $user->role !== 'ADMIN' && $user->role !== 'SUPERADMIN') {
            if (!$user->isPremiumActive()) {
                return response()->json(['error' => 'Sesi Terkunci. Pembahasan materi ini khusus untuk keanggotaan PREMIUM.'], 403);
            }
        }

        // Record learning progress
        $progress = LearningProgress::where('user_id', $user->id)
            ->where('material_id', $material->id)
            ->first();

        if (!$progress) {
            LearningProgress::create([
                'user_id' => $user->id,
                'material_id' => $material->id,
                'last_opened_at' => now(),
            ]);
        } else {
            $progress->update(['last_opened_at' => now()]);
        }

        // Refresh progress after update
        $progress = LearningProgress::where('user_id', $user->id)
            ->where('material_id', $material->id)
            ->first();

        $category = $material->category;

        return response()->json([
            'material' => $material,
            'categoryName' => $category ? $category->name : 'Umum',
            'completedAt' => $progress ? ($progress->completed_at ? $progress->completed_at->toIso8601String() : null) : null,
        ]);
    }

    /**
     * Mark material as completed.
     */
    public function completeMaterial(Request $request, $slug): JsonResponse
    {
        $user = $request->user();

        $material = Material::where('slug', $slug)->first();
        if (!$material) {
            return response()->json(['error' => 'Materi gagal dibaca.'], 404);
        }

        $progress = LearningProgress::where('user_id', $user->id)
            ->where('material_id', $material->id)
            ->first();

        if (!$progress) {
            LearningProgress::create([
                'user_id' => $user->id,
                'material_id' => $material->id,
                'completed_at' => now(),
                'last_opened_at' => now(),
            ]);
        } else {
            $progress->update(['completed_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Helper: Auto-submit an expired attempt with grading.
     */
    private function autoSubmitExpiredAttempt(Attempt $attempt, Tryout $tryout): void
    {
        $attemptAnswers = Answer::where('attempt_id', $attempt->id)->get();
        $questions = Question::where('tryout_id', $tryout->id)->get();

        $aggregateScore = 0;
        $correctAnswers = 0;

        foreach ($questions as $q) {
            $ans = $attemptAnswers->where('question_id', $q->id)->first();
            $correctOptionIds = Option::where('question_id', $q->id)
                ->where('is_correct', true)
                ->pluck('id')
                ->toArray();

            $userSelections = $ans ? $ans->selected_opts : [];

            $isCorrect = count($userSelections) > 0
                && count($userSelections) === count($correctOptionIds)
                && empty(array_diff($userSelections, $correctOptionIds));

            if ($isCorrect) {
                $aggregateScore += $q->points;
                $correctAnswers++;
                if ($ans) {
                    $ans->update(['is_correct' => true]);
                }
            } else {
                if ($ans) {
                    $ans->update(['is_correct' => false]);
                }
            }
        }

        $totalMaxPoints = $questions->sum('points') ?: 100;
        $score = round(($aggregateScore / $totalMaxPoints) * 100);

        $attempt->update([
            'status' => 'SUBMITTED',
            'submitted_at' => now(),
            'score' => $score,
            'total_correct' => $correctAnswers,
            'total_answered' => $attemptAnswers->filter(function ($a) {
                return !empty($a->selected_opts);
            })->count(),
        ]);
    }
}