<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Category;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\Option;
use App\Models\Material;
use App\Models\Attempt;
use App\Models\LearningProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // DASHBOARD STATS
    public function dashboard(): JsonResponse
    {
        $totalUsers = User::count();
        $premiumUsers = User::where('membership_tier', 'PREMIUM')->count();
        $freeUsers = User::where('membership_tier', 'FREE')->count();
        $totalTryouts = Tryout::count();
        $totalAttempts = Attempt::whereIn('status', ['SUBMITTED', 'EXPIRED'])->count();

        $latestUsers = User::orderBy('created_at', 'desc')->take(5)->get();
        $latestAttempts = Attempt::with(['user', 'tryout'])
            ->whereIn('status', ['SUBMITTED', 'EXPIRED'])
            ->orderBy('submitted_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'totalUsers' => $totalUsers,
            'premiumUsers' => $premiumUsers,
            'freeUsers' => $freeUsers,
            'totalTryouts' => $totalTryouts,
            'totalAttempts' => $totalAttempts,
            'latestUsers' => $latestUsers->map(fn($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'email' => $u->email,
                'role' => $u->role,
                'membershipTier' => $u->membership_tier,
                'createdAt' => $u->created_at->toIso8601String(),
            ]),
            'latestAttempts' => $latestAttempts->map(fn($a) => [
                'id' => $a->id,
                'userName' => $a->user->name,
                'tryoutTitle' => $a->tryout->title,
                'score' => $a->score,
                'status' => $a->status,
                'submittedAt' => $a->submitted_at ? $a->submitted_at->toIso8601String() : null,
            ]),
        ]);
    }

    // USER MANAGEMENT
    public function users(Request $request): JsonResponse
    {
        $query = User::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(12);

        return response()->json($users);
    }

    public function userDetail($id): JsonResponse
    {
        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function updateUser(Request $request, $id): JsonResponse
    {
        $admin = $request->user();
        $user = User::findOrFail($id);

        // ADMINS cannot edit SUPERADMIN users
        if ($user->role === 'SUPERADMIN' && $admin->role !== 'SUPERADMIN') {
            abort(403, 'Hanya Super Admin yang dapat mengedit akun Super Admin.');
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'role' => 'sometimes|in:SUPERADMIN,ADMIN,USER',
            'membership_tier' => 'sometimes|in:FREE,PREMIUM',
            'membership_status' => 'sometimes|in:ACTIVE,EXPIRED,SUSPENDED',
            'membership_expiry' => 'sometimes|nullable|date',
            'is_active' => 'sometimes|boolean',
        ]);

        $user->update($validated);
        return response()->json($user);
    }

    public function resetPassword(Request $request, $id): JsonResponse
    {
        $admin = $request->user();
        $user = User::findOrFail($id);

        if ($user->role === 'SUPERADMIN' && $admin->role !== 'SUPERADMIN') {
            abort(403, 'Hanya Super Admin yang dapat reset password akun Super Admin.');
        }

        $validated = $request->validate([
            'new_password' => 'required|string|min:5',
        ]);

        $user->update(['password' => Hash::make($validated['new_password'])]);
        return response()->json(['message' => 'Password berhasil direset.']);
    }

    public function createUser(Request $request): JsonResponse
    {
        // SUPERADMIN only
        if ($request->user()->role !== 'SUPERADMIN') {
            abort(403, 'Hanya Super Admin yang dapat membuat akun baru.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:5',
            'role' => 'sometimes|in:SUPERADMIN,ADMIN,USER',
            'membership_tier' => 'sometimes|in:FREE,PREMIUM',
            'membership_status' => 'sometimes|in:ACTIVE,EXPIRED,SUSPENDED',
            'membership_expiry' => 'sometimes|nullable|date',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = true;
        if (!isset($validated['role']))
            $validated['role'] = 'USER';
        if (!isset($validated['membership_tier']))
            $validated['membership_tier'] = 'FREE';
        if (!isset($validated['membership_status']))
            $validated['membership_status'] = 'ACTIVE';

        $user = User::create($validated);
        return response()->json($user, 201);
    }

    public function deleteUser(Request $request, $id): JsonResponse
    {
        // SUPERADMIN only
        if ($request->user()->role !== 'SUPERADMIN') {
            abort(403, 'Hanya Super Admin yang dapat menghapus akun.');
        }

        // Cannot delete self
        if ($request->user()->id == $id) {
            abort(403, 'Tidak dapat menghapus akun sendiri.');
        }

        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'Akun berhasil dihapus.']);
    }

    // CATEGORY MANAGEMENT
    public function categories(): JsonResponse
    {
        return response()->json(Category::orderBy('order')->get());
    }

    public function createCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'sometimes|integer',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        if (!isset($validated['order']))
            $validated['order'] = 0;

        $category = Category::create($validated);
        return response()->json($category, 201);
    }

    public function updateCategory(Request $request, $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'description' => 'nullable|string|max:500',
            'order' => 'sometimes|integer',
        ]);

        if (isset($validated['name'])) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $category->update($validated);
        return response()->json($category);
    }

    public function deleteCategory($id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Kategori berhasil dihapus.']);
    }

    // TRYOUT MANAGEMENT
    public function createTryout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'sometimes|string|unique:tryouts,slug',
            'description' => 'nullable|string|max:500',
            'category_id' => 'required|exists:categories,id',
            'status' => 'sometimes|in:DRAFT,PUBLISHED,ARCHIVED',
            'required_tier' => 'sometimes|in:FREE,PREMIUM',
            'duration_minutes' => 'required|integer|min:1',
            'total_questions' => 'required|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'randomize_order' => 'sometimes|boolean',
            'show_result' => 'sometimes|boolean',
        ]);

        if (!isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        if (!isset($validated['status']))
            $validated['status'] = 'DRAFT';
        if (!isset($validated['required_tier']))
            $validated['required_tier'] = 'FREE';
        if (!isset($validated['randomize_order']))
            $validated['randomize_order'] = false;
        if (!isset($validated['show_result']))
            $validated['show_result'] = true;

        $tryout = Tryout::create($validated);
        return response()->json($tryout, 201);
    }

    public function updateTryout(Request $request, $id): JsonResponse
    {
        $tryout = Tryout::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:200',
            'slug' => 'sometimes|string|unique:tryouts,slug,' . $id,
            'description' => 'nullable|string|max:500',
            'category_id' => 'sometimes|exists:categories,id',
            'status' => 'sometimes|in:DRAFT,PUBLISHED,ARCHIVED',
            'required_tier' => 'sometimes|in:FREE,PREMIUM',
            'duration_minutes' => 'sometimes|integer|min:1',
            'total_questions' => 'sometimes|integer|min:1',
            'passing_score' => 'sometimes|integer|min:0|max:100',
            'randomize_order' => 'sometimes|boolean',
            'show_result' => 'sometimes|boolean',
        ]);

        $tryout->update($validated);
        return response()->json($tryout);
    }

    public function deleteTryout($id): JsonResponse
    {
        $tryout = Tryout::findOrFail($id);
        $tryout->delete(); // cascades to questions, options, attempts
        return response()->json(['message' => 'Tryout berhasil dihapus.']);
    }

    // QUESTION MANAGEMENT
    public function getQuestions($tryoutId): JsonResponse
    {
        $tryout = Tryout::findOrFail($tryoutId);
        $questions = Question::where('tryout_id', $tryoutId)
            ->with('options')
            ->orderBy('order')
            ->get();

        return response()->json($questions);
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240', // max 10MB
        ]);

        $path = $request->file('image')->store('uploads', 'public');
        $url = Storage::url($path);

        return response()->json(['url' => $url]);
    }

    public function createQuestion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'tryout_id' => 'required|exists:tryouts,id',
            'type' => 'required|in:SINGLE_CHOICE,MULTIPLE_CHOICE,TRUE_FALSE',
            'content' => 'required|string',
            'image_url' => 'nullable|string',
            'order' => 'sometimes|integer',
            'explanation' => 'nullable|string',
            'points' => 'sometimes|integer|min:1',
            'options' => 'required|array|min:2',
            'options.*.label' => 'required|string',
            'options.*.content' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
            'options.*.order' => 'sometimes|integer',
        ]);

        if (!isset($validated['order']))
            $validated['order'] = 0;
        if (!isset($validated['points']))
            $validated['points'] = 1;

        $question = Question::create([
            'tryout_id' => $validated['tryout_id'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'image_url' => $validated['image_url'],
            'order' => $validated['order'],
            'explanation' => $validated['explanation'],
            'points' => $validated['points'],
        ]);

        foreach ($validated['options'] as $opt) {
            Option::create([
                'question_id' => $question->id,
                'label' => $opt['label'],
                'content' => $opt['content'],
                'is_correct' => $opt['is_correct'],
                'order' => $opt['order'] ?? 0,
            ]);
        }

        // Update total_questions count on tryout
        $tryout = Tryout::find($validated['tryout_id']);
        $tryout->update(['total_questions' => Question::where('tryout_id', $tryout->id)->count()]);

        $question->load('options');
        return response()->json($question, 201);
    }

    public function updateQuestion(Request $request, $id): JsonResponse
    {
        $question = Question::findOrFail($id);

        $validated = $request->validate([
            'type' => 'sometimes|in:SINGLE_CHOICE,MULTIPLE_CHOICE,TRUE_FALSE',
            'content' => 'sometimes|string',
            'image_url' => 'nullable|string',
            'order' => 'sometimes|integer',
            'explanation' => 'nullable|string',
            'points' => 'sometimes|integer|min:1',
            'options' => 'sometimes|array',
            'options.*.id' => 'sometimes|exists:options,id',
            'options.*.label' => 'required|string',
            'options.*.content' => 'required|string',
            'options.*.is_correct' => 'required|boolean',
            'options.*.order' => 'sometimes|integer',
        ]);

        $question->update(collect($validated)->except('options')->toArray());

        if (isset($validated['options'])) {
            // Delete existing options not in the update list
            $updatedOptionIds = collect($validated['options'])
                ->whereNotNull('id')
                ->pluck('id')
                ->toArray();

            Option::where('question_id', $question->id)
                ->whereNotIn('id', $updatedOptionIds)
                ->delete();

            foreach ($validated['options'] as $opt) {
                if (isset($opt['id'])) {
                    Option::where('id', $opt['id'])->update([
                        'label' => $opt['label'],
                        'content' => $opt['content'],
                        'is_correct' => $opt['is_correct'],
                        'order' => $opt['order'] ?? 0,
                    ]);
                } else {
                    Option::create([
                        'question_id' => $question->id,
                        'label' => $opt['label'],
                        'content' => $opt['content'],
                        'is_correct' => $opt['is_correct'],
                        'order' => $opt['order'] ?? 0,
                    ]);
                }
            }
        }

        $question->load('options');
        return response()->json($question);
    }

    public function deleteQuestion($id): JsonResponse
    {
        $question = Question::findOrFail($id);
        $tryoutId = $question->tryout_id;
        $question->delete();

        // Update total_questions
        Tryout::where('id', $tryoutId)
            ->update(['total_questions' => Question::where('tryout_id', $tryoutId)->count()]);

        return response()->json(['message' => 'Soal berhasil dihapus.']);
    }

    // MATERIAL MANAGEMENT
    public function createMaterial(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:200',
            'slug' => 'sometimes|string|unique:materials,slug',
            'content' => 'required|string',
            'required_tier' => 'sometimes|in:FREE,PREMIUM',
            'order' => 'sometimes|integer',
            'is_published' => 'sometimes|boolean',
        ]);

        if (!isset($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
        }
        if (!isset($validated['required_tier']))
            $validated['required_tier'] = 'FREE';
        if (!isset($validated['order']))
            $validated['order'] = 0;
        if (!isset($validated['is_published']))
            $validated['is_published'] = false;

        $material = Material::create($validated);
        return response()->json($material, 201);
    }

    public function updateMaterial(Request $request, $id): JsonResponse
    {
        $material = Material::findOrFail($id);

        $validated = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'title' => 'sometimes|string|max:200',
            'slug' => 'sometimes|string|unique:materials,slug,' . $id,
            'content' => 'sometimes|string',
            'required_tier' => 'sometimes|in:FREE,PREMIUM',
            'order' => 'sometimes|integer',
            'is_published' => 'sometimes|boolean',
        ]);

        $material->update($validated);
        return response()->json($material);
    }

    public function deleteMaterial($id): JsonResponse
    {
        $material = Material::findOrFail($id);
        LearningProgress::where('material_id', $material->id)->delete();
        $material->delete();
        return response()->json(['message' => 'Materi berhasil dihapus.']);
    }
}