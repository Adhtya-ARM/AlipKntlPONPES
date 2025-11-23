<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

// Models (sesuaikan namespace jika berbeda)
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Mapel;
use App\Models\Akademik\Penilaian;
use App\Models\Akademik\RencanaPembelajaran;
use App\Models\User\SantriKelas;
use App\Models\User\SantriProfile;
use App\Models\User\Santri;
use App\Models\User\Guru;
use App\Models\User\WaliProfile;

class DashboardController extends Controller
{
    /**
     * Entry point dashboard. Expects route default 'guard' (contoh: ->defaults(['guard'=>'guru'])).
     */
    public function index(Request $request)
    {
        $guard = $request->route()->defaults['guard'] ?? 'web';

        $allGuards = array_keys(Config::get('auth.guards', []));
        if (!in_array($guard, $allGuards, true)) {
            return redirect('/login')->with('error', 'Guard yang diakses tidak valid.');
        }

        if (!Auth::guard($guard)->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard($guard)->user();

        switch ($guard) {
            case 'guru':
                return $this->handleGuruDashboard($user);
            case 'santri':
                return $this->handleSantriDashboard($user);
            case 'wali':
                return $this->handleWaliDashboard($user);
            case 'web':
            default:
                return $this->handleWebDashboard($user);
        }
    }

    /**
     * Guru dashboard: mempertimbangkan session('active_guru_role').
     */
    private function handleGuruDashboard($user)
    {
        $guruProfile = $user->guruProfile ?? null;

        if (!$guruProfile) {
            Auth::guard('guru')->logout();
            return redirect()->route('login')->with('error', 'Profil guru tidak ditemukan. Hubungi admin.');
        }

        // Ambil semua mapel yang diajar oleh guru (relasi atau model pivot)
        // Support dua pola: relasi guruMapels() yang punya relation mapel OR direct relation mapels()
        $guruMapels = collect();

        if (method_exists($guruProfile, 'guruMapels')) {
            $guruMapels = $guruProfile->guruMapels()->with(['mapel', 'kelas'])->get();
        } elseif (method_exists($guruProfile, 'mapels')) {
            $guruMapels = $guruProfile->mapels()->withPivot('kelas_id')->get();
        }

        // Extract Mapel model collection (robust)
        $mapels = $guruMapels->map(function ($item) {
            // jika item adalah pivot container with relation
            if (isset($item->mapel) && $item->mapel) {
                return $item->mapel;
            }
            // jika item itself adalah Mapel
            return $item;
        })->filter()->unique('id')->values();

        // Tentukan kelas id/identifier dari mapel (cari fields umum: kelas_id, kelas, level)
        $kelasIds = $mapels->map(function ($m) {
            if (isset($m->kelas_id)) return $m->kelas_id;
            if (isset($m->kelas)) return $m->kelas;
            if (isset($m->level)) return $m->level;
            return null;
        })->filter()->unique()->values()->all();

        // Ambil active role (mts|ma|both) dari session/user fallback
        $activeRole = session('active_guru_role') ?? ($user->last_active_guru_role ?? null);
        $activeRole = $this->normalizeActiveRole($activeRole, $user->sub_roles ?? []);

        // Total santri yang diajar: cari berdasarkan kelas identifier yang tersedia
        $totalSantri = 0;
        if (!empty($kelasIds)) {
            // coba dua kemungkinan kolom pada SantriKelas: kelas_id atau kelas
            $query = SantriKelas::query();
            if (SchemaHasColumn(SantriKelas::class, 'kelas_id')) {
                $query->whereIn('kelas_id', $kelasIds);
            } elseif (SchemaHasColumn(SantriKelas::class, 'kelas')) {
                $query->whereIn('kelas', $kelasIds);
            } else {
                // fallback: jika SantriProfile menyimpan kelas, cari dari sana
                $totalSantri = SantriProfile::whereIn('kelas', $kelasIds)->count();
            }

            if ($totalSantri === 0) {
                try {
                    $totalSantri = $query->count();
                } catch (\Throwable $e) {
                    $totalSantri = 0;
                }
            }
        }

        // Filter penilaian sesuai guru mapel yang diajar
        $guruMapelIds = $guruMapels->pluck('id')->toArray();
        $totalPenilaian = !empty($guruMapelIds) 
            ? Penilaian::whereIn('guru_mapel_id', $guruMapelIds)->count() 
            : 0;

        // Ambil data kalender akademik bulan ini
        $currentMonth = now()->format('Y-m');
        $kalenderData = RencanaPembelajaran::forMonth($currentMonth)->get();
        
        // Format kalender untuk view (grouping by date)
        $kalenderEvents = [];
        foreach ($kalenderData as $item) {
            $from = \Carbon\Carbon::parse($item->from_date);
            $to = \Carbon\Carbon::parse($item->to_date);
            
            // Loop semua tanggal dari from_date sampai to_date
            $current = $from->clone();
            while ($current->lte($to)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($kalenderEvents[$dateKey])) {
                    $kalenderEvents[$dateKey] = [];
                }
                $kalenderEvents[$dateKey][] = [
                    'jenis' => $item->jenis,
                    'judul' => $item->judul,
                    'catatan' => $item->catatan,
                ];
                $current->addDay();
            }
        }

        return view('Dashboard.Guru', compact('mapels', 'guruMapels', 'guruProfile', 'totalSantri', 'totalPenilaian', 'activeRole', 'kalenderEvents'));
    }

    /**
     * Santri dashboard.
     */
    private function handleSantriDashboard($user)
    {
        $santriProfile = $user->SantriProfile ?? null;

        if (!$santriProfile) {
            Auth::guard('santri')->logout();
            return redirect()->route('login')->with('error', 'Profil santri tidak ditemukan.');
        }

        // Ambil kelas aktif santri
        $kelasAktif = $santriProfile->santriKelas ?? null;
        $kelas = $kelasAktif ? $kelasAktif->kelas : null;

        // Ambil mapel yang terdaftar (melalui guru_mapel)
        $mapels = collect();
        if ($kelas) {
            $mapels = GuruMapel::where('kelas_id', $kelas->id)
                ->with(['mapel', 'guruProfile'])
                ->get();
        }

        // Statistik Absensi bulan ini
        $bulanIni = now()->format('Y-m');
        $absensiStats = [
            'hadir' => $santriProfile->absensis()
                ->where('status', 'hadir')
                ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])
                ->count(),
            'sakit' => $santriProfile->absensis()
                ->where('status', 'sakit')
                ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])
                ->count(),
            'izin' => $santriProfile->absensis()
                ->where('status', 'izin')
                ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])
                ->count(),
            'alpa' => $santriProfile->absensis()
                ->where('status', 'alpa')
                ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])
                ->count(),
        ];

        // Total penilaian yang sudah masuk
        $totalPenilaian = $santriProfile->penilaians()->count();

        // Penilaian terbaru (5 terakhir)
        $penilaianTerbaru = $santriProfile->penilaians()
            ->with(['guruMapel.mapel', 'guruMapel.guruProfile'])
            ->orderBy('tanggal', 'desc')
            ->limit(5)
            ->get();

        // Ambil data kalender akademik bulan ini
        $currentMonth = now()->format('Y-m');
        $kalenderData = RencanaPembelajaran::forMonth($currentMonth)->get();
        
        // Format kalender untuk view (grouping by date)
        $kalenderEvents = [];
        foreach ($kalenderData as $item) {
            $from = \Carbon\Carbon::parse($item->from_date);
            $to = \Carbon\Carbon::parse($item->to_date);
            
            $current = $from->clone();
            while ($current->lte($to)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($kalenderEvents[$dateKey])) {
                    $kalenderEvents[$dateKey] = [];
                }
                $kalenderEvents[$dateKey][] = [
                    'jenis' => $item->jenis,
                    'judul' => $item->judul,
                    'catatan' => $item->catatan,
                ];
                $current->addDay();
            }
        }

        return view('Dashboard.Santri', compact(
            'santriProfile', 
            'kelas', 
            'mapels', 
            'absensiStats', 
            'totalPenilaian', 
            'penilaianTerbaru',
            'kalenderEvents'
        ));
    }

    /**
     * Wali dashboard.
     */
    private function handleWaliDashboard($user)
    {
        $waliProfile = $user->WaliProfile ?? null;

        if (!$waliProfile) {
            Auth::guard('wali')->logout();
            return redirect()->route('login')->with('error', 'Profil wali tidak ditemukan.');
        }

        // Ambil santri yang menjadi anak wali
        $santriAnak = SantriProfile::where('wali_profile_id', $waliProfile->id)
            ->with(['santri', 'santriKelas.kelas'])
            ->get();
        
        $totalAnak = $santriAnak->count();

        // Statistik gabungan semua anak
        $bulanIni = now()->format('Y-m');
        $totalAbsensiHadir = 0;
        $totalAbsensiSakit = 0;
        $totalAbsensiIzin = 0;
        $totalAbsensiAlpa = 0;
        $totalPenilaian = 0;

        foreach ($santriAnak as $santri) {
            $totalAbsensiHadir += $santri->absensis()
                ->where('status', 'hadir')
                ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])
                ->count();
            $totalAbsensiSakit += $santri->absensis()
                ->where('status', 'sakit')
                ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])
                ->count();
            $totalAbsensiIzin += $santri->absensis()
                ->where('status', 'izin')
                ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])
                ->count();
            $totalAbsensiAlpa += $santri->absensis()
                ->where('status', 'alpa')
                ->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])
                ->count();
            $totalPenilaian += $santri->penilaians()->count();
        }

        // Ambil data kalender akademik bulan ini
        $currentMonth = now()->format('Y-m');
        $kalenderData = RencanaPembelajaran::forMonth($currentMonth)->get();
        
        // Format kalender untuk view (grouping by date)
        $kalenderEvents = [];
        foreach ($kalenderData as $item) {
            $from = \Carbon\Carbon::parse($item->from_date);
            $to = \Carbon\Carbon::parse($item->to_date);
            
            $current = $from->clone();
            while ($current->lte($to)) {
                $dateKey = $current->format('Y-m-d');
                if (!isset($kalenderEvents[$dateKey])) {
                    $kalenderEvents[$dateKey] = [];
                }
                $kalenderEvents[$dateKey][] = [
                    'jenis' => $item->jenis,
                    'judul' => $item->judul,
                    'catatan' => $item->catatan,
                ];
                $current->addDay();
            }
        }

        return view('Dashboard.Wali', compact(
            'waliProfile',
            'santriAnak', 
            'totalAnak',
            'totalAbsensiHadir',
            'totalAbsensiSakit',
            'totalAbsensiIzin',
            'totalAbsensiAlpa',
            'totalPenilaian',
            'kalenderEvents'
        ));
    }

    /**
     * Web/admin dashboard.
     */
    private function handleWebDashboard($user)
    {
        $totalGuru = class_exists(Guru::class) ? Guru::count() : 0;
        $totalSantri = class_exists(Santri::class) ? Santri::count() : 0;

        return view('Dashboard.Web', compact('totalGuru', 'totalSantri', 'user'));
    }

    /**
     * Daftar santri yang diajar guru (paginated).
     */
    public function guruSantri()
    {
        $user = Auth::guard('guru')->user();
        $guruProfile = $user->guruProfile ?? null;

        if (!$guruProfile) {
            return redirect()->route('guru.dashboard')->with('error', 'Profil guru tidak ditemukan.');
        }

        // mapel ids
        $mapelIds = GuruMapel::where('guru_profile_id', $guruProfile->id)->pluck('mapel_id')->unique()->values();
        $kelasMapels = Mapel::whereIn('id', $mapelIds)->pluck('kelas')->unique()->values()->all();

        $query = SantriProfile::query();
        if (!empty($kelasMapels)) {
            if (SchemaHasColumn(SantriProfile::class, 'kelas')) {
                $query->whereIn('kelas', $kelasMapels);
            }
        }

        $santris = $query->with('santri')->paginate(10);

        return view('Dashboard.GuruSantri', compact('santris'));
    }

    /**
     * Daftar wali dari santri yang diajar guru (paginated).
     */
    public function guruWali()
    {
        $user = Auth::guard('guru')->user();
        $guruProfile = $user->guruProfile ?? null;

        if (!$guruProfile) {
            return redirect()->route('guru.dashboard')->with('error', 'Profil guru tidak ditemukan.');
        }

        $mapelIds = GuruMapel::where('guru_profile_id', $guruProfile->id)->pluck('mapel_id')->unique()->values();
        $kelasMapels = Mapel::whereIn('id', $mapelIds)->pluck('kelas')->unique()->values()->all();

        $waliIds = SantriProfile::whereIn('kelas', $kelasMapels)
                                ->whereNotNull('wali_profile_id')
                                ->pluck('wali_profile_id')
                                ->unique()
                                ->values()
                                ->all();

        $walis = WaliProfile::whereIn('id', $waliIds)->paginate(10);

        return view('Dashboard.GuruWali', compact('walis'));
    }

    // ---------------------------
    // Helper functions
    // ---------------------------

    /**
     * Normalisasi active role: jika role valid kembalikan role, jika tidak dan subRoles > 1 return 'both', dsb.
     */
    private function normalizeActiveRole($activeRole, $subRoles = [])
    {
        $subRoles = is_array($subRoles) ? $subRoles : (is_null($subRoles) ? [] : (array) $subRoles);

        if ($activeRole && in_array($activeRole, $subRoles, true)) {
            return $activeRole;
        }

        if (in_array('mts', $subRoles, true) && !in_array('ma', $subRoles, true)) {
            return 'mts';
        }

        if (in_array('ma', $subRoles, true) && !in_array('mts', $subRoles, true)) {
            return 'ma';
        }

        if (count($subRoles) > 1) {
            return 'both';
        }

        return null;
    }
}

/**
 * Small helper: cek apakah model table punya kolom tertentu.
 * Menggunakan Schema facade secara ringan jika ada. 
 * Fungsi ini dibuat lokal untuk menghindari error jika Schema tidak di-import.
 */
function SchemaHasColumn(string $modelClass, string $column): bool
{
    try {
        $model = new $modelClass;
        $table = $model->getTable();
        return \Illuminate\Support\Facades\Schema::hasColumn($table, $column);
    } catch (\Throwable $e) {
        return false;
    }
}
