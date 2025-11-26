<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Kelas;
use App\Models\User\SantriProfile;
use App\Models\User\GuruProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with(['waliKelas:id,nama'])
            ->withCount(['santriProfile', 'guruMapels'])
            ->orderBy('level')
            ->get()
            ->map(function ($item) {
                $item->is_locked = $item->guru_mapels_count > 0;
                $item->nama_display = "Kelas {$item->level}";
                $item->wali_kelas_id = $item->guru_profile_id;
                return $item;
            });

        $summary = $kelas->groupBy('level')->map(function($group) {
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

    public function store(Request $request)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'level' => 'required|integer|in:7,8,9,10,11,12',
            'guru_profile_id' => 'nullable|exists:guru_profile,id',
        ]);

        Kelas::create($validated);

        return response()->json(['message' => 'Kelas berhasil ditambahkan.'], 201);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $this->authorizeManagement();

        $validated = $request->validate([
            'level' => 'nullable|integer|in:7,8,9,10,11,12',
            'guru_profile_id' => 'nullable|exists:guru_profile,id',
        ]);
    
        if (isset($validated['level'])) {
            $kelas->level = $validated['level'];
        }
        if (array_key_exists('guru_profile_id', $validated)) {
            $kelas->guru_profile_id = $validated['guru_profile_id'];
            
        }
        
        $kelas->save();
        $kelas = $kelas->fresh(['waliKelas:id,nama']);
        $kelas->wali_kelas_id = $kelas->guru_profile_id;
    
        return response()->json([
            'message' => 'Kelas berhasil diperbarui.',
            'kelas' => $kelas
        ]);
    }

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

    private function authorizeManagement()
    {
        $user = Auth::guard('guru')->user();
        if (!$user || !$user->guruProfile) {
            abort(403, 'Unauthorized');
        }

        $jabatan = strtolower($user->guruProfile->jabatan);
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            abort(403, 'Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola kelas.');
        }
    }

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
            ->get()
            ->map(function ($student) use ($kelasId) {
                $hasAbsensi = DB::table('absensis')
                    ->where('santri_profile_id', $student->id)
                    ->where('kelas_id', $kelasId)
                    ->exists();
                
                $hasPenilaian = DB::table('penilaians')
                    ->where('santri_profile_id', $student->id)
                    ->exists();
                
                $student->has_relations = $hasAbsensi || $hasPenilaian;
                return $student;
            });

        $terpilih = $kelas->santriProfile->pluck('id')->toArray();

        return response()->json([
            'students' => $santri,
            'enrolled_ids' => $terpilih,
        ]);
    }

    public function updateSiswa(Request $request, $kelasId)
    {
        $kelas = Kelas::findOrFail($kelasId);

        $santriIds = $request->input('santri_ids', []);
        if (!is_array($santriIds)) {
            $santriIds = [];
        }

        $santriSudahPunyaKelas = DB::table('santri_kelas')
            ->where('kelas_id', '!=', $kelasId)
            ->pluck('santri_profile_id')
            ->toArray();

        $validIds = SantriProfile::whereIn('id', $santriIds)
            ->whereNotIn('id', $santriSudahPunyaKelas)
            ->pluck('id')
            ->toArray();

        $kelas->santriProfile()->sync($validIds);

        return response()->json(['message' => 'Daftar santri kelas berhasil diperbarui.']);
    }
}
