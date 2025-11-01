<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User\Guru;
use App\Models\User\GuruProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class GuruController extends Controller
{
    // Definisikan nilai status yang valid (opsional, jika ada status untuk guru)
    private const VALID_STATUSES = ["aktif", "non-aktif"];

    /**
     * Tampilkan daftar semua guru (READ - All).
     */
    public function index()
    {
        // 1. Ambil data dengan pagination (WAJIB PAGINATOR)
        $gurus = Guru::with("guruProfile")->paginate(10);

        // 2. Inisialisasi Model Guru kosong untuk modal CREATE
        $guru = new Guru();

        // 3. Kirim kedua variabel ke view
        return view("User.Guru.index", compact("gurus", "guru"));
    }

    /**
     * Tampilkan form untuk membuat guru baru (CREATE - Form).
     */
    public function create()
    {
        $statuses = self::VALID_STATUSES;
        return view("User.Guru.create", compact("statuses"));
    }

    /**
     * Simpan data guru baru ke database (CREATE - Store).
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            "username" => "required|string|unique:guru,username|max:50",
            "password" => "required|string|min:8|confirmed",

            // Data Profile Guru
            "nama" => "required|string|max:255",
            "jabatan" => "required|string|max:255",
            "alamat" => "required|string|max:255",
            "no_hp" => "nullable|string|max:20",
        ]);

        // 2. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // A. Simpan data ke tabel Guru
            $guru = Guru::create([
                "username" => $validatedData["username"],
                "password" => $validatedData["password"],
            ]);

            // B. Simpan data ke tabel GuruProfile menggunakan relasi
            $guru->guruProfile()->create([
                "nama" => $validatedData["nama"],
                "jabatan" => $validatedData["jabatan"],
                "alamat" => $validatedData["alamat"],
                "no_hp" => $validatedData["no_hp"],
            ]);

            DB::commit();

            $guruNama = $validatedData["nama"];

            return redirect()
                ->route("guru.index")
                ->with(
                    "success",
                    "Data Guru **" . $guruNama . "** berhasil ditambahkan!",
                );
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal menyimpan data Guru. Detail: " . $e->getMessage(),
                );
        }
    }

    /**
     * Tampilkan detail guru tertentu (READ - Single).
     */
    public function show(Guru $guru)
    {
        $guru->load("guruProfile");
        return view("User.Guru.show", compact("guru"));
    }

    /**
     * Tampilkan form untuk mengedit guru tertentu (UPDATE - Form).
     */
    public function edit(Guru $guru)
    {
        $guru->load("guruProfile");
        $statuses = self::VALID_STATUSES;
        return view("User.Guru.edit", compact("guru", "statuses"));
    }

    /**
     * Perbarui data guru di database (UPDATE - Store).
     */
    public function update(Request $request, Guru $guru)
    {
        // 1. Validasi Input
        $validatedData = $request->validate([
            "username" => [
                "required",
                "string",
                "max:50",
                Rule::unique("guru")->ignore($guru->id),
            ],
            "password" => "nullable|string|min:8|confirmed",

            // Data Profile Guru
            "nama" => "required|string|max:255",
            "jabatan" => "required|string|max:255",
            "alamat" => "required|string|max:255",
            "no_hp" => "nullable|string|max:20",
        ]);

        // 2. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // A. Update data tabel Guru
            $guruData = [
                "username" => $validatedData["username"],
            ];

            if (!empty($validatedData["password"])) {
                $guruData["password"] = $validatedData["password"];
            }

            $guru->update($guruData);

            // B. Update data tabel GuruProfile
            $guru->guruProfile()->update([
                "nama" => $validatedData["nama"],
                "jabatan" => $validatedData["jabatan"],
                "alamat" => $validatedData["alamat"],
                "no_hp" => $validatedData["no_hp"],
            ]);

            DB::commit();

            $guruNama = $validatedData["nama"];

            return redirect()
                ->route("guru.index")
                ->with(
                    "success",
                    "Data Guru **" . $guruNama . "** berhasil diperbarui!",
                );
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with(
                    "error",
                    "Gagal memperbarui data Guru. Detail: " .
                        $e->getMessage(),
                );
        }
    }

    /**
     * Hapus data guru dari database (DELETE).
     */
    public function destroy(Guru $guru)
    {
        $nama = $guru->guruProfile->nama ?? "Nama Guru";

        try {
            DB::beginTransaction();

            // 1. Hapus data profile terkait (CHILD)
            if ($guru->guruProfile) {
                $guru->guruProfile->delete();
            }

            // 2. Hapus data guru (PARENT)
            $guru->delete();

            DB::commit();

            return redirect()
                ->route("guru.index")
                ->with(
                    "success",
                    "Data Guru **" . $nama . "** berhasil dihapus!",
                );
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with(
                "error",
                "Gagal menghapus data Guru. Detail: " . $e->getMessage(),
            );
        }
    }
}