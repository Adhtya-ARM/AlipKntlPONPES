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
     * Tampilkan halaman pemilihan mapel untuk guru dengan data lengkap
     */
    public function index()
    {
        $guru = Auth::guard('guru')->user();
        
        // Ambil mapel yang sudah dipilih guru dengan informasi lengkap
        $guruMapels = GuruMapel::with(['mapel', 'kelas.santriProfile'])
            ->where('guru_profile_id', $guru->id)
            ->get();
        
        // Hitung statistik untuk setiap mapel
        $guruMapels = $guruMapels->map(function($gm) {
            // Hitung jumlah siswa aktif
            $jumlahSiswa = $gm->kelas->santriProfile()
                ->where('status', 'aktif')
                ->count();
            
            // Hitung jumlah pertemuan (absensi yang sudah diinput)
            $jumlahPertemuan = Absensi::where('mapel_id', $gm->mapel_id)
                ->where('kelas_id', $gm->kelas_id)
                ->distinct('tanggal')
                ->count('tanggal');
            
            // Hitung rata-rata nilai
            $rataRataNilai = Penilaian::where('guru_mapel_id', $gm->id)
                ->avg('nilai');
            
            // Hitung jumlah siswa yang sudah dinilai
            $siswaDinilai = Penilaian::where('guru_mapel_id', $gm->id)
                ->distinct('santri_profile_id')
                ->count('santri_profile_id');
            
            $gm->jumlah_siswa = $jumlahSiswa;
            $gm->jumlah_pertemuan = $jumlahPertemuan;
            $gm->rata_rata_nilai = $rataRataNilai ? round($rataRataNilai, 1) : null;
            $gm->siswa_dinilai = $siswaDinilai;
            
            return $gm;
        });
        
        // Ambil semua mapel untuk dropdown
        $mapels = Mapel::orderBy('nama_mapel')->get();
        
        // Ambil semua kelas untuk dropdown
        $kelas = Kelas::orderBy('level')->get();
        
        return view('User.Guru.MapelSaya.index', compact('guruMapels', 'mapels', 'kelas'));
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

        // Cek apakah sudah ada kombinasi mapel + kelas yang sama
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
                'message' => 'Tidak dapat dihapus karena sudah ada penilaian. Silakan hapus penilaian terlebih dahulu.'
            ], 400);
        }

        $guruMapel->delete();

        return response()->json([
            'message' => 'Mapel berhasil dihapus'
        ]);
    }

    /**
     * Hapus semua penilaian untuk mapel ini
     */
    public function clearGrades(GuruMapel $guruMapel)
    {
        $guru = Auth::guard('guru')->user();

        if ($guruMapel->guru_profile_id != $guru->id) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $count = $guruMapel->penilaians()->count();
        $guruMapel->penilaians()->delete();

        return response()->json([
            'message' => "Berhasil menghapus $count data penilaian."
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
                'santri', 
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
