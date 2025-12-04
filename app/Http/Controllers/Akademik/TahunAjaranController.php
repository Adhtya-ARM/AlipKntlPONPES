<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\TahunAjaran;
use App\Models\Akademik\Kelas;
use App\Models\User\SantriProfile;
use App\Models\User\SantriKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TahunAjaranController extends Controller
{
    public function index()
    {
        // Only show non-archived semesters in the main view
        $tahunAjarans = TahunAjaran::notArchived()->orderBy('created_at', 'desc')->get();
        return view('Akademik.TahunAjaran.index', compact('tahunAjarans'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string',
            'semester' => 'required|in:Ganjil,Genap',
            'jenjang' => 'required|in:SMP,SMA,Semua',
        ]);

        // Check if a non-archived semester with same label already exists
        $exists = TahunAjaran::notArchived()
            ->where('nama', $request->nama)
            ->where('semester', $request->semester)
            ->where('jenjang', $request->jenjang)
            ->exists();

        if ($exists) {
            return back()->withInput()->with('error', 'Tahun ajaran dengan nama, semester, dan jenjang yang sama sudah ada dan masih aktif. Arsipkan semester tersebut terlebih dahulu jika ingin membuat yang baru.');
        }

        TahunAjaran::create($request->all());

        return redirect()->route('manajemen-sekolah.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil ditambahkan.');
    }

    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        // Prevent updating archived semesters
        if ($tahunAjaran->isArchived()) {
            return back()->with('error', 'Tidak dapat mengubah semester yang sudah terarsip. Data arsip bersifat read-only.');
        }

        $request->validate([
            'nama' => 'required|string',
            'semester' => 'required|in:Ganjil,Genap',
            'jenjang' => 'required|in:SMP,SMA,Semua',
        ]);

        $tahunAjaran->update($request->all());

        return redirect()->route('manajemen-sekolah.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil diperbarui.');
    }

    public function destroy(TahunAjaran $tahunAjaran)
    {
        // Prevent deleting archived semesters
        if ($tahunAjaran->isArchived()) {
            return back()->with('error', 'Tidak dapat menghapus semester yang sudah terarsip. Data arsip bersifat permanen.');
        }

        // Check if used
        if ($tahunAjaran->is_active) {
            return back()->with('error', 'Tidak dapat menghapus tahun ajaran aktif.');
        }
        $tahunAjaran->delete();
        return redirect()->route('manajemen-sekolah.tahun-ajaran.index')->with('success', 'Tahun ajaran berhasil dihapus.');
    }

    public function activate(TahunAjaran $tahunAjaran)
    {
        // Prevent activating an already archived semester
        if ($tahunAjaran->isArchived()) {
            return back()->with('error', 'Tidak dapat mengaktifkan semester yang sudah terarsip. Semester terarsip bersifat read-only.');
        }

        DB::transaction(function () use ($tahunAjaran) {
            // Get currently active year
            $currentActive = TahunAjaran::where('is_active', true)->first();

            // Deactivate all
            TahunAjaran::query()->update(['is_active' => false]);
            
            // If there was an active year and it's not the one being activated
            if ($currentActive && $currentActive->id !== $tahunAjaran->id) {
                // Set status to Terarsip - marking it read-only
                $currentActive->update(['status' => 'Terarsip']);
            }

            // Activate the selected year
            $tahunAjaran->update([
                'is_active' => true,
                'status' => 'Aktif'
            ]);
        });

        return redirect()->route('manajemen-sekolah.tahun-ajaran.index')
            ->with('success', 'Tahun ajaran ' . $tahunAjaran->nama . ' ' . $tahunAjaran->semester . ' diaktifkan. Data semester sebelumnya telah diarsipkan.');
    }


    public function archive(TahunAjaran $tahunAjaran)
    {
        // Prevent archiving an active year
        if ($tahunAjaran->is_active) {
            return back()->with('error', 'Tidak dapat mengarsipkan tahun ajaran yang sedang aktif. Aktifkan tahun ajaran lain terlebih dahulu.');
        }

        if ($tahunAjaran->isArchived()) {
            return back()->with('error', 'Tahun ajaran sudah terarsip.');
        }

        DB::transaction(function () use ($tahunAjaran) {
            // Step 1: Mark current semester as archived (read-only)
            $tahunAjaran->update(['status' => 'Terarsip']);

            // Step 2: Create NEW semester with same label but new ID
            TahunAjaran::create([
                'nama' => $tahunAjaran->nama,
                'semester' => $tahunAjaran->semester,
                'jenjang' => $tahunAjaran->jenjang,
                'is_active' => false,
                'status' => 'Tidak Aktif', // New semester starts as inactive
            ]);
        });

        return redirect()->route('manajemen-sekolah.tahun-ajaran.index')
            ->with('success', "Semester {$tahunAjaran->nama} {$tahunAjaran->semester} telah diarsipkan. Semester baru dengan label yang sama telah dibuat dan siap untuk diaktifkan.");
    }


    private function createArchiveForYear($tahunAjaranId)
    {
        // Find all classes that have activity in this year
        $kelasIdsFromSantri = SantriKelas::where('tahun_ajaran_id', $tahunAjaranId)->pluck('kelas_id');
        $kelasIdsFromGuru = \App\Models\Akademik\GuruMapel::where('tahun_ajaran_id', $tahunAjaranId)->pluck('kelas_id');

        $allKelasIds = $kelasIdsFromSantri->merge($kelasIdsFromGuru)->unique()->filter();

        \Log::info("createArchiveForYear: year={$tahunAjaranId}, found_kelas_count=" . $allKelasIds->count());

        $arsipController = app(ArsipController::class);

        $created = 0;
        $errors = [];

        foreach ($allKelasIds as $kelasId) {
            // Check if already archived to avoid duplicates
            $exists = \App\Models\Akademik\Arsip::where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->exists();

            if ($exists) {
                continue;
            }

            try {
                $arsipController->createArchive($kelasId, $tahunAjaranId);
                $created++;
            } catch (\Exception $e) {
                \Log::error("Failed to archive class {$kelasId} for year {$tahunAjaranId}: " . $e->getMessage());
                $errors[] = "kelas {$kelasId}: " . $e->getMessage();
            }
        }

        return ['created' => $created, 'errors' => $errors, 'checked' => $allKelasIds->count()];
    }

    public function kenaikanKelasIndex()
    {
        $activeYear = TahunAjaran::active()->first();
        $allYears = TahunAjaran::orderBy('created_at', 'desc')->get();
        $kelas = Kelas::all(); // All classes (static definitions)

        return view('Akademik.TahunAjaran.kenaikan', compact('activeYear', 'allYears', 'kelas'));
    }

    public function processKenaikanKelas(Request $request)
    {
        $request->validate([
            'target_tahun_ajaran_id' => 'nullable|exists:tahun_ajarans,id',
            'source_kelas_id' => 'required|exists:kelas,id',
            'target_kelas_id' => 'required|exists:kelas,id',
            'santri_ids' => 'required|array',
            'santri_ids.*' => 'exists:santri_profile,id',
        ]);

        // If target year not provided, use active year
        $targetYearId = $request->target_tahun_ajaran_id ?? (TahunAjaran::active()->first()?->id);
        $targetKelasId = $request->target_kelas_id;
        $santriIds = $request->santri_ids;

        if (!$targetYearId) {
            return redirect()->back()->with('error', 'Tahun ajaran tujuan tidak ditemukan atau belum diaktifkan.');
        }

        DB::transaction(function () use ($targetYearId, $targetKelasId, $santriIds, $request) {
            $active = TahunAjaran::active()->first();

            // Archive previous year data (mark santri_kelas as Arsip for active year)
            if ($active) {
                SantriKelas::where('tahun_ajaran_id', $active->id)
                    ->whereIn('kelas_id', [$request->source_kelas_id])
                    ->update(['status' => 'Arsip']);

                // Optionally archive related GuruMapel, Penilaian, and Absensi by marking flags if columns exist.
                // We'll attempt safe updates if columns exist.
                try {
                    \DB::table('guru_mapel')->where('tahun_ajaran', $active->nama ?? $active->id)->update(['is_archived' => 1]);
                } catch (\Exception $e) {
                    // ignore if column/tables not present or format differs
                }
            }

            foreach ($santriIds as $santriId) {
                // Use updateOrCreate to handle both new assignments and updates to existing ones
                // This ensures if a student was already assigned to a wrong class for this year, it gets corrected
                SantriKelas::updateOrCreate(
                    [
                        'santri_profile_id' => $santriId,
                        'tahun_ajaran_id' => $targetYearId,
                    ],
                    [
                        'kelas_id' => $targetKelasId,
                        'status' => 'Aktif',
                    ]
                );
            }

            // Ensure promoted students start fresh in the target class/year:
            // - Remove any existing absensi/penilaian tied to the target year/class for these santri
            // - Remove santri_mapel assignments so they can be re-created for the new class/year
            try {
                // Clear absensi rows for those students in the target year & class
                \App\Models\Akademik\Absensi::whereIn('santri_profile_id', $santriIds)
                    ->where('tahun_ajaran_id', $targetYearId)
                    ->where('kelas_id', $targetKelasId)
                    ->delete();

                // Find guru_mapel ids for target year/class
                $guruMapelIds = \App\Models\Akademik\GuruMapel::where('tahun_ajaran_id', $targetYearId)
                    ->where('kelas_id', $targetKelasId)
                    ->pluck('id');

                if ($guruMapelIds->isNotEmpty()) {
                    \App\Models\Akademik\Penilaian::whereIn('guru_mapel_id', $guruMapelIds)
                        ->whereIn('santri_profile_id', $santriIds)
                        ->delete();
                }

                // Remove SantriMapel links so students have a clean slate in the new class
                \App\Models\Akademik\SantriMapel::whereIn('santri_profile_id', $santriIds)->delete();
            } catch (\Exception $e) {
                // If any table/column doesn't exist or deletion fails, ignore to avoid breaking the transaction
            }
        });

        return redirect()->back()->with('success', 'Santri berhasil dinaikkan kelasnya. Data lama dipindahkan ke arsip untuk tahun ajaran aktif.');
    }
    
    // Helper to get students in a class for a specific year (AJAX)
    public function getSantriByKelas(Request $request)
    {
        $kelasId = $request->kelas_id;
        $tahunAjaranId = $request->tahun_ajaran_id; // Optional, defaults to active

        if (!$tahunAjaranId) {
            $active = TahunAjaran::active()->first();
            $tahunAjaranId = $active ? $active->id : null;
        }

        if (!$tahunAjaranId) {
            return response()->json([]);
        }

        $santris = SantriProfile::with('santri:id,nisn')
            ->whereHas('riwayatKelas', function($q) use ($kelasId, $tahunAjaranId) {
                $q->where('kelas_id', $kelasId)
                  ->where('tahun_ajaran_id', $tahunAjaranId);
            })
            ->get(['id', 'nama', 'santri_id']);
        
        // Map to include nisn from relationship
        $result = $santris->map(function($santri) {
            return [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'nisn' => $santri->santri->nisn ?? '-'
            ];
        });

        return response()->json($result);
    }
}
