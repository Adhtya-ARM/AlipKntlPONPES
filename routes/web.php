<?php

use Illuminate\Support\Facades\Route;

// === AUTH CONTROLLERS ===
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;

// === USER CONTROLLERS ===
use App\Http\Controllers\User\SantriController;
use App\Http\Controllers\User\WaliController;
use App\Http\Controllers\User\GuruController;
use App\Http\Controllers\User\GuruAkademikController;

// === AKADEMIK CONTROLLERS ===
use App\Http\Controllers\Akademik\AbsensiController;
use App\Http\Controllers\Akademik\PenilaianController;
use App\Http\Controllers\Akademik\KelasController;
use App\Http\Controllers\Akademik\MapelController;
use App\Http\Controllers\Akademik\GuruMapelController;
use App\Http\Controllers\Akademik\RencanaPembelajaranController;
use App\Http\Controllers\Akademik\JadwalPelajaranController;
use App\Http\Controllers\Akademik\SekolahProfileController;
use App\Http\Controllers\Akademik\TahunAjaranController;
use App\Http\Controllers\Akademik\EraportController;

// === SANTRI & WALI CONTROLLERS ===
use App\Http\Controllers\Santri\SantriAkademikController;
use App\Http\Controllers\Wali\WaliAkademikController;

// ======================================
// 🏠 LANDING PAGE
// ======================================
use App\Http\Controllers\LandingController;
Route::get('/', [LandingController::class, 'index'])->name('landing');

// ======================================
// 🔐 LOGIN & LOGOUT
// ======================================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout/{guard}', [AuthController::class, 'logout'])->name('logout');

// ======================================
// 🧭 DASHBOARD PER ROLE
// ======================================

Route::middleware('auth:guru')->prefix('guru')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('guru.dashboard')->defaults('guard', 'guru');
});

Route::middleware('auth:santri')->prefix('santri')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('santri.dashboard')->defaults('guard', 'santri');
    
    // Akademik Santri
    Route::get('/kehadiran', [SantriAkademikController::class, 'kehadiran'])->name('santri.kehadiran');
    Route::get('/mapel', [SantriAkademikController::class, 'mapel'])->name('santri.mapel');
    Route::get('/nilai', [SantriAkademikController::class, 'nilai'])->name('santri.nilai');
});

Route::middleware('auth:wali')->prefix('wali')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('wali.dashboard')->defaults('guard', 'wali');
    
    // Monitoring Anak
    Route::get('/kehadiran', [WaliAkademikController::class, 'kehadiran'])->name('wali.kehadiran');
    Route::get('/mapel', [WaliAkademikController::class, 'mapel'])->name('wali.mapel');
    Route::get('/nilai', [WaliAkademikController::class, 'nilai'])->name('wali.nilai');
});

//========
//  User Management
// ========
Route::middleware(['auth:guru'])->group(function () {
Route::resource('santri', SantriController::class);
 Route::resource('wali', WaliController::class);
 Route::resource('guru', GuruController::class);
});

// ======================================
// 🎓 AKADEMIK (Guru & Admin)
// ======================================
Route::middleware(['auth:guru'])->prefix('akademik')->name('akademik.')->group(function () {

    // ---- Kalender 
    Route::resource('rencana-pembelajaran', RencanaPembelajaranController::class);
    
    //--- Mapel ---//
    Route::get('mapel', [MapelController::class, 'index'])->name('mapel.index');
    Route::post('mapel', [MapelController::class, 'store'])->name('mapel.store');
    Route::put('mapel/{mapel}', [MapelController::class, 'update'])->name('mapel.update');
    Route::delete('mapel/{mapel}', [MapelController::class, 'destroy'])->name('mapel.destroy');
    
    //--- Guru Mapel (Pilih Mapel yang Diajar) ---//
    Route::get('guru-mapel', [GuruMapelController::class, 'index'])->name('guru-mapel.index');
    Route::post('guru-mapel', [GuruMapelController::class, 'store'])->name('guru-mapel.store');
    Route::delete('guru-mapel/{guruMapel}', [GuruMapelController::class, 'destroy'])->name('guru-mapel.destroy');
    Route::get('guru-mapel/{guruMapel}/rekap', [GuruMapelController::class, 'rekap'])->name('guru-mapel.rekap');
    Route::delete('guru-mapel/{guruMapel}/clear-grades', [GuruMapelController::class, 'clearGrades'])->name('guru-mapel.clear-grades');
    Route::delete('guru-mapel/{guruMapel}/reset-absensi', [GuruMapelController::class, 'resetAbsensi'])->name('guru-mapel.reset-absensi');

    // --- ARSIP KELAS (Historical Data - Snapshot Based) ---
    Route::get('arsip', [App\Http\Controllers\Akademik\ArsipController::class, 'index'])->name('arsip.index');
    Route::get('arsip/{arsip}', [App\Http\Controllers\Akademik\ArsipController::class, 'show'])->name('arsip.show');
    Route::post('arsip/create', [App\Http\Controllers\Akademik\ArsipController::class, 'createFromForm'])->name('arsip.create');
    
    // --- E-RAPORT (Active Students - Current Year) ---
    Route::get('eraport', [EraportController::class, 'index'])->name('eraport.index');
    Route::get('eraport/{kelas}/cetak-semua', [EraportController::class, 'cetakSemua'])->name('eraport.cetak-semua');
    Route::get('eraport/{kelas}/santri/{santri}', [EraportController::class, 'detailSantri'])->name('eraport.detail-santri');
    Route::get('eraport/{kelas}/santri/{santri}/cetak', [EraportController::class, 'cetakSantri'])->name('eraport.cetak-santri');
    
    //---- Kelas ---//
    Route::get('kelas/{kelas}/siswa', [KelasController::class, 'getSiswa'])->name('kelas.siswa');
    Route::post('kelas/{kelas}/siswa', [KelasController::class, 'updateSiswa'])->name('kelas.siswa.update');
    Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas']);
    
      // --- Absensi Input Per Mapel ---
      Route::get('absensi', [AbsensiController::class, 'index'])->name('absensi.index');
      Route::get('absensi/{guruMapelId}/santri', [AbsensiController::class, 'getSantriByMapel'])->name('absensi.getSantri');
      Route::post('absensi/store', [AbsensiController::class, 'store'])->name('absensi.store');
      Route::post('absensi/reset', [AbsensiController::class, 'resetAbsensi'])->name('absensi.reset');

    // --- Rekap Kehadiran ---
      Route::get('rekap-kehadiran', [AbsensiController::class, 'rekap'])->name('rekap-kehadiran.index');
      Route::get('rekap-kehadiran/data', [AbsensiController::class, 'getRekapData'])->name('rekap-kehadiran.getData');

    // --- Jadwal Pelajaran (Kepsek/Waka only for CRUD) ---
    Route::get('jadwal-pelajaran', [JadwalPelajaranController::class, 'index'])->name('jadwal-pelajaran.index');
    Route::post('jadwal-pelajaran', [JadwalPelajaranController::class, 'store'])->name('jadwal-pelajaran.store');
    Route::put('jadwal-pelajaran/{jadwalPelajaran}', [JadwalPelajaranController::class, 'update'])->name('jadwal-pelajaran.update');
    Route::delete('jadwal-pelajaran/{jadwalPelajaran}', [JadwalPelajaranController::class, 'destroy'])->name('jadwal-pelajaran.destroy');
    Route::get('jadwal-pelajaran/hari/{hari}', [JadwalPelajaranController::class, 'getByDay'])->name('jadwal-pelajaran.byDay');
    Route::get('jadwal-pelajaran/guru-mapels', [JadwalPelajaranController::class, 'getGuruMapels'])->name('jadwal-pelajaran.guruMapels');

    Route::get('penilaian/{guruMapelId}/santri', [PenilaianController::class, 'getSantriByMapel'])->name('penilaian.getSantri');
    Route::get('rekap-penilaian', [PenilaianController::class, 'rekap'])->name('rekap-penilaian.index');
    Route::get('rekap-penilaian/data', [PenilaianController::class, 'getRekapData'])->name('rekap-penilaian.getData');
    Route::resource('penilaian', PenilaianController::class);

    // --- Kelas Saya (Guru) ---
    Route::get('kelas-saya', [GuruAkademikController::class, 'kelasSaya'])->name('kelas-saya.index');

    // --- Teacher Archive (Historical Data - Read-Only) ---
    Route::get('guru/arsip', [\App\Http\Controllers\User\GuruArsipController::class, 'index'])->name('guru.arsip.index');
    Route::get('guru/arsip/semester/{semester}', [\App\Http\Controllers\User\GuruArsipController::class, 'show'])->name('guru.arsip.show');
    Route::get('guru/arsip/detail/{guruMapel}', [\App\Http\Controllers\User\GuruArsipController::class, 'detail'])->name('guru.arsip.detail');
    });
// ======================================
// ℹ️ INFORMASI SEKOLAH
// ======================================
Route::middleware(['auth:guru'])->prefix('informasi')->name('informasi.')->group(function () {
    Route::get('struktur-organisasi', [\App\Http\Controllers\Informasi\StrukturOrganisasiController::class, 'index'])->name('struktur-organisasi.index');
    Route::post('struktur-organisasi/update', [\App\Http\Controllers\Informasi\StrukturOrganisasiController::class, 'update'])->name('struktur-organisasi.update');
});

// ======================================
// 🏫 MANAJEMEN SEKOLAH (Waka/Kepsek)
// ======================================
Route::middleware(['auth:guru'])->prefix('manajemen-sekolah')->name('manajemen-sekolah.')->group(function () {
    // Profil Sekolah
    Route::get('sekolah', [SekolahProfileController::class, 'index'])->name('sekolah.index');
    Route::put('sekolah', [SekolahProfileController::class, 'update'])->name('sekolah.update');

    // Tahun Ajaran
    Route::post('tahun-ajaran/{tahunAjaran}/activate', [TahunAjaranController::class, 'activate'])->name('tahun-ajaran.activate');
    Route::resource('tahun-ajaran', TahunAjaranController::class);
    Route::get('kenaikan-kelas', [\App\Http\Controllers\Akademik\KenaikanKelasController::class, 'index'])->name('kenaikan-kelas.index');
    Route::get('kenaikan-kelas/get-santri', [\App\Http\Controllers\Akademik\KenaikanKelasController::class, 'getSantri'])->name('kenaikan-kelas.getSantri');
    Route::post('kenaikan-kelas', [\App\Http\Controllers\Akademik\KenaikanKelasController::class, 'process'])->name('kenaikan-kelas.process');
});

// ======================================
// 👤 USER PROFILE (Shared)
// ======================================
Route::middleware(['auth:guru,santri,wali'])->group(function () {
    Route::get('profile', [\App\Http\Controllers\User\ProfileController::class, 'index'])->name('profile.index');
    Route::put('profile', [\App\Http\Controllers\User\ProfileController::class, 'update'])->name('profile.update');
});

