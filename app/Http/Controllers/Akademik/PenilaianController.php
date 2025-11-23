<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Penilaian;
use App\Models\User\SantriProfile;

class PenilaianController extends Controller
{
    /**
     * Display input penilaian page for guru
     */
    public function index()
    {
        $guard = $this->getGuardName();

        if ($guard !== 'guru') {
            return back()->with('error', 'Unauthorized. Hanya guru yang bisa akses halaman ini.');
        }

        $user = auth('guru')->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai guru terlebih dahulu.');
        }

        $guruProfile = $user->guruProfile;
        if (!$guruProfile) {
            return back()->with('error', 'Profil guru tidak ditemukan.');
        }

        $guruMapels = GuruMapel::with(['mapel:id,nama_mapel', 'kelas:id,level,nama_unik'])
            ->where('guru_profile_id', $guruProfile->id)
            ->get();

        return view('Akademik.Penilaian.index', compact('guruMapels'));
    }

    /**
     * Get list of santri for a specific mapel/kelas
     */
    public function getSantriByMapel($guruMapelId)
    {
        $guruMapel = GuruMapel::with('kelas.santriProfile')->findOrFail($guruMapelId);

        $santriList = $guruMapel->kelas?->santriProfile()
            ->select('santri_profile.id', 'santri_profile.nama', 'santris.nisn')
            ->join('santris', 'santri_profile.santri_id', '=', 'santris.id')
            ->where('santri_profile.status', 'aktif')
            ->orderBy('santri_profile.nama')
            ->get() ?? collect([]);

        return response()->json([
            'santri' => $santriList,
            'jumlahSantri' => $santriList->count(),
        ]);
    }

    /**
     * Store or update penilaian
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_mapel_id' => 'required|exists:guru_mapel,id',
            'tanggal_penilaian' => 'required|date',
            'jenis_penilaian' => 'required|string',
            'penilaian' => 'required|array',
            'penilaian.*.id' => 'required|exists:santri_profile,id',
            'penilaian.*.nilai' => 'nullable|numeric|min:0|max:100',
            'penilaian.*.keterangan' => 'nullable|string',
        ]);

        $guruMapelId = $validated['guru_mapel_id'];
        $tanggal = $validated['tanggal_penilaian'];
        $jenis = $validated['jenis_penilaian'];

        try {
            DB::beginTransaction();

            foreach ($validated['penilaian'] as $nilaiData) {
                // Skip if nilai is null/empty, unless you want to record it as 0 or empty
                // But usually we updateOrCreate. If value is null, maybe we shouldn't create?
                // Let's assume we updateOrCreate if it exists, or create if provided.
                
                if (!isset($nilaiData['nilai']) && !isset($nilaiData['keterangan'])) {
                    continue; 
                }

                Penilaian::updateOrCreate(
                    [
                        'santri_profile_id' => $nilaiData['id'],
                        'guru_mapel_id' => $guruMapelId,
                        'tanggal' => $tanggal,
                        'jenis_penilaian' => $jenis,
                    ],
                    [
                        'nilai' => $nilaiData['nilai'] ?? 0,
                        'keterangan' => $nilaiData['keterangan'] ?? null,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Penilaian berhasil disimpan.',
                'tanggal' => $tanggal,
                'jumlah_santri' => count($validated['penilaian'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Gagal menyimpan penilaian: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get guard name
     */
    private function getGuardName()
    {
        foreach (['guru', 'santri', 'wali', 'web'] as $guard) {
            if (auth()->guard($guard)->check()) return $guard;
        }
        return null;
    }

    /**
     * Display rekap penilaian page
     */
    public function rekap(Request $request)
    {
        $guard = $this->getGuardName();

        if ($guard !== 'guru') {
            return back()->with('error', 'Unauthorized. Hanya guru yang bisa akses halaman ini.');
        }

        $user = auth('guru')->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Silakan login sebagai guru terlebih dahulu.');
        }

        $guruProfile = $user->guruProfile;
        if (!$guruProfile) {
            return back()->with('error', 'Profil guru tidak ditemukan.');
        }

        $guruMapels = GuruMapel::with(['mapel:id,nama_mapel', 'kelas:id,level,nama_unik'])
            ->where('guru_profile_id', $guruProfile->id)
            ->get();

        return view('Akademik.Penilaian.rekap', compact('guruMapels'));
    }

    /**
     * Get rekap data via AJAX
     */
    public function getRekapData(Request $request)
    {
        $guruMapelId = $request->guru_mapel_id;
        $bulan = $request->bulan; // Format: YYYY-MM

        if (!$guruMapelId || !$bulan) {
            return response()->json(['students' => []]);
        }

        // Get students in the mapel's class
        $guruMapel = GuruMapel::with('kelas.santriProfile')->findOrFail($guruMapelId);
        $santriList = $guruMapel->kelas?->santriProfile()
            ->select('santri_profile.id', 'santri_profile.nama', 'santris.nisn')
            ->join('santris', 'santri_profile.santri_id', '=', 'santris.id')
            ->where('santri_profile.status', 'aktif')
            ->orderBy('santri_profile.nama')
            ->get();

        // Get date range
        [$year, $month] = explode('-', $bulan);
        $startDate = "{$year}-{$month}-01";
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $endDate = "{$year}-{$month}-{$daysInMonth}";

        // Get grades data
        $students = [];
        foreach ($santriList as $santri) {
            $gradeRecords = Penilaian::where('santri_profile_id', $santri->id)
                ->where('guru_mapel_id', $guruMapelId)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->get()
                ->keyBy(function($item) {
                    return (int) date('j', strtotime($item->tanggal)); // Day of month
                });

            // Build grades array (1-31)
            $grades = [];
            $totalNilai = 0;
            $count = 0;
            $min = null;
            $max = null;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                if (isset($gradeRecords[$day])) {
                    $record = $gradeRecords[$day];
                    $nilai = (float) $record->nilai;
                    
                    $grades[$day] = [
                        'nilai' => $nilai,
                        'jenis' => $record->jenis_penilaian
                    ];
                    
                    $totalNilai += $nilai;
                    $count++;
                    
                    if ($min === null || $nilai < $min) $min = $nilai;
                    if ($max === null || $nilai > $max) $max = $nilai;
                } else {
                    $grades[$day] = null;
                }
            }

            // Calculate average
            $average = $count > 0 ? round($totalNilai / $count, 1) : 0;

            $students[] = [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'nisn' => $santri->nisn,
                'grades' => $grades,
                'average' => $average,
                'count' => $count,
                'min' => $min,
                'max' => $max
            ];
        }

        return response()->json(['students' => $students]);
    }
}