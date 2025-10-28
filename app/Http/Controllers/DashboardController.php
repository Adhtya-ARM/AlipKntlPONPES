<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan role/guard yang sedang login.
     * Route ini akan dipanggil oleh setiap role: /guru/dashboard, /santri/dashboard, dst.
     */
    public function index($guard)
    {
        // 1. Validasi Guard
        $allGuards = array_keys(Config::get('auth.guards'));
        if (!in_array($guard, $allGuards)) {
            // Jika guard yang diminta di URL tidak valid, redirect ke login
            return redirect('/login');
        }

        // 2. Cek apakah pengguna benar-benar login di guard tersebut
        if (!Auth::guard($guard)->check()) {
            // Jika tidak login, arahkan ke halaman login (middleware seharusnya sudah menangani ini)
            return redirect()->route('login');
        }

        // Ambil data pengguna yang sedang login
        $user = Auth::guard($guard)->user();
        
        // Tentukan data dan view berdasarkan role (guard)
        switch ($guard) {
            case 'guru':
                // Ambil data untuk dashboard guru
                $guruProfile = $user->Profile; // Use the correct relationship method
                if (!$guruProfile) {
                    // Handle case where guruProfile is null
                    return redirect()->route('login')->with('error', 'Profile guru tidak ditemukan.');
                }
                $mapelIds = \App\Models\Akademik\GuruMapel::where('guru_profile_id', $guruProfile->id)->pluck('mapel_id');
                $mapels = \App\Models\Akademik\Mapel::whereIn('id', $mapelIds)->get();

                // Hitung total santri yang diajar (unik berdasarkan kelas mapel)
                $kelasMapels = $mapels->pluck('kelas')->unique();
                $totalSantri = \App\Models\User\SantriProfile::whereIn('kelas', $kelasMapels)->count();

                // Total penilaian yang sudah diinput oleh guru ini
                $totalPenilaian = \App\Models\Akademik\Penilaian::where('guru_profile_id', $guruProfile->id)->count();

                return view('dashboard.guru', compact('mapels', 'totalSantri', 'totalPenilaian'));

            case 'santri':
             
                
                return view('dashboard.santri');

            case 'wali':
               
                return view('dashboard.wali');
            
            case 'web':
               
                return view('admin.dashboard');

            default:
                // Fallback jika ada guard yang tidak terdaftar di switch
                return redirect()->route('login');
        }
    }
}