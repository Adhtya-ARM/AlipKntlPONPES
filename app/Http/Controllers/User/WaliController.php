<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

use App\Models\User\SantriProfile;
use App\Models\User\Wali;
use App\Models\User\WaliProfile;

class WaliController extends Controller
{
    public function index(Request $request)
    {
        // 1. Mulai Query Builder
        $query = Wali::with(["waliProfile" => function($q) {
            $q->withCount('santriProfiles');
        }]);

        // 2. Logika Pencarian
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('username', 'like', "%{$search}%")
                  ->orWhereHas('waliProfile', function($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        // 3. Eksekusi Pagination
        $walis = $query->paginate(10);

        // 4. Kirim ke view
        return view("User.Management.Wali.index", compact("walis"));
    }

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
            "username" => "required|string|unique:wali,username|unique:guru,username|max:50",
            "password" => "required|string|min:8", // Hapus confirmed

            // Data Profile Wali
            "nama" => "required|string|max:255",
            "alamat" => "nullable|string|max:255",
            "no_hp" => "nullable|string|max:20",
        ]);

        // 2. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // A. Simpan data ke tabel Wali
            $wali = Wali::create([
                "username" => $validatedData["username"],
                "password" => Hash::make($validatedData["password"]),
            ]);

            // B. Simpan data ke tabel WaliProfile
            $wali->waliProfile()->create([
                "nama" => $validatedData["nama"],
                "alamat" => $validatedData["alamat"] ?? null,
                "no_hp" => $validatedData["no_hp"] ?? null,
            ]);

            DB::commit();

            return response()->json(['message' => "Data Wali **" . $validatedData["nama"] . "** berhasil ditambahkan!"], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal menyimpan data Wali. Detail: " . $e->getMessage()], 500);
        }
    }

    /**
     * Perbarui data wali di database (UPDATE - Store).
     */
    public function update(Request $request, Wali $wali)
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
                // FIX: Tidak ada validasi unique saat update
            ],
            "password" => "nullable|string|min:8", // Hapus confirmed

            // Data Profile Wali
            "nama" => "required|string|max:255",
            "alamat" => "nullable|string|max:255",
            "no_hp" => "nullable|string|max:20",
        ]);

        // 2. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // A. Update data tabel Wali
            $waliData = [
                "username" => $validatedData["username"],
            ];

            if (!empty($validatedData["password"])) {
                $waliData["password"] = Hash::make($validatedData["password"]);
            }

            $wali->update($waliData);

            // B. Update data tabel WaliProfile
            $wali->waliProfile()->update([
                "nama" => $validatedData["nama"],
                "alamat" => $validatedData["alamat"] ?? null,
                "no_hp" => $validatedData["no_hp"] ?? null,
            ]);

            DB::commit();

            return response()->json(['message' => "Data Wali **" . $validatedData["nama"] . "** berhasil diperbarui!"], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal memperbarui data Wali. Detail: " . $e->getMessage()], 500);
        }
    }

    /**
     * Hapus data wali dari database (DELETE).
     */
    public function destroy(Wali $wali)
    {
        // Cek Permission Waka/Kepsek
        $currentUser = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        $jabatan = strtolower($currentUser->guruProfile->jabatan ?? '');
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            abort(403, 'Akses Ditolak. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola data ini.');
        }
        
        // Cek Relasi
        $profile = $wali->waliProfile;
        if ($profile && $profile->santriProfiles()->exists()) {
            return response()->json(['message' => 'Gagal menghapus! Wali ini masih memiliki santri terkait.'], 422);
        }

        $nama = $profile->nama ?? "Wali";

        try {
            DB::beginTransaction();

            // 1. Hapus data profile terkait
            if ($wali->waliProfile) {
                $wali->waliProfile->delete();
            }

            // 2. Hapus data wali
            $wali->delete();

            DB::commit();

            return response()->json(['message' => "Data Wali **" . $nama . "** berhasil dihapus!"], 200);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => "Gagal menghapus data Wali. Detail: " . $e->getMessage()], 500);
        }
    }
}
