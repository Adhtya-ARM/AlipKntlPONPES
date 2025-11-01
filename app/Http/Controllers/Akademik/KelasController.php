<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Kelas;
use App\Models\User\SantriProfile;
use App\Models\User\GuruProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    /**
     * 🔹 Tampilkan daftar kelas dengan wali dan hitungan santri/guru_mapel
     * Eager loading mencegah N+1 query
     */
    public function index()
    {
        $kelas = Kelas::with([
                'waliKelas:id,nama',
                'santriProfiles:id,nama', // eager load nama santri juga
            ])
            ->withCount(['santriProfiles', 'guruMapels'])
            ->orderBy('nama_kelas')
            ->get()
            ->map(function ($item) {
                $item->is_locked = $item->guru_mapels_count > 0; // flag edit lock
                return $item;
            });

        $guruProfiles = GuruProfile::select('id', 'nama')
            ->orderBy('nama')
            ->get();

        return view('Akademik.Kelas.index', [
            'kelas' => $kelas ?? collect(),
            'guruProfiles' => $guruProfiles ?? collect(),
        ]);
    }

    /**
     * 🔹 Tambah kelas baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|string|max:100',
            'wali_kelas_id' => 'nullable|exists:guru_profile,id',
        ]);

        Kelas::create($validated);

        return response()->json(['message' => 'Kelas berhasil ditambahkan.'], 201);
    }

    /**
     * 🔹 Update data kelas
     */
     public function update(Request $request, Kelas $kelas)
     {
         $validated = $request->validate([
             'nama_kelas' => 'required|string|max:100',
             'wali_kelas_id' => 'nullable|exists:guru_profile,id', // ✅ bukan guru_profiles
         ]);
     
         if ($kelas->guruMapels()->exists()) {
             // Preserve nama_kelas but only update wali_kelas_id when locked
             $kelas->update([
                 'nama_kelas' => $kelas->nama_kelas, // Keep existing name
                 'wali_kelas_id' => $validated['wali_kelas_id']
             ]);
             return response()->json(['message' => 'Wali kelas berhasil diperbarui.']);
         }
     
         $kelas->update($validated);
         return response()->json(['message' => 'Kelas berhasil diperbarui.']);
     }
     
    /**
     * 🔹 Hapus kelas
     */
    public function destroy(Kelas $kelas)
    {
        if ($kelas->guruMapels()->exists()) {
            return response()->json([
                'message' => 'Kelas ini sudah digunakan pada data mapel dan tidak dapat dihapus.'
            ], 422);
        }

        $kelas->delete();

        return response()->json(['message' => 'Kelas berhasil dihapus.']);
    }

    /**
     * 🔹 Ambil daftar santri (eager load + filter)
     */
    public function getSiswa($kelasId)
    {
        $kelas = Kelas::with('santriProfiles:id,nama')->findOrFail($kelasId);

        // Santri yang sudah punya kelas lain
        $santriSudahPunyaKelas = DB::table('santri_kelas')
            ->where('kelas_id', '!=', $kelasId)
            ->pluck('santri_profile_id')
            ->toArray();

        // Ambil semua santri yang belum punya kelas (kecuali yang sudah di kelas ini)
        $santri = SantriProfile::select('id', 'nama')
            ->whereNotIn('id', $santriSudahPunyaKelas)
            ->orderBy('nama')
            ->get();

        // Santri yang sudah termasuk di kelas ini
        $terpilih = $kelas->santriProfiles->pluck('id')->toArray();

        // Santri detail untuk modal "Detail"
        $terpilihData = $kelas->santriProfiles->map(function ($s) {
            return ['id' => $s->id, 'nama' => $s->nama];
        });

        return response()->json([
            'santri' => $santri,
            'terpilih' => $terpilih,
            'terpilih_data' => $terpilihData,
        ]);
    }

    /**
     * 🔹 Simpan daftar santri kelas (sinkronisasi pivot)
     */
     public function updateSiswa(Request $request, $kelasId)
     {
         $kelas = Kelas::findOrFail($kelasId);
     
         // 🚫 Jika kelas sudah dipakai di data mapel, jangan izinkan ubah santri
         if ($kelas->guruMapels()->exists()) {
             return response()->json([
                 'message' => 'Kelas ini sudah digunakan pada data mapel dan tidak dapat diubah daftar santrinya.'
             ], 422);
         }
     
         $santriIds = $request->input('santri', []);
         if (!is_array($santriIds)) {
             $santriIds = [];
         }
     
         // Filter santri yang sudah di kelas lain
         $santriSudahPunyaKelas = DB::table('santri_kelas')
             ->where('kelas_id', '!=', $kelasId)
             ->pluck('santri_profile_id')
             ->toArray();
     
         $validIds = SantriProfile::whereIn('id', $santriIds)
             ->whereNotIn('id', $santriSudahPunyaKelas)
             ->pluck('id')
             ->toArray();
     
         $kelas->santriProfiles()->sync($validIds);
     
         return response()->json(['message' => 'Daftar santri kelas berhasil diperbarui.']);
     }
}
