<?php

namespace App\Http\Controllers\Wali;

use App\Http\Controllers\Controller;
use App\Models\User\SantriProfile;
use App\Models\Akademik\GuruMapel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class WaliAkademikController extends Controller
{
    /**
     * Rekap Kehadiran Anak
     */
    public function kehadiran(Request $request)
    {
        $user = Auth::guard('wali')->user();
        $waliProfile = $user->WaliProfile;

        if (!$waliProfile) {
            return redirect()->route('wali.dashboard')->with('error', 'Profil wali tidak ditemukan.');
        }

        // Ambil semua anak
        $santriAnak = SantriProfile::where('wali_profile_id', $waliProfile->id)
            ->with(['santri', 'santriKelas.kelas'])
            ->get();

        // Filter bulan (default: bulan ini)
        $bulan = $request->input('bulan', now()->format('Y-m'));
        
        // Filter santri (default: semua)
        $santriId = $request->input('santri_id', 'semua');

        // Data kehadiran per anak
        $kehadiranData = [];
        
        foreach ($santriAnak as $santri) {
            // Skip jika filter santri aktif dan bukan santri yang dipilih
            if ($santriId !== 'semua' && $santri->id != $santriId) {
                continue;
            }

            $stats = [
                'hadir' => $santri->absensis()->where('status', 'hadir')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])->count(),
                'sakit' => $santri->absensis()->where('status', 'sakit')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])->count(),
                'izin' => $santri->absensis()->where('status', 'izin')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])->count(),
                'alpa' => $santri->absensis()->where('status', 'alpa')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])->count(),
            ];

            $absensiList = $santri->absensis()
                ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulan])
                ->orderBy('tanggal', 'desc')
                ->limit(10)
                ->get();

            $kehadiranData[] = [
                'santri' => $santri,
                'stats' => $stats,
                'absensi' => $absensiList,
            ];
        }

        return view('User.Wali.Kehadiran.index', compact('waliProfile', 'santriAnak', 'kehadiranData', 'bulan', 'santriId'));
    }

    /**
     * Mata Pelajaran Anak
     */
    public function mapel(Request $request)
    {
        $user = Auth::guard('wali')->user();
        $waliProfile = $user->WaliProfile;

        if (!$waliProfile) {
            return redirect()->route('wali.dashboard')->with('error', 'Profil wali tidak ditemukan.');
        }

        // Ambil semua anak
        $santriAnak = SantriProfile::where('wali_profile_id', $waliProfile->id)
            ->with(['santri', 'santriKelas.kelas'])
            ->get();

        // Filter santri (default: semua)
        $santriId = $request->input('santri_id', 'semua');

        // Data mapel per anak
        $mapelData = [];
        
        foreach ($santriAnak as $santri) {
            // Skip jika filter santri aktif dan bukan santri yang dipilih
            if ($santriId !== 'semua' && $santri->id != $santriId) {
                continue;
            }

            $kelasAktif = $santri->santriKelas;
            $kelas = $kelasAktif ? $kelasAktif->kelas : null;

            $mapels = collect();
            if ($kelas) {
                $mapels = GuruMapel::where('kelas_id', $kelas->id)
                    ->with(['mapel', 'guruProfile'])
                    ->get();
            }

            $mapelData[] = [
                'santri' => $santri,
                'kelas' => $kelas,
                'mapels' => $mapels,
            ];
        }

        return view('User.Wali.Mapel.index', compact('waliProfile', 'santriAnak', 'mapelData', 'santriId'));
    }

    /**
     * Nilai Anak
     */
    public function nilai(Request $request)
    {
        $user = Auth::guard('wali')->user();
        $waliProfile = $user->WaliProfile;

        if (!$waliProfile) {
            return redirect()->route('wali.dashboard')->with('error', 'Profil wali tidak ditemukan.');
        }

        // Ambil semua anak
        $santriAnak = SantriProfile::where('wali_profile_id', $waliProfile->id)
            ->with(['santri', 'santriKelas.kelas'])
            ->get();

        // Filter santri (default: semua)
        $santriId = $request->input('santri_id', 'semua');
        
        // Filter jenis penilaian (default: semua)
        $jenis = $request->input('jenis', 'semua');

        // Data nilai per anak
        $nilaiData = [];
        
        foreach ($santriAnak as $santri) {
            // Skip jika filter santri aktif dan bukan santri yang dipilih
            if ($santriId !== 'semua' && $santri->id != $santriId) {
                continue;
            }

            $nilaiQuery = $santri->penilaians()
                ->with(['guruMapel.mapel', 'guruMapel.guruProfile']);

            if ($jenis !== 'semua') {
                $nilaiQuery->where('jenis_penilaian', $jenis);
            }

            $nilai = $nilaiQuery->orderBy('tanggal', 'desc')->limit(10)->get();
            $avgNilai = $santri->penilaians()->avg('nilai') ?? 0;
            $totalNilai = $santri->penilaians()->count();

            $nilaiData[] = [
                'santri' => $santri,
                'nilai' => $nilai,
                'avgNilai' => $avgNilai,
                'totalNilai' => $totalNilai,
            ];
        }

        return view('User.Wali.Nilai.index', compact('waliProfile', 'santriAnak', 'nilaiData', 'santriId', 'jenis'));
    }
}
