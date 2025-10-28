<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\User\SantriController;
use App\Http\Controllers\User\WaliController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Akademik\PenilaianController;
use App\Http\Controllers\Akademik\MapelController;
use Illuminate\Support\Facades\Config;
// Ambil semua guard berbasis sesi dari config/auth.php

// Ambil semua guard berbasis sesi
$allGuards = Config::get("auth.guards");
$webGuards = array_keys(
    array_filter($allGuards, fn($g) => $g["driver"] === "session"),
);
$guardRegex = implode("|", $webGuards);

// Login URL tunggal: /login
Route::get("/login", [AuthController::class, "showLoginForm"])->name("login");
Route::post("/login", [AuthController::class, "login"])->name("login.process");
Route::post("/logout/{guard}", [
    App\Http\Controllers\Auth\AuthController::class,
    "logout",
])->name("logout");

// Route Dashboard (Tetap butuh middleware auth:guard)
foreach ($webGuards as $guard) {
    Route::middleware("auth:{$guard}")->group(function () use ($guard) {
        // 1. Rute Dashboard: /guru/dashboard, /santri/dashboard, dst.
        // Memanggil DashboardController::index dan meneruskan nama guard.
        Route::get("/{$guard}/dashboard", [DashboardController::class, "index"])
            ->name("{$guard}.dashboard")
            ->defaults("guard", $guard);

        // 2. Rute Logout: Setiap role memanggil AuthController::logout
        // Menggunakan POST karena ini adalah aksi yang mengubah state.
        Route::post("/{$guard}/logout", [AuthController::class, "logout"])
            ->name("{$guard}.logout")
            ->defaults("guard", $guard);
    });
<<<<<<< HEAD

    // 1. Gunakan Route::resource untuk Penilaian
    // Hanya sertakan index, store, update, destroy
    Route::resource('penilaian', PenilaianController::class)->only([
        'index', // GET /penilaian (Tabel utama)
        'store', // POST /penilaian (Menyimpan data baru dari modal CREATE)
        'update', // PUT/PATCH /penilaian/{penilaian} (Memperbarui data dari modal EDIT)
        'destroy', // DELETE /penilaian/{penilaian} (Menghapus data)
    ]);
  
    // 2. Tambahkan custom route untuk Upload PDF
    Route::post('penilaian/upload-pdf', [PenilaianController::class, 'uploadAndProcessPdf'])->name('penilaian.upload.pdf');

    Route::resource('mapel', App\Http\Controllers\Akademik\MapelController::class)->names('akademik.mapel')->except(['show', 'create', 'edit']);
    
    // ATAU jika Anda hanya ingin rute yang diperlukan:
    Route::prefix('akademik')->name('akademik.')->group(function () {
        Route::post('mapel', [App\Http\Controllers\Akademik\MapelController::class, 'store'])->name('mapel.store');
        Route::put('mapel/{mapel}', [App\Http\Controllers\Akademik\MapelController::class, 'update'])->name('mapel.update');
        Route::delete('mapel/{mapel}', [App\Http\Controllers\Akademik\MapelController::class, 'destroy'])->name('mapel.destroy');
        Route::get('mapel', [App\Http\Controllers\Akademik\MapelController::class, 'index'])->name('mapel.index');
        // Jika Anda ingin detail, aktifkan ini juga
        Route::get('mapel/{mapel}', [App\Http\Controllers\Akademik\MapelController::class, 'show'])->name('mapel.show');
    });
    
    Route::resource("santri", SantriController::class);
    Route::resource("wali", WaliController::class);
    Route::resource("user", UserController::class);
=======
>>>>>>> 98f639d64081b54f598da9eeff848454bc5b332b
}

// Define resource routes outside the foreach to avoid multiple definitions
// These routes will be accessible without specific guard middleware, but the controller handles authentication
Route::resource('penilaian', PenilaianController::class)->only([
    'index', // GET /penilaian (Tabel utama)
    'store', // POST /penilaian (Menyimpan data baru dari modal CREATE)
    'update', // PUT/PATCH /penilaian/{penilaian} (Memperbarui data dari modal EDIT)
    'destroy', // DELETE /penilaian/{penilaian} (Menghapus data)
]);

// Custom route untuk Upload PDF
Route::post('penilaian/upload-pdf', [PenilaianController::class, 'uploadAndProcessPdf'])->name('penilaian.upload.pdf');

Route::resource("santri", SantriController::class);
Route::resource("wali", WaliController::class);
Route::resource("user", UserController::class);