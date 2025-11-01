<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\User\SantriController;
use App\Http\Controllers\User\WaliController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\GuruController;
use App\Http\Controllers\Akademik\PenilaianController;
use App\Http\Controllers\Akademik\MapelController;
use App\Http\Controllers\Akademik\AbsensiController;

// Login routes (no auth required)
Route::get("/login", [AuthController::class, "showLoginForm"])->name("login");
Route::post("/login", [AuthController::class, "login"])->name("login.process");
Route::post("/logout/{guard}", [AuthController::class, "logout"])->name("logout");

// Dashboard routes - separate per guard
Route::middleware("auth:web")->prefix("admin")->group(function () {
    Route::get("/dashboard", [DashboardController::class, "index"])->name("admin.dashboard")->defaults("guard", "web");
});

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
    });
});

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