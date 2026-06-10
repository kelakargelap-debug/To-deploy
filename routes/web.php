<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebAuthController;
use App\Http\Controllers\TotpController;

// Auth routes (session-based for web interface)
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login']);
Route::post('/register', [WebAuthController::class, 'register'])->name('register');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// TOTP verification during login (no auth required, session-based)
Route::get('/verify-totp', [TotpController::class, 'showVerifyLogin'])->name('totp.verify-login');
Route::post('/verify-totp', [TotpController::class, 'verifyLogin']);

// TOTP setup (requires auth — user is logged in but may be pending_verification)
Route::middleware(['auth'])->group(function () {
    Route::get('/setup-totp', [TotpController::class, 'showSetup'])->name('totp.setup');
    Route::post('/setup-totp', [TotpController::class, 'verifySetup']);
    Route::get('/backup-codes', [TotpController::class, 'showBackupCodes'])->name('totp.backup-codes');
    Route::post('/backup-codes/acknowledge', [TotpController::class, 'acknowledgeBackupCodes'])->name('totp.acknowledge-backup');
});

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

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');

    Route::get('/security', [\App\Http\Controllers\SecurityController::class, 'index'])->name('security.index');
    Route::delete('/security/devices/{id}', [\App\Http\Controllers\SecurityController::class, 'revokeDevice'])->name('security.revoke-device');
    Route::post('/security/logout-all', [\App\Http\Controllers\SecurityController::class, 'requestLogoutAll'])->name('security.logout-all');
    Route::post('/security/logout-all/confirm', [\App\Http\Controllers\SecurityController::class, 'confirmLogoutAll'])->name('security.logout-all.confirm');

    // TOTP management from security settings
    Route::post('/security/totp/reset', [TotpController::class, 'resetTotp'])->name('security.totp.reset');
    Route::post('/security/totp/regenerate-backup', [TotpController::class, 'regenerateBackupCodes'])->name('security.totp.regenerate-backup');

    Route::put('/profile', function (\Illuminate\Http\Request $request) {
        $user = auth()->user();
        $request->validate([
            'name' => 'required|string|max:255',
        ]);
        $user->update(['name' => $request->name]);
        return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
    });

    Route::get('/change-password', function () {
        return redirect()->route('profile');
    })->name('change-password');

    Route::post('/change-password', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
            'new_password_confirmation' => 'required',
        ]);
        $user = auth()->user();
        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password saat ini salah.']);
        }
        $user->update(['password' => \Illuminate\Support\Facades\Hash::make($request->new_password)]);
        return redirect()->back()->with('success', 'Password berhasil diubah.');
    });

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