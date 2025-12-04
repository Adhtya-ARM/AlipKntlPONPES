<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Akademik\TahunAjaran;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Absensi;
use App\Models\Akademik\Penilaian;
use App\Models\User\SantriKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * GuruArsipController
 * 
 * Handles teacher-facing archive views for historical teaching data.
 * Provides READ-ONLY access to archived semesters where the teacher taught.
 */
class GuruArsipController extends Controller
{
    /**
     * Display list of archived semesters where this teacher taught
     */
    public function index()
    {
        $guru = Auth::guard('guru')->user();
        
        if (!$guru) {
            return back()->with('error', 'Profil guru tidak ditemukan.');
        }

        // Get all archived semesters where this teacher has GuruMapel records
        // Using whereHas to ensure we only show semesters where the teacher actually taught
        $archivedSemesters = TahunAjaran::archived()
            ->whereHas('guruMapels', function ($query) use ($guru) {
                $query->where('guru_profile_id', $guru->id);
            })
            ->withCount(['guruMapels' => function ($query) use ($guru) {
                $query->where('guru_profile_id', $guru->id);
            }])
            ->orderBy('nama', 'desc') // Order by semester name (e.g., "2025" before "2024")
            ->orderBy('semester', 'desc') // Then by semester type
            ->get();

        return view('User.Guru.Arsip.index', compact('archivedSemesters'));
    }

    /**
     * Show details of a specific archived semester for this teacher
     */
    public function show($semesterId)
    {
        $guru = Auth::guard('guru')->user();
        
        if (!$guru) {
            return back()->with('error', 'Profil guru tidak ditemukan.');
        }

        // Get the archived semester
        $semester = TahunAjaran::archived()->findOrFail($semesterId);

        // Get all GuruMapel for this teacher in this archived semester
        // Eager load all necessary relationships to prevent N+1 queries
        $guruMapels = GuruMapel::where('guru_profile_id', $guru->id)
            ->where('tahun_ajaran_id', $semester->id)
            ->with(['kelas', 'kelas.waliKelas', 'mapel', 'tahunAjaran'])
            ->get();

        // Check authorization - teacher can only view their own archive
        if ($guruMapels->isEmpty()) {
            return redirect()->route('akademik.guru.arsip.index')
                ->with('warning', 'Anda tidak memiliki data pembelajaran untuk semester ini.');
        }

        return view('User.Guru.Arsip.show', compact('semester', 'guruMapels'));
    }

    /**
     * Show detailed view of a specific archived class/subject
     */
    public function detail($guruMapelId)
    {
        $guru = Auth::guard('guru')->user();
        
        if (!$guru) {
            return back()->with('error', 'Profil guru tidak ditemukan.');
        }

        // Get the GuruMapel record with relationships
        $guruMapel = GuruMapel::with([
            'kelas',
            'mapel',
            'tahunAjaran',
            'penilaians.santriProfile.santri',
        ])->findOrFail($guruMapelId);

        // Authorization check
        if ($guruMapel->guru_profile_id !== $guru->id) {
            abort(403, 'Anda tidak memiliki akses ke data ini.');
        }

        // Ensure semester is archived
        if (!$guruMapel->tahunAjaran || !$guruMapel->tahunAjaran->isArchived()) {
            return redirect()->route('akademik.guru-mapel.index')
                ->with('info', 'Data ini bukan dari semester terarsip. Silakan akses melalui menu pembelajaran aktif.');
        }

        // Get students who were in this class during this semester
        $santris = SantriKelas::where('kelas_id', $guruMapel->kelas_id)
            ->where('tahun_ajaran_id', $guruMapel->tahun_ajaran_id)
            ->with('santriProfile.santri')
            ->get()
            ->map(function ($sk) {
                return $sk->santriProfile;
            })
            ->filter();

        // Get attendance records for this class/subject/semester
        $absensiRecords = Absensi::where('kelas_id', $guruMapel->kelas_id)
            ->where('mapel_id', $guruMapel->mapel_id)
            ->where('tahun_ajaran_id', $guruMapel->tahun_ajaran_id)
            ->with('santriProfile.santri')
            ->orderBy('tanggal', 'desc')
            ->get();

        // Group attendance by date
        $absensiByDate = $absensiRecords->groupBy('tanggal');

        // Get grades for this GuruMapel
        $penilaianRecords = Penilaian::where('guru_mapel_id', $guruMapel->id)
            ->with('santriProfile.santri')
            ->get();

        // Group grades by type
        $penilaianByType = $penilaianRecords->groupBy('jenis_penilaian');

        return view('User.Guru.Arsip.detail', compact(
            'guruMapel',
            'santris',
            'absensiByDate',
            'penilaianByType'
        ));
    }
}
