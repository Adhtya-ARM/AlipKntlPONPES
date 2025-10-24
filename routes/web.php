<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\User\SantriController;
use App\Http\Controllers\User\WaliController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Akademik\PenilaianController;
use Illuminate\Support\Facades\Config;
// Ambil semua guard berbasis sesi dari config/auth.php

// Ambil semua guard berbasis sesi
$allGuards = Config::get('auth.guards');
$webGuards = array_keys(array_filter($allGuards, fn($g) => $g['driver'] === 'session'));
$guardRegex = implode('|', $webGuards); 

// Login URL tunggal: /login
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');


// Route Dashboard (Tetap butuh middleware auth:guard)
foreach ($webGuards as $guard) {
    Route::middleware("auth:{$guard}")->group(function () use ($guard) {
        
        // 1. Rute Dashboard: /guru/dashboard, /santri/dashboard, dst.
        // Memanggil DashboardController::index dan meneruskan nama guard.
        Route::get("/{$guard}/dashboard", [DashboardController::class, 'index'])
            ->name("{$guard}.dashboard")
            ->defaults('guard', $guard); 

        // 2. Rute Logout: Setiap role memanggil AuthController::logout
        // Menggunakan POST karena ini adalah aksi yang mengubah state.
        Route::post("/{$guard}/logout", [AuthController::class, 'logout'])
            ->name("{$guard}.logout")
            ->defaults('guard', $guard); 
    });
    
        Route::resource("santri", SantriController::class);
        Route::resource("penilaian", PenilaianController::class);
        Route::resource("wali", WaliController::class);
        Route::resource("user", UserController::class);
}