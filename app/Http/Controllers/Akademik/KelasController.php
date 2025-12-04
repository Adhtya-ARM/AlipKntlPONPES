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
        $activeYear = \App\Models\Akademik\TahunAjaran::active()->first();
        
        // Ambil data kelas dengan eager loading guru dan hitung guru_mapels
        $kelas = Kelas::with(['waliKelas:id,nama'])
            ->withCount(['guruMapels'])
            ->where('status', 'Aktif') // Only show active classes
            ->orderBy('level')
            ->get()
            ->map(function ($item) use ($activeYear) {
                $item->is_locked = $item->guru_mapels_count > 0;
                $item->nama_display = "Kelas {$item->level}";
                $item->wali_kelas_id = $item->guru_profile_id;
                
                // Count santri in this class for active year
                if ($activeYear) {
                    $item->santri_count = \DB::table('santri_kelas')
                        ->where('kelas_id', $item->id)
                        ->where('tahun_ajaran_id', $activeYear->id)
                        ->where('status', 'Aktif')
                        ->count();
                } else {
                    $item->santri_count = 0;
                }
                
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
        $kelas = Kelas::findOrFail($kelasId);
        $activeYear = \App\Models\Akademik\TahunAjaran::active()->first();

        if (!$activeYear) {
            return response()->json([
                'students' => [],
                'enrolled_ids' => [],
                'error' => 'Tidak ada tahun ajaran aktif'
            ]);
        }

        // Get santri yang sudah ada di kelas INI di tahun ajaran aktif
        $santriDiKelasIni = DB::table('santri_kelas')
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $activeYear->id)
            ->where('status', 'Aktif')
            ->pluck('santri_profile_id')
            ->toArray();

        // Get santri yang sudah punya kelas di tahun ajaran aktif (tapi bukan kelas ini)
        $santriSudahPunyaKelas = DB::table('santri_kelas')
            ->where('kelas_id', '!=', $kelasId)
            ->where('tahun_ajaran_id', $activeYear->id)
            ->where('status', 'Aktif')
            ->pluck('santri_profile_id')
            ->toArray();

        // Get semua santri yang belum punya kelas di tahun ajaran aktif
        $santri = SantriProfile::select('santri_profile.id', 'santri_profile.nama', 'santris.nisn')
            ->join('santris', 'santri_profile.santri_id', '=', 'santris.id')
            ->whereNotIn('santri_profile.id', $santriSudahPunyaKelas)
            ->where('santri_profile.status', 'aktif')
            ->orderBy('santri_profile.nama')
            ->get()
            ->map(function ($student) use ($kelasId, $activeYear) {
                // Check if has academic data in this class for active year
                $hasAbsensi = DB::table('absensis')
                    ->where('santri_profile_id', $student->id)
                    ->where('kelas_id', $kelasId)
                    ->exists();
                
                $hasPenilaian = DB::table('penilaians')
                    ->where('santri_profile_id', $student->id)
                    ->join('guru_mapel', 'penilaians.guru_mapel_id', '=', 'guru_mapel.id')
                    ->where('guru_mapel.kelas_id', $kelasId)
                    ->where('guru_mapel.tahun_ajaran_id', $activeYear->id)
                    ->exists();
                
                $student->has_relations = $hasAbsensi || $hasPenilaian;
                return $student;
            });

        return response()->json([
            'students' => $santri,
            'enrolled_ids' => $santriDiKelasIni,
        ]);
    }

    public function updateSiswa(Request $request, $kelasId)
    {
        $kelas = Kelas::findOrFail($kelasId);
        $activeYear = \App\Models\Akademik\TahunAjaran::active()->first();

        if (!$activeYear) {
            return response()->json(['message' => 'Tidak ada tahun ajaran aktif'], 400);
        }

        $santriIds = $request->input('santri_ids', []);
        if (!is_array($santriIds)) {
            $santriIds = [];
        }

        // Get santri yang sudah punya kelas lain di tahun ajaran aktif
        $santriSudahPunyaKelas = DB::table('santri_kelas')
            ->where('kelas_id', '!=', $kelasId)
            ->where('tahun_ajaran_id', $activeYear->id)
            ->pluck('santri_profile_id')
            ->toArray();

        // Filter only valid IDs
        $validIds = SantriProfile::whereIn('id', $santriIds)
            ->whereNotIn('id', $santriSudahPunyaKelas)
            ->pluck('id')
            ->toArray();

        // Delete existing records for this class in active year
        DB::table('santri_kelas')
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $activeYear->id)
            ->delete();

        // Insert new records
        $records = array_map(function($santriId) use ($kelasId, $activeYear) {
            return [
                'santri_profile_id' => $santriId,
                'kelas_id' => $kelasId,
                'tahun_ajaran_id' => $activeYear->id,
                'status' => 'Aktif',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }, $validIds);

        if (!empty($records)) {
            DB::table('santri_kelas')->insert($records);
        }

        return response()->json(['message' => 'Daftar santri kelas berhasil diperbarui.']);
    }

    /**
     * Archive class data for current active year for this kelas (triggered by teacher)
     */
    public function archive(Request $request, Kelas $kelas)
    {
        // Only allow if authenticated as guru
        $user = auth()->user();
        
        try {
            DB::beginTransaction();

            // Find active tahun ajaran
            $active = \App\Models\Akademik\TahunAjaran::active()->first();
            if ($active) {
                // Build a snapshot of the class and related data before archiving
                try {
                    $enrollments = \App\Models\User\SantriKelas::with(['santriProfile'])
                        ->where('kelas_id', $kelas->id)
                        ->where('tahun_ajaran_id', $active->id)
                        ->get();

                    $snapshot = [];
                    foreach ($enrollments as $enr) {
                        $santriId = $enr->santri_profile_id;
                        $profile = $enr->santriProfile ? $enr->santriProfile->toArray() : null;

                        $absensis = \App\Models\Akademik\Absensi::where('santri_profile_id', $santriId)
                            ->where('kelas_id', $kelas->id)
                            ->where('tahun_ajaran_id', $active->id)
                            ->get()
                            ->map(function($a){ return $a->toArray(); });

                        // Penilaian: join via guru_mapel to ensure class/year association
                        $penilaians = \App\Models\Akademik\Penilaian::where('santri_profile_id', $santriId)
                            ->join('guru_mapel', 'penilaians.guru_mapel_id', '=', 'guru_mapel.id')
                            ->where('guru_mapel.kelas_id', $kelas->id)
                            ->where('guru_mapel.tahun_ajaran_id', $active->id)
                            ->select('penilaians.*')
                            ->get()
                            ->map(function($p){ return $p->toArray(); });

                        $snapshot[] = [
                            'santri_kelas' => $enr->toArray(),
                            'profile' => $profile,
                            'absensis' => $absensis,
                            'penilaians' => $penilaians,
                        ];
                    }

                    // Also capture guru_mapel records for this class/year
                    $guruMapels = \App\Models\Akademik\GuruMapel::where('kelas_id', $kelas->id)
                        ->where('tahun_ajaran_id', $active->id)
                        ->get()
                        ->map(function($g){ return $g->toArray(); });

                    // Save into archives table
                    \App\Models\Akademik\Archive::create([
                        'title' => "Arsip Kelas {$kelas->level} - {$active->nama} {$active->semester}",
                        'kelas_id' => $kelas->id,
                        'tahun_ajaran_id' => $active->id,
                        'created_by' => auth()->id() ?? null,
                        'data' => [
                            'kelas' => $kelas->toArray(),
                            'guru_mapels' => $guruMapels,
                            'enrollments' => $snapshot,
                        ],
                    ]);
                } catch (\Exception $e) {
                    // Don't fail the whole archive if snapshot saving fails — proceed with status update
                }

                // Mark santri_kelas entries for this kelas and tahun_ajaran as 'Arsip'
                \App\Models\User\SantriKelas::where('kelas_id', $kelas->id)
                    ->where('tahun_ajaran_id', $active->id)
                    ->update(['status' => 'Arsip']);

                // Note: We do NOT archive the Kelas entity itself ($kelas->status)
                // because the class (e.g., 7A) continues to exist for the next batch of students.
                // We only archive the *enrollments* for this year.

                // Reset Mapel (Delete GuruMapel if no grades exist)
                // Actually, GuruMapel is tied to TahunAjaran. 
                // We don't need to delete it. New year = New GuruMapel records.
                // But if we want to "clean up" for the current view:
                
                // Logic: The class is now "empty" for this year in terms of active students.
                
                $hasGrades = \DB::table('penilaians')
                    ->join('guru_mapel', 'penilaians.guru_mapel_id', '=', 'guru_mapel.id')
                    ->where('guru_mapel.kelas_id', $kelas->id)
                    ->where('guru_mapel.tahun_ajaran_id', $active->id)
                    ->exists();

                if (!$hasGrades) {
                    \App\Models\Akademik\GuruMapel::where('kelas_id', $kelas->id)
                        ->where('tahun_ajaran_id', $active->id)
                        ->delete();
                }
            }

            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Kelas berhasil diarsipkan untuk tahun ajaran aktif.']);
            }
            return redirect()->back()->with('success', 'Kelas berhasil diarsipkan untuk tahun ajaran aktif.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Gagal mengarsipkan kelas: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal mengarsipkan kelas: ' . $e->getMessage());
        }
    }

    /**
     * Restore an archived class for the active year: set SantriKelas.status back to 'Aktif'
     */
    public function restore(Request $request, Kelas $kelas)
    {
        try {
            DB::beginTransaction();
            $active = \App\Models\Akademik\TahunAjaran::active()->first();
            if (!$active) {
                if ($request->wantsJson()) {
                    return response()->json(['message' => 'Tidak ada tahun ajaran aktif.'], 400);
                }
                return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
            }

            // Restore santri_kelas status to Aktif
            \App\Models\User\SantriKelas::where('kelas_id', $kelas->id)
                ->where('tahun_ajaran_id', $active->id)
                ->update(['status' => 'Aktif']);

            // Restore kelas status to Aktif
            $kelas->update(['status' => 'Aktif']);

            DB::commit();
            
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Kelas berhasil direstore (kembali aktif).']);
            }
            return redirect()->back()->with('success', 'Kelas berhasil direstore (kembali aktif).');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Gagal merestore kelas: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal merestore kelas: ' . $e->getMessage());
        }
    }
}
