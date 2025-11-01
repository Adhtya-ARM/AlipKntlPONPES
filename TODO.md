# TODO: Implementasi CRUD Guru

## 1. Update GuruController.php

-   [x] Copy logic dari SantriController
-   [x] Sesuaikan untuk model Guru dan GuruProfile
-   [x] Field: username, password, nama, jabatan, alamat, no_hp

## 2. Update routes/web.php

-   [x] Tambahkan Route::resource("guru", GuruController::class) di dalam middleware auth:guru
-   [x] Pastikan route /guru/guru tersedia

## 3. Update view index.blade.php

-   [x] Sesuaikan dari Santri index
-   [x] Ganti field: NIS -> Username, tambah Jabatan, No HP
-   [x] Update Alpine.js untuk field baru
-   [x] Tambahkan kolom Mata Pelajaran dengan tombol dummy "Pilih Mapel"

## 4. Test dan Verifikasi

-   [x] Jalankan aplikasi, pastikan route /guru/guru bekerja
-   [x] Pastikan routes lainnya tidak error
-   [x] Test CRUD: Create, Read, Update, Delete guru
-   [x] Verifikasi data tersimpan di tabel guru dan guru_profile
-   [x] Tambahkan data test via tinker, sekarang ada 3 guru
