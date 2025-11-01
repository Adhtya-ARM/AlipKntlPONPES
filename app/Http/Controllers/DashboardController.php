<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate; // Digunakan untuk otorisasi lebih lanjut
use Illuminate\Database\Eloquent\Builder; // Digunakan untuk query yang lebih kompleks

// Impor Model yang dibutuhkan
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Mapel;
use App\Models\Akademik\Penilaian;
use App\Models\User\SantriProfile;
use App\Models\User\Santri; // Pastikan ini diimpor jika diperlukan

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard berdasarkan role/guard yang sedang login.
     * Route ini akan dipanggil oleh setiap role: /guru/dashboard, /santri/dashboard, dst.
     */
    public function index(Request $request, string $guard)
    {
        // 1. Validasi Akses dan Guard
        // Middleware ('auth:guard') seharusnya sudah memastikan user login
        // Kita hanya perlu memastikan guard yang diminta di URL benar-benar di-autentikasi.
        if (!Auth::guard($guard)->check()) {
             // Jika middleware gagal, atau user mencoba akses langsung tanpa auth.
            return redirect()->route('login');
        }

        // Ambil data pengguna yang sedang login
        $user = Auth::guard($guard)->user();
        
        // 2. Tentukan Data dan View berdasarkan Role
        switch ($guard) {
            case 'guru':
                return $this->handleGuruDashboard($user);

            case 'santri':
                return $this->handleSantriDashboard($user);
            
            case 'wali':
                return $this->handleWaliDashboard($user);
                
            case 'web':
                return $this->handleWebDashboard($user);

            default:
                // Jika route /nama_guard_aneh/dashboard diakses, kembalikan 403 atau redirect.
                return abort(403, 'Akses dashboard role ini tidak diizinkan.');
        }
    }

    // --- LOGIKA DASHBOARD TERPISAH (CLEANER CODE) ---

    private function handleGuruDashboard($user)
    {
        // Asumsi: Model Guru memiliki relasi 'guruProfile()'
        $guruProfile = $user->guruProfile; // Perbaiki nama relasi jika perlu (e.g., $user->profile)

        if (!$guruProfile) {
            // Log user out atau redirect dengan pesan error jika profile tidak ditemukan
            Auth::guard('guru')->logout();
            return redirect()->route('login')->with('error', 'Profile guru tidak ditemukan. Silakan hubungi admin.');
        }

        // 1. Ambil Mapel yang diajar
        $mapels = $guruProfile->guruMapels()->with('mapel')->get()->pluck('mapel');

        // 2. Hitung total santri unik yang diajar (berdasarkan kelas)
        //$kelasIds = $mapels->pluck('kelas_id')->unique(); 
       // $totalSantri = SantriProfile::whereIn('kelas_id', $kelasIds)->count();

        // 3. Total Penilaian
        //$totalPenilaian = Penilaian::where('guru_profile_id', $guruProfile->id)->count();

        return view('dashboard.guru', compact('mapels',  'guruProfile'));  // 'totalPenilaian', 'totalSantri',
    }

    private function handleSantriDashboard($user)
    {
        // Asumsi: Santri memiliki relasi SantriProfile()
        $santriProfile = $user->santriProfile; // Ambil profile santri

        // 1. Data Penilaian Santri
        $penilaian = $santriProfile->penilaian()->with(['guruProfile', 'mapel'])->get();

        // 2. Data Pelanggaran (contoh)
        // $pelanggaran = $user->pelanggaran;

        return view('dashboard.santri', compact('santriProfile', 'penilaian'));
    }

    private function handleWaliDashboard($user)
    {
        // Asumsi: Model Wali memiliki relasi 'santriAnak()'
        $santriAnak = $user->santriAnak()->with('santriProfile')->get(); // Ambil data santri yang diwalikan

        // Hitung total anak dan status pembayaran (contoh)
        $totalAnak = $santriAnak->count();

        return view('dashboard.wali', compact('santriAnak', 'totalAnak'));
    }

    private function handleWebDashboard($user)
    {
        // Logic untuk Administrator/Web
        // Biasanya berisi statistik global: Total Guru, Total Santri, Transaksi, dll.
        $totalGuru = \App\Models\User\Guru::count();
        $totalSantri = Santri::count();
        
        return view('dashboard.web', compact('totalGuru', 'totalSantri', 'user'));
    }
}