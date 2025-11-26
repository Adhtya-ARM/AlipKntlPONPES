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
        
        // Ambil semua GuruMapel milik guru ini dengan relasi lengkap
        $guruMapels = GuruMapel::where('guru_profile_id', $guruProfile->id)
            ->with([
                'kelas.santriKelas', // Load santriKelas collection
                'kelas.waliKelas',   // Load waliKelas (yang adalah GuruProfile)
                'mapel'
            ])
            ->get();

        // Grouping berdasarkan Kelas
        $kelasAjar = [];
        
        foreach ($guruMapels as $gm) {
            if (!$gm->kelas) continue;
            
            $kelasId = $gm->kelas->id;
            
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
