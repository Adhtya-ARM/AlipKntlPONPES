<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Santri;
use App\Models\User\SantriProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SantriController extends Controller
{
    // Definisikan nilai status yang valid
    private const VALID_STATUSES = ["aktif", "non-aktif", "lulus", "dropout"];

    /**
     * Tampilkan daftar semua santri (READ - All).
     */
    public function index()
    {
        // 1. Ambil data dengan pagination (WAJIB PAGINATOR)
        $santris = Santri::with("santriprofile")->paginate(10);

        // 2. Inisialisasi Model Santri kosong untuk modal CREATE
        // Ini memastikan variabel $santri (tunggal) selalu ada di view.
        $santri = new Santri();

        // 3. Kirim kedua variabel ke view
        return view("User.Santri.index", compact("santris", "santri"));
    }

    /**
     * Tampilkan form untuk membuat santri baru (CREATE - Form).
     */
    public function create()
    {
        $statuses = self::VALID_STATUSES;
        // Jika Anda menggunakan modal, metode ini mungkin tidak terpakai,
        // tetapi tetap dipertahankan untuk Resource Controller standar.
        return view("User.Santri.create", compact("statuses"));
    }

    /**
     * Simpan data santri baru ke database (CREATE - Store).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            "username" => "required|string|unique:santris,nis|max:50",
            "password" => "required|string|min:8|confirmed",

            // Data Profile Santri
            "nama" => "required|string|max:255",
            "alamat" => "required|string|max:255",
            "wali" => "required|string|max:255",
            "kelas" => "required|string|max:100",
            "kamar" => "required|string|max:100",
            "status" => ["required", "string", Rule::in(self::VALID_STATUSES)],
        ]);

        // 2. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // A. Simpan data ke tabel Santri
            $santri = Santri::create([
                "username" => $validatedData["nis"],
                "password" => Hash::make($validatedData["password"]),
            ]);

            // B. Simpan data ke tabel SantriProfile menggunakan relasi
            $santri->santriprofile()->create([
                "nama" => $validatedData["nama"],
                "alamat" => $validatedData["alamat"],
                "wali" => $validatedData["wali"],
                "kelas" => $validatedData["kelas"],
                "kamar" => $validatedData["kamar"],
                "status" => $validatedData["status"],
            ]);

            DB::commit();

            $santriNama = $validatedData["nama"];

            return redirect()
                ->route("santris.index") // Pastikan route name Anda konsisten (santris.index atau santri.index)
                ->with(
                    "success",
                    "Data Santri **" . $santriNama . "** berhasil ditambahkan!",
                );
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal menyimpan data Santri. Detail: " . $e->getMessage(),
                );
        }
    }

    /**
     * Tampilkan detail santri tertentu (READ - Single).
     */
    public function show(Santri $santri)
    {
        $santri->load("santriprofile");
        // Catatan: Jika Anda menggunakan modal untuk detail di index.blade.php, metode ini tidak akan terpanggil.
        return view("User.Santri.show", compact("santri"));
    }

    /**
     * Tampilkan form untuk mengedit santri tertentu (UPDATE - Form).
     */
    public function edit(Santri $santri)
    {
        $santri->load("santriprofile");
        $statuses = self::VALID_STATUSES;
        // Catatan: Jika Anda menggunakan modal untuk edit di index.blade.php, metode ini tidak akan terpanggil.
        return view("User.Santri.edit", compact("santri", "statuses"));
    }

    /**
     * Perbarui data santri di database (UPDATE - Store).
     */
    public function update(Request $request, Santri $santri)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            "username" => [
                "required",
                "string",
                "max:50",
                Rule::unique("santris")->ignore($santri->id),
            ],
            "password" => "nullable|string|min:8|confirmed",

            // Data Profile Santri
            "nama" => "required|string|max:255",
            "alamat" => "required|string|max:255",
            "wali" => "required|string|max:255",
            "kelas" => "required|string|max:100",
            "kamar" => "required|string|max:100",
            "status" => ["required", "string", Rule::in(self::VALID_STATUSES)],
        ]);

        // 2. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // A. Update data tabel Santri
            $santriData = [
                "nis" => $validatedData["nis"],
            ];

            if (!empty($validatedData["password"])) {
                $santriData["password"] = Hash::make(
                    $validatedData["password"],
                );
            }

            $santri->update($santriData);

            // B. Update data tabel SantriProfile
            $santri->santriprofile()->update([
                "nama" => $validatedData["nama"],
                "alamat" => $validatedData["alamat"],
                "wali" => $validatedData["wali"],
                "kelas" => $validatedData["kelas"],
                "kamar" => $validatedData["kamar"],
                "status" => $validatedData["status"],
            ]);

            DB::commit();

            $santriNama = $validatedData["nama"];

            return redirect()
                ->route("santris.index") // Pastikan route name Anda konsisten
                ->with(
                    "success",
                    "Data Santri **" . $santriNama . "** berhasil diperbarui!",
                );
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal memperbarui data Santri. Detail: " .
                        $e->getMessage(),
                );
        }
    }

    public function destroy(Santri $santri)
    {
        $nama = $santri->santriprofile->nama ?? "Nama Santri";

        try {
            DB::beginTransaction();

            // 1. Hapus data profile terkait (CHILD)
            // Ini akan mengatasi error Integrity Constraint 1451
            if ($santri->santriprofile) {
                $santri->santriprofile->delete();
            }

            // 2. Hapus data santri (PARENT)
            $santri->delete();

            DB::commit();

            return redirect()
                ->route("santri.index")
                ->with(
                    "success",
                    "Data Santri **" . $nama . "** berhasil dihapus!",
                );
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with(
                "error",
                "Gagal menghapus data Santri. Detail: " . $e->getMessage(),
            );
        }
    }
}
