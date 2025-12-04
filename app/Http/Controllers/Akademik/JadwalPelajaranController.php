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
     * Get GuruMapels based on Kelas and Mapel (for active academic year)
     */
    public function getGuruMapels(Request $request)
    {
        $kelasId = $request->kelas_id;
        $mapelId = $request->mapel_id;
        $activeYear = \App\Models\Akademik\TahunAjaran::active()->first();

        $query = GuruMapel::with('guruProfile')
            ->where('kelas_id', $kelasId)
            ->where('mapel_id', $mapelId);
        
        if ($activeYear) {
            $query->where('tahun_ajaran_id', $activeYear->id);
        }

        $guruMapels = $query->get();

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
            'jenis_kegiatan' => 'required|in:KBM,Upacara,Apel,Istirahat,Ekstrakurikuler,Lainnya',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_ke' => 'required|integer|min:1|max:12',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            // Conditional validation
            'guru_mapel_id' => 'required_if:jenis_kegiatan,KBM|nullable|exists:guru_mapel,id',
            'nama_kegiatan' => 'required_unless:jenis_kegiatan,KBM|nullable|string|max:255',
            'jenjang' => 'required_unless:jenis_kegiatan,KBM|nullable|in:SMP,SMA',
        ]);

        $activeYear = \App\Models\Akademik\TahunAjaran::active()->first();
        if (!$activeYear) {
            return response()->json(['message' => 'Tidak ada tahun ajaran aktif'], 400);
        }

        // --- LOGIC FOR KBM (CLASSROOM LEARNING) ---
        if ($validated['jenis_kegiatan'] === 'KBM') {
            $guruMapel = GuruMapel::with(['kelas', 'mapel', 'guruProfile', 'tahunAjaran'])->findOrFail($validated['guru_mapel_id']);
            
            // Check conflicts
            $this->checkConflicts($guruMapel->kelas_id, $validated['hari'], $validated['jam_mulai'], $validated['jam_selesai'], null, $guruMapel->guru_profile_id);

            $jadwal = JadwalPelajaran::create([
                'jenis_kegiatan' => 'KBM',
                'guru_mapel_id' => $validated['guru_mapel_id'],
                'kelas_id' => $guruMapel->kelas_id,
                'mapel_id' => $guruMapel->mapel_id,
                'guru_profile_id' => $guruMapel->guru_profile_id,
                'hari' => $validated['hari'],
                'jam_ke' => $validated['jam_ke'],
                'jam_mulai' => $validated['jam_mulai'],
                'jam_selesai' => $validated['jam_selesai'],
                'semester' => $activeYear->semester,
                'tahun_ajaran' => "{$activeYear->tahun_mulai}/{$activeYear->tahun_selesai}",
            ]);
        } 
        // --- LOGIC FOR NON-KBM (GLOBAL ACTIVITIES) ---
        else {
            // Check conflicts (Global activities conflict with everything in that Jenjang)
            // Note: For simplicity, we might skip strict conflict checks for global events or check against all classes in that jenjang
            
            $jadwal = JadwalPelajaran::create([
                'jenis_kegiatan' => $validated['jenis_kegiatan'],
                'nama_kegiatan' => $validated['nama_kegiatan'],
                'jenjang' => $validated['jenjang'],
                'hari' => $validated['hari'],
                'jam_ke' => $validated['jam_ke'],
                'jam_mulai' => $validated['jam_mulai'],
                'jam_selesai' => $validated['jam_selesai'],
                'semester' => $activeYear->semester,
                'tahun_ajaran' => "{$activeYear->tahun_mulai}/{$activeYear->tahun_selesai}",
            ]);
        }

        return response()->json([
            'message' => 'Jadwal berhasil ditambahkan',
            'data' => $jadwal->load(['kelas', 'mapel', 'guruProfile'])
        ], 201);
    }

    private function checkConflicts($kelasId, $hari, $jamMulai, $jamSelesai, $ignoreId = null, $guruProfileId = null)
    {
        // 1. Time Overlap in Same Class
        $timeConflict = JadwalPelajaran::where('kelas_id', $kelasId)
            ->where('hari', $hari)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->where(function($q) use ($jamMulai, $jamSelesai) {
                $q->where(function($q2) use ($jamMulai) {
                    $q2->where('jam_mulai', '<=', $jamMulai)->where('jam_selesai', '>', $jamMulai);
                })->orWhere(function($q2) use ($jamSelesai) {
                    $q2->where('jam_mulai', '<', $jamSelesai)->where('jam_selesai', '>=', $jamSelesai);
                })->orWhere(function($q2) use ($jamMulai, $jamSelesai) {
                    $q2->where('jam_mulai', '>=', $jamMulai)->where('jam_selesai', '<=', $jamSelesai);
                });
            })->exists();

        if ($timeConflict) {
            throw new \Exception('Bentrok Waktu! Ada jadwal lain di kelas ini yang waktunya bertabrakan.');
        }

        // 2. Teacher Conflict
        if ($guruProfileId) {
            $teacherConflict = JadwalPelajaran::where('guru_profile_id', $guruProfileId)
                ->where('hari', $hari)
                ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
                ->where(function($q) use ($jamMulai, $jamSelesai) {
                    $q->where(function($q2) use ($jamMulai) {
                        $q2->where('jam_mulai', '<=', $jamMulai)->where('jam_selesai', '>', $jamMulai);
                    })->orWhere(function($q2) use ($jamSelesai) {
                        $q2->where('jam_mulai', '<', $jamSelesai)->where('jam_selesai', '>=', $jamSelesai);
                    })->orWhere(function($q2) use ($jamMulai, $jamSelesai) {
                        $q2->where('jam_mulai', '>=', $jamMulai)->where('jam_selesai', '<=', $jamSelesai);
                    });
                })->exists();

            if ($teacherConflict) {
                throw new \Exception('Bentrok Guru! Guru ini sudah mengajar di kelas lain pada waktu yang bertabrakan.');
            }
        }
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

        try {
            // Only check conflicts if it's a KBM activity or if we want to enforce it for everything
            if ($jadwalPelajaran->jenis_kegiatan === 'KBM') {
                $this->checkConflicts($jadwalPelajaran->kelas_id, $validated['hari'], $validated['jam_mulai'], $validated['jam_selesai'], $jadwalPelajaran->id, $jadwalPelajaran->guru_profile_id);
            }
            
            $jadwalPelajaran->update($validated);

            return response()->json([
                'message' => 'Jadwal berhasil diperbarui',
                'data' => $jadwalPelajaran->load(['kelas', 'mapel', 'guruProfile'])
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
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
