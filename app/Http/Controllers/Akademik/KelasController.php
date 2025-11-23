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
     * Display kelas management page
     */
    public function index()
    {
        $kelas = Kelas::with(['waliKelas:id,nama'])
            ->withCount(['santriProfile', 'guruMapels'])
            ->orderBy('level')
            ->orderBy('nama_unik')
            ->get()
            ->map(function ($item) {
                $item->is_locked = $item->guru_mapels_count > 0;
                $item->nama_display = "Kelas {$item->level} {$item->nama_unik}";
                return $item;
            });

        // Summary by jurusan
        $summary = $kelas->groupBy(function($item) {
            return $item->nama_unik; // Group by jurusan (TITL, TKR, etc)
        })->map(function($group) {
            return $group->count();
        });

        $guruList = GuruProfile::select('id', 'nama')
            ->orderBy('nama')
            ->get();

        return view('Akademik.Kelas.index', [
            'kelas' => $kelas,
            'guruList' => $guruList,
            'summary' => $summary,
        ]);
    }

    /**
     * Store new kelas
     */
    /**
     * Store new kelas
     */
    public function store(Request $request)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'level' => 'required|integer|in:7,8,9,10,11,12',
            'nama_unik' => 'required|string|max:50',
            'wali_kelas_id' => 'nullable|exists:guru_profile,id',
        ]);

        Kelas::create($validated);

        return response()->json(['message' => 'Kelas berhasil ditambahkan.'], 201);
    }

    /**
     * Update kelas
     */
    public function update(Request $request, Kelas $kelas)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'level' => 'required|integer|in:7,8,9,10,11,12',
            'nama_unik' => 'required|string|max:50',
            'wali_kelas_id' => 'nullable|exists:guru_profile,id',
        ]);

        // Restriction removed to allow fixing typos in class names/levels
        // if ($kelas->guruMapels()->exists()) { ... }

        \Illuminate\Support\Facades\Log::info('Updating Kelas', ['id' => $kelas->id, 'request' => $request->all(), 'validated' => $validated]);

        $updated = $kelas->update($validated);
        
        \Illuminate\Support\Facades\Log::info('Kelas Updated', ['updated' => $updated, 'fresh' => $kelas->fresh()]);

        return response()->json([
            'message' => 'Kelas berhasil diperbarui.',
            'debug_validated' => $validated,
            'debug_fresh' => $kelas->fresh()
        ]);
    }

    /**
     * Delete kelas
     */
    public function destroy(Kelas $kelas)
    {
        $this->authorizeManagement();

        if ($kelas->guruMapels()->exists()) {
            return response()->json([
                'message' => 'Kelas ini sudah digunakan pada guru mapel dan tidak dapat dihapus.'
            ], 422);
        }

        if ($kelas->santriProfile()->exists()) {
            return response()->json([
                'message' => 'Kelas ini masih memiliki siswa dan tidak dapat dihapus.'
            ], 422);
        }

        $kelas->delete();

        return response()->json(['message' => 'Kelas berhasil dihapus.']);
    }

    /**
     * Helper to authorize management actions
     */
    private function authorizeManagement()
    {
        $user = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        if (!$user || !$user->guruProfile) {
            abort(403, 'Unauthorized');
        }

        $jabatan = strtolower($user->guruProfile->jabatan);
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            abort(403, 'Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola kelas.');
        }
    }

    /**
     * Get santri for a kelas
     */
    public function getSiswa($kelasId)
    {
        $kelas = Kelas::with('santriProfile:id,nama')->findOrFail($kelasId);

        $santriSudahPunyaKelas = DB::table('santri_kelas')
            ->where('kelas_id', '!=', $kelasId)
            ->pluck('santri_profile_id')
            ->toArray();

        $santri = SantriProfile::select('santri_profile.id', 'santri_profile.nama', 'santris.nisn')
            ->join('santris', 'santri_profile.santri_id', '=', 'santris.id')
            ->whereNotIn('santri_profile.id', $santriSudahPunyaKelas)
            ->where('santri_profile.status', 'aktif')
            ->orderBy('santri_profile.nama')
            ->get();

        $terpilih = $kelas->santriProfile->pluck('id')->toArray();

        return response()->json([
            'students' => $santri,
            'enrolled_ids' => $terpilih,
        ]);
    }

    /**
     * Update santri in kelas
     */
    public function updateSiswa(Request $request, $kelasId)
    {
        $kelas = Kelas::findOrFail($kelasId);

        // Validasi input
        $santriIds = $request->input('santri_ids', []);
        if (!is_array($santriIds)) {
            $santriIds = [];
        }

        // Pastikan santri yang dipilih valid dan tidak sedang di kelas lain (double check)
        $santriSudahPunyaKelas = DB::table('santri_kelas')
            ->where('kelas_id', '!=', $kelasId)
            ->pluck('santri_profile_id')
            ->toArray();

        $validIds = SantriProfile::whereIn('id', $santriIds)
            ->whereNotIn('id', $santriSudahPunyaKelas)
            ->pluck('id')
            ->toArray();

        // Sync data siswa ke kelas (update tabel pivot santri_kelas)
        // Menggunakan sync akan menghapus yang tidak ada di array dan menambahkan yang baru
        $kelas->santriProfile()->sync($validIds);

        return response()->json(['message' => 'Daftar santri kelas berhasil diperbarui.']);
    }
}
