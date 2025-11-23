<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Mapel;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\Penilaian;
use App\Models\Akademik\Absensi;
use App\Models\User\SantriProfile;

class GuruMapelController extends Controller
{
    /**
     * Tampilkan halaman pemilihan mapel untuk guru
     */
    public function index()
    {
        $guru = Auth::guard('guru')->user();
        
        // Ambil mapel yang sudah dipilih guru
        $guruMapels = GuruMapel::with(['mapel', 'kelas'])
            ->where('guru_profile_id', $guru->id)
            ->get();
        
        // Ambil semua mapel untuk dropdown
        $mapels = Mapel::orderBy('nama_mapel')->get();
        
        // Ambil semua kelas untuk dropdown
        $kelas = Kelas::orderBy('level')->orderBy('nama_unik')->get();
        
        return view('Akademik.guru-mapel.index', compact('guruMapels', 'mapels', 'kelas'));
    }

    /**
     * Simpan pilihan mapel guru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'mapel_id' => 'required|exists:mapel,id',
            'kelas_id' => 'required|exists:kelas,id',
            'semester' => 'required|in:ganjil,genap',
            'tahun_ajaran' => 'required|string'
        ]);

        $guru = Auth::guard('guru')->user();

        // Cek apakah sudah ada
        $exists = GuruMapel::where('guru_profile_id', $guru->id)
            ->where('mapel_id', $validated['mapel_id'])
            ->where('kelas_id', $validated['kelas_id'])
            ->where('semester', $validated['semester'])
            ->where('tahun_ajaran', $validated['tahun_ajaran'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Anda sudah mengajar mapel ini di kelas tersebut'
            ], 400);
        }

        $guruMapel = GuruMapel::create([
            'guru_profile_id' => $guru->id,
            'mapel_id' => $validated['mapel_id'],
            'kelas_id' => $validated['kelas_id'],
            'semester' => $validated['semester'],
            'tahun_ajaran' => $validated['tahun_ajaran']
        ]);

        return response()->json([
            'message' => 'Mapel berhasil ditambahkan',
            'data' => $guruMapel->load(['mapel', 'kelas'])
        ], 201);
    }

    /**
     * Hapus mapel yang diajar guru
     */
    public function destroy(GuruMapel $guruMapel)
    {
        $guru = Auth::guard('guru')->user();

        // Pastikan ini mapel milik guru yang login
        if ($guruMapel->guru_profile_id != $guru->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        // Cek apakah sudah ada penilaian
        if ($guruMapel->penilaians()->count() > 0) {
            return response()->json([
                'message' => 'Tidak dapat dihapus karena sudah ada penilaian'
            ], 400);
        }

        $guruMapel->delete();

        return response()->json([
            'message' => 'Mapel berhasil dihapus'
        ]);
    }

    /**
     * Tampilkan rekap mapel (nilai dan kehadiran siswa)
     */
    public function rekap($guruMapelId)
    {
        $guru = Auth::guard('guru')->user();

        $guruMapel = GuruMapel::with(['mapel', 'kelas'])
            ->where('id', $guruMapelId)
            ->where('guru_profile_id', $guru->id)
            ->firstOrFail();

        // Ambil siswa dari kelas tersebut dengan nilai dan kehadiran
        $siswa = SantriProfile::whereHas('kelasAktif', function($q) use ($guruMapel) {
                $q->where('kelas_id', $guruMapel->kelas_id);
            })
            ->with([
                'kelasAktif.kelas',
                'santri', // Eager load santri for NIS
                'penilaians' => function($q) use ($guruMapelId) {
                    $q->where('guru_mapel_id', $guruMapelId);
                },
                'absensis' => function($q) use ($guruMapel) {
                    $q->where('kelas_id', $guruMapel->kelas_id);
                }
            ])
            ->orderBy('nama')
            ->get();

        // Hitung statistik kehadiran per siswa
        $siswa = $siswa->map(function($s) {
            $totalPertemuan = $s->absensis->count();
            $hadir = $s->absensis->where('status', 'H')->count();
            $sakit = $s->absensis->where('status', 'S')->count();
            $izin = $s->absensis->where('status', 'I')->count();
            $alpha = $s->absensis->where('status', 'A')->count();
            
            $s->kehadiran = [
                'total' => $totalPertemuan,
                'hadir' => $hadir,
                'sakit' => $sakit,
                'izin' => $izin,
                'alpha' => $alpha,
                'persentase' => $totalPertemuan > 0 ? round(($hadir / $totalPertemuan) * 100, 1) : 0
            ];

            // Ambil nilai
            $penilaians = $s->penilaians;
            
            // Calculate averages
            // Tugas includes: Tugas, UH, Praktek
            $tugasGrades = $penilaians->filter(function($p) {
                return in_array($p->jenis_penilaian, ['Tugas', 'UH', 'Praktek']);
            });
            $tugas = $tugasGrades->isNotEmpty() ? $tugasGrades->avg('nilai') : null;
            
            $utsGrades = $penilaians->where('jenis_penilaian', 'UTS');
            $uts = $utsGrades->isNotEmpty() ? $utsGrades->avg('nilai') : null;
            
            $uasGrades = $penilaians->where('jenis_penilaian', 'UAS');
            $uas = $uasGrades->isNotEmpty() ? $uasGrades->avg('nilai') : null;
            
            // Calculate final average
            $components = [];
            if ($tugas !== null) $components[] = $tugas;
            if ($uts !== null) $components[] = $uts;
            if ($uas !== null) $components[] = $uas;
            
            $rataRata = count($components) > 0 ? array_sum($components) / count($components) : null;

            $s->nilai_data = [
                'tugas' => $tugas ? number_format($tugas, 1) : null,
                'uts' => $uts ? number_format($uts, 1) : null,
                'uas' => $uas ? number_format($uas, 1) : null,
                'rata_rata' => $rataRata ? number_format($rataRata, 1) : null
            ];

            return $s;
        });

        return view('Akademik.guru-mapel.rekap', compact('guruMapel', 'siswa'));
    }
}
