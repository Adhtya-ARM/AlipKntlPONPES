<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\Absensi;
use App\Models\User\SantriProfile;

class AbsensiController extends Controller
{
    /**
     * Display input absensi page for guru
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

        return view('Akademik.Absensi.input', compact('guruMapels'));
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
     * Store or update absensi
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guru_mapel_id' => 'required|exists:guru_mapel,id',
            'tanggal_absensi' => 'required|date',
            'absensi' => 'required|array',
            'absensi.*.id' => 'required|exists:santri_profile,id',
            'absensi.*.kehadiran' => 'required|string|in:H,I,S,A',
        ]);

        $guruMapel = GuruMapel::findOrFail($validated['guru_mapel_id']);
        $tanggal = $validated['tanggal_absensi'];

        try {
            DB::beginTransaction();

            foreach ($validated['absensi'] as $abs) {
                // Map kehadiran to status
                // Status is already in correct format (H, S, I, A)
                $status = $abs['kehadiran'];

                Absensi::updateOrCreate(
                    [
                        'santri_profile_id' => $abs['id'],
                        'tanggal' => $tanggal,
                        'mapel_id' => $guruMapel->mapel_id, // Add mapel_id to condition
                    ],
                    [
                        'kelas_id' => $guruMapel->kelas_id,
                        'status' => $status,
                        'keterangan' => null,
                    ]
                );
            }

            DB::commit();

            return response()->json([
                'message' => 'Absensi berhasil disimpan.',
                'tanggal' => $tanggal,
                'jumlah_santri' => count($validated['absensi'])
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Gagal menyimpan absensi: ' . $e->getMessage()
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
     * Display rekap kehadiran page
     */
    public function rekap(Request $request)
    {
        $guard = $this->getGuardName();

        if ($guard !== 'guru') {
            return back()->with('error', 'Unauthorized. Hanya guru yang bisa akses halaman ini.');
        }

        $user = auth('guru')->user();
        $guruProfile = $user->guruProfile;
        
        $jabatan = strtolower($guruProfile->jabatan ?? '');
        $isWakaOrKepsek = in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka']);

        if ($isWakaOrKepsek) {
            // Waka/Kepsek bisa melihat semua kelas
            $kelasList = Kelas::orderBy('level')->get();
        } else {
            // Guru biasa hanya melihat kelas yang diajar
            $kelasList = Kelas::whereHas('guruMapels', function($q) use ($guruProfile) {
                $q->where('guru_profile_id', $guruProfile->id);
            })->orderBy('level')->get();
        }

        return view('Akademik.Absensi.rekap', compact('kelasList'));
    }

    /**
     * Get rekap data via AJAX
     */
    public function getRekapData(Request $request)
    {
        $kelasId = $request->kelas_id;
        $bulan = $request->bulan; // Format: YYYY-MM

        if (!$kelasId || !$bulan) {
            return response()->json(['students' => []]);
        }

        // Get students in the class
        $kelas = Kelas::with('santriProfile')->findOrFail($kelasId);
        $santriList = $kelas->santriProfile()
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

        // Get attendance data
        $students = [];
        foreach ($santriList as $santri) {
            $attendanceRecords = Absensi::where('santri_profile_id', $santri->id)
                ->whereBetween('tanggal', [$startDate, $endDate])
                ->get()
                ->keyBy(function($item) {
                    return (int) date('j', strtotime($item->tanggal)); // Day of month
                });

            // Build attendance array (1-31)
            $attendance = [];
            $summary = ['H' => 0, 'S' => 0, 'I' => 0, 'A' => 0, 'D' => 0, 'T' => 0];
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                if (isset($attendanceRecords[$day])) {
                    $status = $attendanceRecords[$day]->status;
                    $attendance[$day] = $status;
                    $summary[$status] = ($summary[$status] ?? 0) + 1;
                } else {
                    $attendance[$day] = null;
                }
            }

            // Calculate percentage
            $totalHadir = $summary['H'];
            $totalDays = $daysInMonth;
            $persentase = $totalDays > 0 ? round(($totalHadir / $totalDays) * 100, 1) : 0;

            $students[] = [
                'id' => $santri->id,
                'nama' => $santri->nama,
                'nisn' => $santri->nisn,
                'attendance' => $attendance,
                'summary' => $summary,
                'persentase' => $persentase
            ];
        }

        return response()->json(['students' => $students]);
    }
}
