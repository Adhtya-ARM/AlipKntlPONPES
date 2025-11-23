<?php

namespace App\Http\Controllers\Santri;

use App\Http\Controllers\Controller;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Absensi;
use App\Models\Akademik\Penilaian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SantriAkademikController extends Controller
{
    /**
     * Rekap Kehadiran Santri
     */
    public function kehadiran(Request $request)
    {
        $user = Auth::guard('santri')->user();
        $santriProfile = $user->SantriProfile;

        if (!$santriProfile) {
            return redirect()->route('santri.dashboard')->with('error', 'Profil santri tidak ditemukan.');
        }

        // Filter bulan (default: bulan ini)
        $bulan = $request->input('bulan', now()->format('Y-m'));
        
        // Ambil data absensi santri untuk bulan yang dipilih
        $absensi = $santriProfile->absensis()
            ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])
            ->with(['kelas'])
            ->orderBy('tanggal', 'desc')
            ->paginate(20);

        // Statistik kehadiran
        $stats = [
            'hadir' => $santriProfile->absensis()->where('status', 'hadir')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])->count(),
            'sakit' => $santriProfile->absensis()->where('status', 'sakit')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])->count(),
            'izin' => $santriProfile->absensis()->where('status', 'izin')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])->count(),
            'alpa' => $santriProfile->absensis()->where('status', 'alpa')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])->count(),
        ];

        return view('Santri.Kehadiran.index', compact('santriProfile', 'absensi', 'stats', 'bulan'));
    }

    /**
     * Daftar Mata Pelajaran Santri
     */
    public function mapel()
    {
        $user = Auth::guard('santri')->user();
        $santriProfile = $user->SantriProfile;

        if (!$santriProfile) {
            return redirect()->route('santri.dashboard')->with('error', 'Profil santri tidak ditemukan.');
        }

        // Ambil kelas aktif
        $kelasAktif = $santriProfile->santriKelas;
        $kelas = $kelasAktif ? $kelasAktif->kelas : null;

        // Ambil mapel berdasarkan kelas
        $mapels = collect();
        if ($kelas) {
            $mapels = GuruMapel::where('kelas_id', $kelas->id)
                ->with(['mapel', 'guruProfile', 'kelas'])
                ->get();
        }

        return view('Santri.Mapel.index', compact('santriProfile', 'kelas', 'mapels'));
    }

    /**
     * Nilai Santri
     */
    public function nilai(Request $request)
    {
        $user = Auth::guard('santri')->user();
        $santriProfile = $user->SantriProfile;

        if (!$santriProfile) {
            return redirect()->route('santri.dashboard')->with('error', 'Profil santri tidak ditemukan.');
        }

        // Filter jenis penilaian (default: semua)
        $jenis = $request->input('jenis', 'semua');
        
        // Query nilai
        $nilaiQuery = $santriProfile->penilaians()
            ->with(['guruMapel.mapel', 'guruMapel.guruProfile']);

        if ($jenis !== 'semua') {
            $nilaiQuery->where('jenis_penilaian', $jenis);
        }

        $nilai = $nilaiQuery->orderBy('tanggal', 'desc')->paginate(15);

        // Statistik nilai
        $avgNilai = $santriProfile->penilaians()->avg('nilai') ?? 0;
        $totalNilai = $santriProfile->penilaians()->count();

        return view('User.Santri.Nilai.index', compact('santriProfile', 'nilai', 'avgNilai', 'totalNilai', 'jenis'));
    }
}
