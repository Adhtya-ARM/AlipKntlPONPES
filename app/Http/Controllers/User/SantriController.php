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
        "santriprofile" => function($q) {
            $q->with(['waliProfile', 'santriKelas.kelas', 'kelasAktif.kelas'])
              ->withCount(['absensis', 'penilaians']);
        }
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

    return view("User.Management.Santri.index", compact("santris", "statuses", "walis", "kelas"));
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

        // 1. Validasi Input (REMOVE kelas_id validation)
        $validatedData = $request->validate([
            "username" => "required|string|unique:santris,username|max:50",
            "password" => "required|string|min:8",
            "nisn" => "required|string|unique:santris,nisn|max:20",

            // Data Profile Santri
            "nama" => "required|string|max:255",
            "jenjang" => "required|in:SMP,SMA",
            "alamat" => "nullable|string|max:255",
            "no_hp" => "nullable|string|max:20",
            "wali_profile_id" => "nullable|exists:wali_profile,id",
            // REMOVED: "kelas_id" validation
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
            $profile = $santri->santriProfile()->create([
                "nama" => $validatedData["nama"],
                "jenjang" => $validatedData["jenjang"],
                "alamat" => $validatedData["alamat"] ?? null,
                "no_hp" => $validatedData["no_hp"] ?? null,
                "wali_profile_id" => $validatedData["wali_profile_id"] ?? null,
                "status" => 'aktif', // Default active
            ]);

            // C. REMOVED: Auto-assign class logic
            // Kelas will be assigned separately via "Kenaikan Kelas" or manual class assignment

            DB::commit();

            return response()->json(['message' => "Data Santri **" . $validatedData["nama"] . "** berhasil ditambahkan! Silakan assign kelas melalui menu Kenaikan Kelas."], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal menyimpan data Santri. Detail: " . $e->getMessage()], 500);
        }
    }

// ---------------------------------------------------------------------
// UPDATE (Edit Data)
// ---------------------------------------------------------------------
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
            "password" => "nullable|string|min:8",
            "nisn" => [
                "required",
                "string",
                "max:20",
                Rule::unique('santris')->ignore($santri->id),
            ],

            // Data Profile Santri
            "nama" => "required|string|max:255",
            "jenjang" => "required|in:SMP,SMA",
            "alamat" => "nullable|string|max:255",
            "no_hp" => "nullable|string|max:20",
            "wali_profile_id" => "nullable|exists:wali_profile,id",
            "status" => "required|in:aktif,non-aktif,lulus,dropout",
            "kelas_id" => "nullable|exists:kelas,id", // Restored validation
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
                "jenjang" => $validatedData["jenjang"],
                "alamat" => $validatedData["alamat"] ?? null,
                "no_hp" => $validatedData["no_hp"] ?? null,
                "wali_profile_id" => $validatedData["wali_profile_id"] ?? null,
                "status" => $validatedData["status"],
            ]);

            // C. Update Kelas (Manual Move) - Restored Logic
            if ($request->filled('kelas_id')) {
                $activeYear = \App\Models\Akademik\TahunAjaran::where('is_active', true)->first();
                if ($activeYear) {
                    \App\Models\User\SantriKelas::updateOrCreate(
                        [
                            'santri_profile_id' => $santri->santriProfile->id, 
                            'tahun_ajaran_id' => $activeYear->id
                        ],
                        [
                            'kelas_id' => $validatedData['kelas_id'], 
                            'status' => 'aktif'
                        ]
                    );
                }
            }

            DB::commit();

            return response()->json(['message' => "Data Santri **" . $validatedData["nama"] . "** berhasil diperbarui!"], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal memperbarui data Santri. Detail: " . $e->getMessage()], 500);
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

        // Cek Relasi
        $profile = $santri->santriprofile;
        if ($profile && ($profile->absensis()->exists() || $profile->penilaians()->exists())) {
            return response()->json(['message' => 'Gagal menghapus! Santri memiliki data absensi atau penilaian terkait.'], 422);
        }

        DB::beginTransaction();
        try {
            $nama = $profile->nama ?? "Santri";

            if ($profile) {
                $profile->santriKelas()->delete(); 
                $profile->delete();
            }

            $santri->delete(); 
            DB::commit();

            return response()->json(['message' => "Data santri {$nama} berhasil dihapus!"], 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => "Gagal menghapus data santri. " . $e->getMessage()], 500);
        }
    }
}