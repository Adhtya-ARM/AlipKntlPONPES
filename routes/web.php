<?php

use Illuminate\Support\Facades\Route;

// === AUTH CONTROLLERS ===
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;

// === USER CONTROLLERS ===
use App\Http\Controllers\User\SantriController;
use App\Http\Controllers\User\WaliController;
use App\Http\Controllers\User\UserController;
<<<<<<< HEAD

// === AKADEMIK CONTROLLERS ===
=======
use App\Http\Controllers\User\GuruController;
use App\Http\Controllers\Akademik\PenilaianController;
>>>>>>> f050ae17c144e6079ae8b8ec27ed5f44f35675f6
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

<<<<<<< HEAD
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
=======
    Route::middleware("auth:guru")->prefix("guru")->group(function () {
        Route::get("/dashboard", [DashboardController::class, "index"])->name("guru.dashboard")->defaults("guard", "guru");
        Route::get("/santri", [DashboardController::class, "guruSantri"])->name("guru.santri");
        Route::get("/wali", [DashboardController::class, "guruWali"])->name("guru.wali");
        Route::resource("", GuruController::class)->names('guru')->except(["show", "create", "edit"]);



    // Akademik routes (penilaian, absensi, mapel)
    Route::prefix("akademik")->name("akademik.")->group(function () {
        // Mapel routes
        Route::resource("mapel", MapelController::class)->except(["show", "create", "edit"]);

        // Absensi routes
        Route::resource("absensi", AbsensiController::class)->only(["index", "store", "update"]);
        Route::get("absensi/santri/{guruMapel}", [AbsensiController::class, "getSiswaList"])
            ->name("absensi.santri-list");

        // Penilaian routes dengan custom upload
        Route::resource("penilaian", PenilaianController::class)
            ->only(["index", "store", "update", "destroy"]);
        Route::post("penilaian/upload", [PenilaianController::class, "uploadAndProcessPdf"])
            ->name("penilaian.upload");
>>>>>>> f050ae17c144e6079ae8b8ec27ed5f44f35675f6
    });

<<<<<<< HEAD
// ======================================
// 👤 USER MANAGEMENT (Admin)
// ======================================
Route::middleware('auth:web')->group(function () {
    Route::resource('santri', SantriController::class);
    Route::resource('wali', WaliController::class);
    Route::resource('user', UserController::class);
});
=======
Route::middleware("auth:santri")->prefix("santri")->group(function () {
    Route::get("/dashboard", [DashboardController::class, "index"])->name("santri.dashboard")->defaults("guard", "santri");
});

Route::middleware("auth:wali")->prefix("wali")->group(function () {
    Route::get("/dashboard", [DashboardController::class, "index"])->name("wali.dashboard")->defaults("guard", "wali");
});

// User management routes - admin only
Route::middleware("auth:web")->group(function () {
    Route::resource("santri", SantriController::class);
    Route::resource("wali", WaliController::class);
    Route::resource("user", UserController::class);
});
>>>>>>> f050ae17c144e6079ae8b8ec27ed5f44f35675f6
