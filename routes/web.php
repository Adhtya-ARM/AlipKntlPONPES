<?php

use Illuminate\Support\Facades\Route;

// === AUTH CONTROLLERS ===
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;

// === USER CONTROLLERS ===
use App\Http\Controllers\User\SantriController;
use App\Http\Controllers\User\WaliController;
use App\Http\Controllers\User\UserController;

// === AKADEMIK CONTROLLERS ===
use App\Http\Controllers\Akademik\MapelController;
use App\Http\Controllers\Akademik\AbsensiController;
use App\Http\Controllers\Akademik\PenilaianController;
use App\Http\Controllers\Akademik\KelasController;

// ======================================
// 🔐 LOGIN & LOGOUT
// ======================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout/{guard}', [AuthController::class, 'logout'])->name('logout');

// ======================================
// 🧭 DASHBOARD PER ROLE
// ======================================
Route::middleware('auth:web')->prefix('admin')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard')->defaults('guard', 'web');
});

Route::middleware('auth:guru')->prefix('guru')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('guru.dashboard')->defaults('guard', 'guru');
});

Route::middleware('auth:santri')->prefix('santri')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('santri.dashboard')->defaults('guard', 'santri');
});

Route::middleware('auth:wali')->prefix('wali')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('wali.dashboard')->defaults('guard', 'wali');
});

// ======================================
// 🎓 AKADEMIK (Guru & Admin)
// ======================================
Route::middleware(['auth:guru,web'])->prefix('akademik')->name('akademik.')->group(function () {

    // ---- 🔹 MAPEL ----
        Route::post('mapel/rencana/{guruMapel}', [MapelController::class, 'updateRencana'])->name('mapel.update-rencana'); // 🔥 harus sebelum resource
        Route::get('mapel/{guruMapel}/siswa', [MapelController::class, 'getSiswa'])->name('mapel.siswa');
        Route::post('mapel/{guruMapel}/siswa', [MapelController::class, 'updateSiswa'])->name('mapel.siswa.update');
        Route::resource('mapel', MapelController::class);

    //---- Kelas ---//
       Route::get('kelas/{kelas}/siswa', [KelasController::class, 'getSiswa'])->name('kelas.siswa');
       Route::post('kelas/{kelas}/siswa', [KelasController::class, 'updateSiswa'])->name('kelas.siswa.update');
      Route::resource('kelas', KelasController::class);
    
    // ---- 🔹 ABSENSI ----
    Route::get('absensi/santri/{guruMapel}', [AbsensiController::class, 'getSiswaList'])->name('absensi.santri-list');
    Route::resource('absensi', AbsensiController::class)->only(['index', 'update']);

    // ---- 🔹 PENILAIAN ----
    Route::post('penilaian/upload', [PenilaianController::class, 'uploadAndProcessPdf'])->name('penilaian.upload');
    Route::resource('penilaian', PenilaianController::class) ->only(['index', 'store', 'update', 'destroy']);
    });

// ======================================
// 👤 USER MANAGEMENT (Admin)
// ======================================
Route::middleware('auth:web')->group(function () {
    Route::resource('santri', SantriController::class);
    Route::resource('wali', WaliController::class);
    Route::resource('user', UserController::class);
});
