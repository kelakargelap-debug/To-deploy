<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\AdminController;

// Public auth routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/verify-totp', [AuthController::class, 'verifyTotp']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::post('/user/change-password', [AuthController::class, 'changePassword']);

    // TOTP setup (for authenticated users who haven't set up TOTP yet)
    Route::post('/auth/setup-totp', [AuthController::class, 'setupTotp']);
    Route::post('/auth/verify-totp-setup', [AuthController::class, 'verifyTotpSetup']);

    // User dashboard & tryout
    Route::get('/dashboard', [UserDashboardController::class, 'dashboard']);
    Route::get('/active-attempt', [UserDashboardController::class, 'checkActiveAttempt']);
    Route::get('/categories', [UserDashboardController::class, 'categories']);
    Route::get('/tryouts', [UserDashboardController::class, 'tryouts']);
    Route::get('/tryouts/{slug}', [UserDashboardController::class, 'tryoutDetail']);
    Route::post('/tryouts/{slug}/start', [UserDashboardController::class, 'startAttempt'])->name('api.tryout.start');
    Route::post('/tryouts/{slug}/heartbeat', [UserDashboardController::class, 'heartbeat']);
    Route::get('/tryouts/{slug}/questions', [UserDashboardController::class, 'getQuestions']);
    Route::post('/tryouts/{slug}/save-answer', [UserDashboardController::class, 'saveAnswer'])->name('api.tryout.save-answer');
    Route::patch('/tryouts/{slug}/position', [UserDashboardController::class, 'savePosition']);
    Route::post('/tryouts/{slug}/submit', [UserDashboardController::class, 'submitAttempt'])->name('api.tryout.submit');
    Route::get('/tryouts/{slug}/result/{attemptId}', [UserDashboardController::class, 'attemptResult'])->name('api.tryout.result');
    Route::get('/my-attempts', [UserDashboardController::class, 'myAttempts']);
    Route::get('/dashboard/stats', function (Request $request) {
        $ctrl = app(UserDashboardController::class);
        $data = $ctrl->dashboard($request)->getData(true);
        return response()->json($data['stats'] ?? $data);
    });

    // Materials
    Route::get('/materials', [UserDashboardController::class, 'materials']);
    Route::get('/materials/{slug}', [UserDashboardController::class, 'materialDetail']);
    Route::post('/materials/{slug}/complete', [UserDashboardController::class, 'completeMaterial']);

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/stats', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::get('/users/{id}', [AdminController::class, 'userDetail']);
        Route::patch('/users/{id}', [AdminController::class, 'updateUser']);
        Route::post('/users/{id}/reset-password', [AdminController::class, 'resetPassword']);
        Route::post('/users', [AdminController::class, 'createUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

        Route::get('/categories', [AdminController::class, 'categories']);
        Route::post('/categories', [AdminController::class, 'createCategory']);
        Route::patch('/categories/{id}', [AdminController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [AdminController::class, 'deleteCategory']);

        Route::post('/tryouts', [AdminController::class, 'createTryout']);
        Route::patch('/tryouts/{id}', [AdminController::class, 'updateTryout']);
        Route::delete('/tryouts/{id}', [AdminController::class, 'deleteTryout']);
        Route::get('/tryouts/{tryoutId}/questions', [AdminController::class, 'getQuestions']);

        Route::post('/questions/upload-image', [AdminController::class, 'uploadImage']);
        Route::post('/questions', [AdminController::class, 'createQuestion']);
        Route::patch('/questions/{id}', [AdminController::class, 'updateQuestion']);
        Route::delete('/questions/{id}', [AdminController::class, 'deleteQuestion']);

        Route::get('/materials/{id}', [AdminController::class, 'materialDetail']);
        Route::post('/materials', [AdminController::class, 'createMaterial']);
        Route::patch('/materials/{id}', [AdminController::class, 'updateMaterial']);
        Route::delete('/materials/{id}', [AdminController::class, 'deleteMaterial']);
    });
});