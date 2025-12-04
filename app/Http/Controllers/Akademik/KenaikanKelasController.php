<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\TahunAjaran;
use App\Models\User\SantriProfile;
use App\Models\User\SantriKelas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KenaikanKelasController extends Controller
{
    public function index()
    {
        $activeYear = TahunAjaran::where('is_active', true)->first();
        $allYears = TahunAjaran::orderBy('created_at', 'desc')->get();
        $kelas = Kelas::where('status', 'Aktif')->orderBy('level')->get();
        
        return view('Akademik.TahunAjaran.kenaikan', compact('activeYear', 'allYears', 'kelas'));
    }

    public function getSantri(Request $request)
    {
        $kelasId = $request->kelas_id;
        $tahunAjaranId = $request->tahun_ajaran_id;

        Log::info('getSantri called', [
            'kelas_id' => $kelasId,
            'tahun_ajaran_id' => $tahunAjaranId,
            'all_params' => $request->all()
        ]);

        // Validate kelas_id
        if (!$kelasId) {
            Log::warning('No kelas_id provided');
            return response()->json([]);
        }

        // Get or find tahun ajaran
        if (!$tahunAjaranId) {
            $active = TahunAjaran::where('is_active', true)->first();
            $tahunAjaranId = $active ? $active->id : null;
            Log::info('Using active year', ['tahun_ajaran_id' => $tahunAjaranId]);
        }

        if (!$tahunAjaranId) {
            Log::warning('No tahun ajaran found');
            return response()->json([]);
        }

        // Check if there are any santri_kelas records with these criteria
        $santriKelasCount = SantriKelas::where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('status', 'Aktif')
            ->count();
        
        Log::info('SantriKelas records found', ['count' => $santriKelasCount]);

        // Get santri profiles
        $santris = SantriProfile::with('santri:id,nisn')
            ->whereHas('riwayatKelas', function($q) use ($kelasId, $tahunAjaranId) {
                $q->where('kelas_id', $kelasId)
                  ->where('tahun_ajaran_id', $tahunAjaranId)
                  ->where('status', 'Aktif');
            })
            ->get(['id', 'nama', 'santri_id']);
        
        Log::info('Santris found', [
            'count' => $santris->count(),
            'sample_ids' => $santris->take(3)->pluck('id')->toArray()
        ]);
        
        $result = $santris->map(function($santri) {
            return [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'nisn' => $santri->santri->nisn ?? '-'
            ];
        });

        Log::info('Result prepared', ['result_count' => $result->count()]);

        return response()->json($result)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
            ->header('Pragma', 'no-cache');
    }

    public function process(Request $request)
    {
        $request->validate([
            'source_kelas_id' => 'required|exists:kelas,id',
            'target_tahun_ajaran_id' => 'required|exists:tahun_ajarans,id',
            'santri_ids' => 'required|string',
            'archive_data' => 'nullable|string'
        ]);

        $sourceKelasId = $request->source_kelas_id;
        $targetTahunAjaranId = $request->target_tahun_ajaran_id;
        $santriIds = json_decode($request->santri_ids, true);
        $shouldArchive = $request->archive_data === 'true';

        $activeYear = TahunAjaran::where('is_active', true)->first();
        
        if (!$activeYear) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif.');
        }

        DB::transaction(function () use ($sourceKelasId, $targetTahunAjaranId, $santriIds, $activeYear, $shouldArchive) {
            
            // Step 1: Archive data if requested
            if ($shouldArchive) {
                $archiveExists = \App\Models\Akademik\Arsip::where('kelas_id', $sourceKelasId)
                    ->where('tahun_ajaran_id', $activeYear->id)
                    ->exists();
                
                if (!$archiveExists) {
                    try {
                        $arsipController = app(ArsipController::class);
                        $arsipController->createArchive($sourceKelasId, $activeYear->id);
                    } catch (\Exception $e) {
                        Log::warning("Archive creation skipped for class $sourceKelasId: " . $e->getMessage());
                    }
                }
            }
            
            // Step 2: Mark current santri_kelas records as 'Arsip' (read-only)
            foreach ($santriIds as $santriId) {
                $currentRecord = SantriKelas::where('santri_profile_id', $santriId)
                    ->where('kelas_id', $sourceKelasId)
                    ->where('tahun_ajaran_id', $activeYear->id)
                    ->where('status', 'Aktif')
                    ->first();

                if ($currentRecord) {
                    $currentRecord->update(['status' => 'Arsip']);
                }

                // Step 3: Create new record for target year (keep same class level)
                SantriKelas::updateOrCreate(
                    [
                        'santri_profile_id' => $santriId,
                        'tahun_ajaran_id' => $targetTahunAjaranId,
                    ],
                    [
                        'kelas_id' => $sourceKelasId, // Keep in same class for new year
                        'status' => 'Aktif',
                    ]
                );
            }

            // Step 4: Clean up any existing data in target year to ensure fresh start
            try {
                \App\Models\Akademik\Absensi::whereIn('santri_profile_id', $santriIds)
                    ->where('tahun_ajaran_id', $targetTahunAjaranId)
                    ->where('kelas_id', $sourceKelasId)
                    ->delete();

                $guruMapelIds = \App\Models\Akademik\GuruMapel::where('tahun_ajaran_id', $targetTahunAjaranId)
                    ->where('kelas_id', $sourceKelasId)
                    ->pluck('id');

                if ($guruMapelIds->isNotEmpty()) {
                    \App\Models\Akademik\Penilaian::whereIn('guru_mapel_id', $guruMapelIds)
                        ->whereIn('santri_profile_id', $santriIds)
                        ->delete();
                }

                \App\Models\Akademik\SantriMapel::whereIn('santri_profile_id', $santriIds)->delete();
            } catch (\Exception $e) {
                Log::warning("Cleanup error during promotion: " . $e->getMessage());
            }
        });

        $message = 'Kenaikan kelas berhasil diproses. Santri dipindahkan ke tahun ajaran berikutnya.';
        if ($shouldArchive) {
            $message .= ' Data semester lama telah diarsipkan.';
        } else {
            $message .= ' Data semester lama masih tersimpan (read-only).';
        }

        return redirect()->back()->with('success', $message);
    }
}
