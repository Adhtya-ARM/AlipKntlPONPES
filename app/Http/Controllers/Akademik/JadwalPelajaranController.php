<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Akademik\JadwalPelajaran;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\Mapel;
use App\Models\User\GuruProfile;

class JadwalPelajaranController extends Controller
{
    /**
     * Cek apakah user adalah Kepsek atau Waka berdasarkan jabatan di guru_profile
     */
    private function checkAuthorization()
    {
        $user = Auth::guard('guru')->user();
        
        // Get guru profile
        if ($user instanceof GuruProfile) {
            $guruProfile = $user;
        } elseif (method_exists($user, 'guruProfile')) {
            $guruProfile = $user->guruProfile;
        } else {
            return false;
        }
        
        if (!$guruProfile) {
            return false;
        }
        
        // Cek jabatan dari kolom guru_profile.jabatan (case-insensitive)
        $jabatan = strtolower(trim($guruProfile->jabatan ?? ''));
        $allowedJabatan = ['kepala sekolah', 'wakil kepala sekolah', 'waka', 'kepsek'];
        
        return in_array($jabatan, $allowedJabatan);
    }

    /**
     * Display list jadwal pelajaran (Kepsek/Waka only)
     */
    public function index()
    {
        if (!$this->checkAuthorization()) {
            return redirect()->route('guru.dashboard')
                ->with('error', 'Akses ditolak. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengakses halaman ini.');
        }

        $jadwals = JadwalPelajaran::with(['kelas', 'mapel', 'guruProfile'])
            ->orderBy('hari')
            ->orderBy('jam_ke')
            ->get();

        // Group by hari dan kelas untuk tampilan yang lebih rapi
        $jadwalGrouped = $jadwals->groupBy('hari');

        // Get data untuk form
        $kelas = Kelas::orderBy('level')->get();
        $mapels = Mapel::orderBy('nama_mapel')->get();
        $guruProfiles = GuruProfile::orderBy('nama')->get();

        return view('Akademik.JadwalPelajaran.index', compact('jadwalGrouped', 'kelas', 'mapels', 'guruProfiles'));
    }

    /**
     * Get GuruMapels based on Kelas and Mapel
     */
    public function getGuruMapels(Request $request)
    {
        $kelasId = $request->kelas_id;
        $mapelId = $request->mapel_id;

        $guruMapels = GuruMapel::with('guruProfile')
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId)
            ->get();

        return response()->json($guruMapels);
    }

    /**
     * Store new jadwal
     */
    public function store(Request $request)
    {
        if (!$this->checkAuthorization()) {
            return response()->json([
                'message' => 'Unauthorized. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengakses fitur ini.'
            ], 403);
        }

        $validated = $request->validate([
            'guru_mapel_id' => 'required|exists:guru_mapel,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:1|max:12',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        // Get GuruMapel data
        $guruMapel = GuruMapel::with(['kelas', 'mapel', 'guruProfile'])->findOrFail($validated['guru_mapel_id']);

        // Check for conflicts (same class, day, period)
        $conflict = JadwalPelajaran::where('kelas_id', $guruMapel->kelas_id)
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Bentrok! Kelas ini sudah ada jadwal di hari dan jam yang sama.'
            ], 400);
        }

        // Check for teacher conflicts
        $teacherConflict = JadwalPelajaran::where('guru_profile_id', $guruMapel->guru_profile_id)
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->exists();

        if ($teacherConflict) {
            return response()->json([
                'message' => 'Bentrok! Guru ini sudah mengajar di kelas lain pada hari dan jam yang sama.'
            ], 400);
        }

        $jadwal = JadwalPelajaran::create([
            'guru_mapel_id' => $validated['guru_mapel_id'],
            'kelas_id' => $guruMapel->kelas_id,
            'mapel_id' => $guruMapel->mapel_id,
            'guru_profile_id' => $guruMapel->guru_profile_id,
            'hari' => $validated['hari'],
            'jam_ke' => $validated['jam_ke'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'semester' => $guruMapel->semester,
            'tahun_ajaran' => $guruMapel->tahun_ajaran,
        ]);

        return response()->json([
            'message' => 'Jadwal berhasil ditambahkan',
            'data' => $jadwal->load(['kelas', 'mapel', 'guruProfile'])
        ], 201);
    }

    /**
     * Update jadwal
     */
    public function update(Request $request, JadwalPelajaran $jadwalPelajaran)
    {
        if (!$this->checkAuthorization()) {
            return response()->json([
                'message' => 'Unauthorized. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengakses fitur ini.'
            ], 403);
        }

        $validated = $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:1|max:12',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
        ]);

        // Check conflicts (excluding current jadwal)
        $conflict = JadwalPelajaran::where('kelas_id', $jadwalPelajaran->kelas_id)
            ->where('hari', $validated['hari'])
            ->where('jam_ke', $validated['jam_ke'])
            ->where('id', '!=', $jadwalPelajaran->id)
            ->exists();

        if ($conflict) {
            return response()->json([
                'message' => 'Bentrok! Sudah ada jadwal di hari dan jam yang sama.'
            ], 400);
        }

        $jadwalPelajaran->update($validated);

        return response()->json([
            'message' => 'Jadwal berhasil diperbarui',
            'data' => $jadwalPelajaran->load(['kelas', 'mapel', 'guruProfile'])
        ]);
    }

    /**
     * Delete jadwal
     */
    public function destroy(JadwalPelajaran $jadwalPelajaran)
    {
        if (!$this->checkAuthorization()) {
            return response()->json([
                'message' => 'Unauthorized. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengakses fitur ini.'
            ], 403);
        }

        $jadwalPelajaran->delete();

        return response()->json([
            'message' => 'Jadwal berhasil dihapus'
        ]);
    }

    /**
     * Get jadwal for specific day (for preview/grid view)
     */
    public function getByDay($hari)
    {
        if (!$this->checkAuthorization()) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 403);
        }

        $jadwals = JadwalPelajaran::with(['kelas', 'mapel', 'guruProfile'])
            ->where('hari', $hari)
            ->orderBy('jam_ke')
            ->orderBy('kelas_id')
            ->get();

        return response()->json($jadwals);
    }
}
