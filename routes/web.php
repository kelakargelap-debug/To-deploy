<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;

// Auth routes (session-based for web interface)
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/register', [WebAuthController::class, 'register'])->name('register');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// Session toggle routes (session-based auth)
Route::middleware(['auth'])->group(function () {
    Route::post('/toggle-dark-mode', function () {
        session(['dark_mode' => !session('dark_mode', false)]);
        return redirect()->back();
    })->name('toggle-dark-mode');

    Route::post('/toggle-sidebar', function () {
        session(['sidebar_collapsed' => !session('sidebar_collapsed', false)]);
        return redirect()->back();
    })->name('toggle-sidebar');
});

// Protected routes (session-based auth)
Route::middleware(['auth'])->group(function () {
    // User routes
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/tryouts', function () {
        return view('tryouts.index');
    })->name('tryouts');

    Route::get('/tryouts/{slug}', function ($slug) {
        return view('tryouts.detail', ['slug' => $slug]);
    })->name('tryout-detail');

    Route::get('/tryouts/{slug}/exam', function ($slug) {
        return view('tryouts.exam', ['slug' => $slug]);
    })->name('tryout-exam');

    Route::get('/tryouts/{slug}/result/{attemptId}', function ($slug, $attemptId) {
        return view('tryouts.result', ['slug' => $slug, 'attemptId' => $attemptId]);
    })->name('tryout-result');

    Route::get('/materials', function () {
        return view('materials.index');
    })->name('materials');

    Route::get('/materials/{slug}', function ($slug) {
        return view('materials.detail', ['slug' => $slug]);
    })->name('material-detail');

    Route::get('/my-attempts', function () {
        return view('attempts.index');
    })->name('my-attempts');

    Route::get('/change-password', function () {
        return view('auth.change-password');
    })->name('change-password');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function () {
            return view('admin.dashboard');
        })->name('admin-dashboard');

        Route::get('/users', function () {
            return view('admin.users.index');
        })->name('admin-users');

        Route::get('/users/create', function () {
            return view('admin.users.create');
        })->name('admin-user-create');

        Route::get('/users/{id}/edit', function ($id) {
            return view('admin.users.edit', ['id' => $id]);
        })->name('admin-user-edit');

        Route::get('/categories', function () {
            return view('admin.categories');
        })->name('admin-categories');

        Route::get('/tryouts', function () {
            return view('admin.tryouts.index');
        })->name('admin-tryouts');

        Route::get('/tryouts/create', function () {
            return view('admin.tryouts.form');
        })->name('admin-tryout-create');

        Route::get('/tryouts/{id}/edit', function ($id) {
            return view('admin.tryouts.form', ['id' => $id]);
        })->name('admin-tryout-edit');

        Route::get('/tryouts/{id}/questions', function ($id) {
            return view('admin.tryouts.questions', ['id' => $id]);
        })->name('admin-tryout-questions');

        Route::get('/materials', function () {
            return view('admin.materials.index');
        })->name('admin-materials');

        Route::get('/materials/create', function () {
            return view('admin.materials.form');
        })->name('admin-material-create');

        Route::get('/materials/{id}/edit', function ($id) {
            return view('admin.materials.form', ['id' => $id]);
        })->name('admin-material-edit');

        Route::get('/questions/create', function () {
            return view('admin.questions.form');
        })->name('admin-question-create');

        Route::get('/questions/{id}/edit', function ($id) {
            return view('admin.questions.form', ['id' => $id]);
        })->name('admin-question-edit');
    });
});

// Root redirect
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});


Route::fallback(function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});