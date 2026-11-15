<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SecurityController;
use App\Http\Controllers\WatchlistController;
use App\Models\Komoditas;
use Illuminate\Support\Facades\Route;

// ---------- Publik (Belum Login Hanya Bisa Lihat Landing Page) ----------
Route::get('/', function () {
    $cuplikanKomoditas = Komoditas::with('latestRiwayat')->orderBy('nama')->take(6)->get();
    $totalKomoditas    = Komoditas::count();

    return view('landing', compact('cuplikanKomoditas', 'totalKomoditas'));
})->name('landing');

// ---------- Guest (Halaman Masuk & Daftar) ----------
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register');
});

// ---------- Wajib Login (Semua Fitur Utama) ----------
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/api/chart/{komoditas:kode}', [DashboardController::class, 'chart'])
        ->where('komoditas', '[a-z0-9\-]+')
        ->middleware('throttle:chart')
        ->name('chart.data');
});

// ---------- Role: User ----------
Route::middleware(['auth', 'role:user'])->group(function () {
    Route::resource('watchlist', WatchlistController::class)
        ->only(['index', 'store', 'edit', 'update', 'destroy'])
        ->whereNumber('watchlist');

    // Pengaturan Akun, Profil, Sandi & Hapus Akun
    Route::get('/security', [SecurityController::class, 'index'])->name('security.index');
    Route::put('/security/profile', [SecurityController::class, 'updateProfile'])->name('security.profile.update');
    Route::post('/security/password', [SecurityController::class, 'updatePassword'])->name('security.password.update');
    Route::delete('/security/account', [SecurityController::class, 'destroyAccount'])->name('security.account.destroy');
});

// ---------- Role: Admin ----------
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/audit', [AdminController::class, 'audit'])->name('audit');
    Route::get('/export/csv', [AdminController::class, 'exportCsv'])->name('export.csv');
    Route::get('/export/pdf', [AdminController::class, 'exportPdf'])->name('export.pdf');
});

// Route Aksi Admin: Sinkronisasi Harga Bapanas Manual & Hapus Akun User Mencurigakan
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/sync-harga', function () {
        \Illuminate\Support\Facades\Artisan::call('fetch:sembako');
        app(\App\Services\AuditLogger::class)->log('admin.sync', 'Admin memicu sinkronisasi harga Bapanas manual');
        return back()->with('status', 'Sinkronisasi data harga Bapanas berhasil dijalankan.');
    })->name('sync');

    Route::delete('/users/{user}', function (\App\Models\User $user) {
        if ($user->isAdmin() || $user->id === \Illuminate\Support\Facades\Auth::id()) {
            return back()->withErrors(['user' => 'Akun Administrator utama tidak dapat dihapus.']);
        }
        $nama = $user->name;
        $email = $user->email;
        $user->delete();
        app(\App\Services\AuditLogger::class)->log('admin.user.delete', "Admin menghapus akun user: {$nama} ({$email})");
        return back()->with('status', "Akun pengguna {$nama} berhasil dihapus.");
    })->name('users.destroy');
});

Route::get('/setup-db', function () {
    \Illuminate\Support\Facades\Artisan::call('migrate:fresh', [
        '--seed' => true,
        '--force' => true
    ]);
    return 'Mantap! Database Aiven PostgreSQL berhasil disiapkan dan akun Admin telah dibuat.';
});