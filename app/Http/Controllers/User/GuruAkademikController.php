<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Kelas;

class GuruAkademikController extends Controller
{
    /**
     * Menampilkan daftar kelas yang diajar oleh guru yang sedang login.
     */
    public function kelasSaya()
    {
        $guru = Auth::guard('guru')->user();
        
        // Ambil semua GuruMapel milik guru ini, load relasi kelas dan mapel
        $guruMapels = GuruMapel::where('guru_profile_id', $guru->guruProfile->id)
            ->with(['kelas', 'mapel'])
            ->get();

        // Grouping berdasarkan Kelas agar tampilannya rapi per kelas
        // Struktur: [ kelas_id => [ 'kelas' => object, 'mapels' => [ ... ] ] ]
        $kelasAjar = [];
        
        foreach ($guruMapels as $gm) {
            if (!$gm->kelas) continue;
            
            $kelasId = $gm->kelas->id;
            
            if (!isset($kelasAjar[$kelasId])) {
                $kelasAjar[$kelasId] = [
                    'kelas' => $gm->kelas,
                    'mapels' => []
                ];
            }
            
            $kelasAjar[$kelasId]['mapels'][] = $gm->mapel;
        }

        // Ubah ke array values untuk dikirim ke view
        $dataKelas = array_values($kelasAjar);

        return view('User.Guru.KelasSaya.index', compact('dataKelas'));
    }
}
