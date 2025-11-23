<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Models\Akademik\Kelas;
use App\Models\User\Santri;
use App\Models\User\SantriProfile;
use App\Models\User\WaliProfile;
use App\Models\User\SantriKelas;

class SantriController extends Controller
{
    private const VALID_STATUSES = ["aktif", "non-aktif", "lulus", "dropout"]; 
    
// ---------------------------------------------------------------------
// INDEX (Menampilkan Daftar Santri)
// ---------------------------------------------------------------------
public function index(Request $request)
{
    // 1. Mulai Query Builder
    $query = Santri::with([
        "santriprofile.waliProfile",
        "santriprofile.santriKelas.kelas",
    ]);

    // 2. Logika Filter Kelas
    if ($request->filled('filter_kelas')) {
        $query->whereHas('santriprofile.santriKelas', function($q) use ($request) {
            $q->where('kelas_id', $request->filter_kelas);
        });
    }

    // 3. Logika Pencarian
    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('nisn', 'like', "%{$search}%")
              ->orWhereHas('santriprofile', function($q2) use ($search) {
                  $q2->where('nama', 'like', "%{$search}%");
              });
        });
    }

    // 4. Eksekusi Pagination
    $santris = $query->paginate(10);

    $statuses = self::VALID_STATUSES;
    
    // Data Dropdown Wali
    $walis = WaliProfile::select("id", "nama")
        ->orderBy('nama', 'asc')
        ->get()
        ->map(fn($w) => ["id" => $w->id, "nama" => (string)$w->nama]);

    // Data Dropdown Kelas
    $kelas = Kelas::select("id", "level")
        ->orderBy('level', 'asc')
        ->get()
        ->map(fn($k) => ["id" => $k->id, "nama" => (string)$k->level]);

    return view("User.Santri.index", compact("santris", "statuses", "walis", "kelas"));
}

// ---------------------------------------------------------------------
// STORE (Simpan Data Baru)
// ---------------------------------------------------------------------
    /**
     * 🔹 Simpan santri baru
     */
    public function store(Request $request)
    {
        // Cek Permission Waka/Kepsek
        $currentUser = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        $jabatan = strtolower($currentUser->guruProfile->jabatan ?? '');
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            abort(403, 'Akses Ditolak. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola data ini.');
        }

        // 1. Validasi Input
        $validatedData = $request->validate([
            "username" => "required|string|unique:santris,username|max:50",
            "password" => "required|string|min:8|confirmed",
            "nisn" => "required|string|unique:santris,nisn|max:20",

            // Data Profile Santri
            "nama" => "required|string|max:255",
            "alamat" => "nullable|string|max:255",
            "no_hp" => "nullable|string|max:20",
            "wali_profile_id" => "nullable|exists:wali_profile,id",
        ]);

        // 2. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // A. Simpan data ke tabel Santri
            $santri = Santri::create([
                "username" => $validatedData["username"],
                "password" => Hash::make($validatedData["password"]),
                "nisn" => $validatedData["nisn"],
            ]);

            // B. Simpan data ke tabel SantriProfile
            $santri->santriProfile()->create([
                "nama" => $validatedData["nama"],
                "alamat" => $validatedData["alamat"] ?? null,
                "no_hp" => $validatedData["no_hp"] ?? null,
                "wali_profile_id" => $validatedData["wali_profile_id"] ?? null,
            ]);

            DB::commit();

            return redirect()
                ->route("santri.index")
                ->with("success", "Data Santri **" . $validatedData["nama"] . "** berhasil ditambahkan!");
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with("error", "Gagal menyimpan data Santri. Detail: " . $e->getMessage());
        }
    }

// ---------------------------------------------------------------------
// UPDATE (Edit Data)
// ---------------------------------------------------------------------
    /**
     * Perbarui data santri di database (UPDATE - Store).
     */
    /**
     * Perbarui data santri di database (UPDATE - Store).
     */
    public function update(Request $request, Santri $santri)
    {
        // Cek Permission Waka/Kepsek
        $currentUser = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        $jabatan = strtolower($currentUser->guruProfile->jabatan ?? '');
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            abort(403, 'Akses Ditolak. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola data ini.');
        }

        // 1. Validasi Input
        $validatedData = $request->validate([
            "username" => [
                "required",
                "string",
                "max:50",
                Rule::unique('santris')->ignore($santri->id),
            ],
            "password" => "nullable|string|min:8|confirmed",
            "nisn" => [
                "required",
                "string",
                "max:20",
                Rule::unique('santris')->ignore($santri->id),
            ],

            // Data Profile Santri
            "nama" => "required|string|max:255",
            "alamat" => "nullable|string|max:255",
            "no_hp" => "nullable|string|max:20",
            "wali_profile_id" => "nullable|exists:wali_profile,id",
            "status" => "required|in:aktif,non-aktif,lulus,dropout",
            "kelas_id" => "nullable|exists:kelas,id",
        ]);

        // 2. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // A. Update data tabel Santri
            $santriData = [
                "username" => $validatedData["username"],
                "nisn" => $validatedData["nisn"],
            ];

            if (!empty($validatedData["password"])) {
                $santriData["password"] = Hash::make($validatedData["password"]);
            }

            $santri->update($santriData);

            // B. Update data tabel SantriProfile
            $santri->santriProfile()->update([
                "nama" => $validatedData["nama"],
                "alamat" => $validatedData["alamat"] ?? null,
                "no_hp" => $validatedData["no_hp"] ?? null,
                "wali_profile_id" => $validatedData["wali_profile_id"] ?? null,
                "status" => $validatedData["status"],
            ]);

            // C. Update data Kelas (SantriKelas)
            if (!empty($validatedData["kelas_id"])) {
                // Cek apakah sudah ada data kelas
                $santriKelas = $santri->santriProfile->santriKelas;
                
                if ($santriKelas) {
                    // Update jika ada
                    $santriKelas->update([
                        'kelas_id' => $validatedData["kelas_id"]
                    ]);
                } else {
                    // Buat baru jika belum ada
                    $santri->santriProfile->santriKelas()->create([
                        'kelas_id' => $validatedData["kelas_id"]
                    ]);
                }
            } else {
                // Jika kelas_id kosong/null, hapus relasi kelas jika ada (opsional, tergantung kebutuhan)
                // $santri->santriProfile->santriKelas()->delete();
            }

            DB::commit();

            return redirect()
                ->route("santri.index")
                ->with("success", "Data Santri **" . $validatedData["nama"] . "** berhasil diperbarui!");
        } catch (\Exception $e) {
            DB::rollback();
            return back()
                ->withInput()
                ->with("error", "Gagal memperbarui data Santri. Detail: " . $e->getMessage());
        }
    }

// ---------------------------------------------------------------------
// DESTROY (Hapus Data)
// ---------------------------------------------------------------------
    /**
     * 🔹 Hapus santri
     */
    public function destroy(Santri $santri)
    {
        // Cek Permission Waka/Kepsek
        $currentUser = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        $jabatan = strtolower($currentUser->guruProfile->jabatan ?? '');
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            abort(403, 'Akses Ditolak. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola data ini.');
        }

        DB::beginTransaction();
        try {
            $profile = $santri->santriprofile;
            $nama = $profile->nama ?? "Santri";

            if ($profile) {
                $profile->santriKelas()->delete(); 
                $profile->delete();
            }

            $santri->delete(); 
            DB::commit();

            return redirect()
                ->route("santri.index")
                ->with("success", "Data santri {$nama} berhasil dihapus!");
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with(
                "error",
                "Gagal menghapus data santri. " . $e->getMessage(),
            );
        }
    }
}