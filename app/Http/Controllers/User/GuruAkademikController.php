<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Akademik\GuruMapel;
use App\Models\User\GuruProfile;
use App\Models\Akademik\Kelas;

class GuruAkademikController extends Controller
{
    /**
     * Menampilkan daftar kelas yang diajar oleh guru yang sedang login dengan informasi lengkap.
     */
    public function kelasSaya()
    {
        $user = Auth::guard('guru')->user();
        
        // Get guru profile
        if ($user instanceof GuruProfile) {
            $guruProfile = $user;
        } elseif (method_exists($user, 'guruProfile')) {
            $guruProfile = $user->guruProfile;
        } else {
            return back()->with('error', 'Profil guru tidak ditemukan.');
        }
        
        if (!$guruProfile) {
            return back()->with('error', 'Profil guru tidak ditemukan.');
        }
        
        $activeYear = \App\Models\Akademik\TahunAjaran::active()->first();

        // Ambil semua GuruMapel milik guru ini dengan relasi lengkap
        // IMPORTANT: Only show NON-ARCHIVED semesters in active teaching view
        $guruMapels = GuruMapel::where('guru_profile_id', $guruProfile->id)
            ->whereHas('tahunAjaran', function ($query) {
                $query->notArchived(); // Exclude archived semesters
            })
            ->with([
                'kelas',
                'kelas.santriKelas', // Load santriKelas collection
                'kelas.waliKelas',   // Load waliKelas (yang adalah GuruProfile)
                'mapel',
                'tahunAjaran'
            ])
            ->get();

        // Grouping berdasarkan Kelas
        $kelasAjar = [];
        
        foreach ($guruMapels as $gm) {
            if (!$gm->kelas) continue;

            $kelasId = $gm->kelas->id;

            // Skip if this kelas is archived for the active year (all santri_kelas are 'Arsip')
            if ($activeYear) {
                $hasActiveSantri = \App\Models\User\SantriKelas::where('kelas_id', $kelasId)
                    ->where('tahun_ajaran_id', $activeYear->id)
                    ->where('status', '!=', 'Arsip')
                    ->exists();

                // Also allow if there are guru_mapel entries for other years (we only show current teaching assignments)
                $hasActiveGuruMapel = $guruMapels->where('kelas.id', $kelasId)
                    ->contains(function($g) use ($activeYear) {
                        return $g->tahun_ajaran_id == ($activeYear->id ?? null);
                    });

                if (!$hasActiveSantri && !$hasActiveGuruMapel) {
                    // Skip this kelas because it's effectively archived for the active year
                    continue;
                }
            }

            if (!isset($kelasAjar[$kelasId])) {
                $kelasAjar[$kelasId] = [
                    'kelas' => $gm->kelas,
                    'mapels' => [],
                    'guru_mapels' => collect()
                ];
            }

            // Attach pivot data to mapel
            $mapel = $gm->mapel;
            if ($mapel) {
                $mapel->pivot = (object)[
                    'semester' => $gm->semester,
                    'tahun_ajaran' => $gm->tahun_ajaran
                ];
                $kelasAjar[$kelasId]['mapels'][] = $mapel;
            }

            $kelasAjar[$kelasId]['guru_mapels']->push($gm);
        }

        // Ubah ke array values untuk dikirim ke view
        $dataKelas = array_values($kelasAjar);

        return view('User.Guru.KelasSaya.index', compact('dataKelas'));
    }
}
