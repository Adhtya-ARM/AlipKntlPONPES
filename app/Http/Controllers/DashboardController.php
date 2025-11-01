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
<<<<<<< HEAD
    public function index(Request $request, string $guard)
    {
        // 1. Validasi Akses dan Guard
        // Middleware ('auth:guard') seharusnya sudah memastikan user login
        // Kita hanya perlu memastikan guard yang diminta di URL benar-benar di-autentikasi.
=======
    public function index()
    {
        // 1. Ambil guard dari route defaults
        $guard = request()->route()->defaults['guard'] ?? 'web';

        // 2. Validasi Guard
        $allGuards = array_keys(Config::get('auth.guards'));
        if (!in_array($guard, $allGuards)) {
            // Jika guard yang diminta di URL tidak valid, redirect ke login
            return redirect('/login');
        }

        // 3. Cek apakah pengguna benar-benar login di guard tersebut
>>>>>>> f050ae17c144e6079ae8b8ec27ed5f44f35675f6
        if (!Auth::guard($guard)->check()) {
             // Jika middleware gagal, atau user mencoba akses langsung tanpa auth.
            return redirect()->route('login');
        }

        // Ambil data pengguna yang sedang login
        $user = Auth::guard($guard)->user();
<<<<<<< HEAD
        
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
=======

        // Tentukan data dan view berdasarkan role (guard)
        switch ($guard) {
            case 'guru':
                // Ambil data untuk dashboard guru
                $guruProfile = $user->guruProfile; // Use the correct relationship method
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
>>>>>>> f050ae17c144e6079ae8b8ec27ed5f44f35675f6

            default:
                // Jika route /nama_guard_aneh/dashboard diakses, kembalikan 403 atau redirect.
                return abort(403, 'Akses dashboard role ini tidak diizinkan.');
        }
    }

<<<<<<< HEAD
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
=======
    public function guruSantri()
    {
        $user = Auth::guard('guru')->user();
        $guruProfile = $user->guruProfile;
        if (!$guruProfile) {
            return redirect()->route('guru.dashboard')->with('error', 'Profile guru tidak ditemukan.');
        }

        // Ambil mapel yang diajar guru
        $mapelIds = \App\Models\Akademik\GuruMapel::where('guru_profile_id', $guruProfile->id)->pluck('mapel_id');
        $mapels = \App\Models\Akademik\Mapel::whereIn('id', $mapelIds)->get();

        // Ambil kelas yang diajar
        $kelasMapels = $mapels->pluck('kelas')->unique();

        // Ambil santri yang ada di kelas tersebut
        $santris = \App\Models\User\SantriProfile::whereIn('kelas', $kelasMapels)->with('santri')->paginate(10);

        return view('dashboard.guru-santri', compact('santris'));
    }

    public function guruWali()
    {
        $user = Auth::guard('guru')->user();
        $guruProfile = $user->guruProfile;
        if (!$guruProfile) {
            return redirect()->route('guru.dashboard')->with('error', 'Profile guru tidak ditemukan.');
        }

        // Ambil mapel yang diajar guru
        $mapelIds = \App\Models\Akademik\GuruMapel::where('guru_profile_id', $guruProfile->id)->pluck('mapel_id');
        $mapels = \App\Models\Akademik\Mapel::whereIn('id', $mapelIds)->get();

        // Ambil kelas yang diajar
        $kelasMapels = $mapels->pluck('kelas')->unique();

        // Ambil wali santri yang ada di kelas tersebut
        $waliIds = \App\Models\User\SantriProfile::whereIn('kelas', $kelasMapels)->pluck('profile_wali_id')->unique();
        $walis = \App\Models\User\WaliProfile::whereIn('id', $waliIds)->paginate(10);

        return view('dashboard.guru-wali', compact('walis'));
>>>>>>> f050ae17c144e6079ae8b8ec27ed5f44f35675f6
    }
}