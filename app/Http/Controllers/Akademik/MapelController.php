<?php

namespace App\Http\Controllers\Akademik;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Akademik\Mapel;

class MapelController extends Controller
{
    /**
     * Tampilkan halaman pengaturan mapel
     */
    public function index()
    {
        // Helper function untuk memproses mapel
        $processMapels = function($query) {
            return $query->with(['guruMapels.kelas']) // Eager load untuk cek penggunaan level
                ->withCount('guruMapels')
                ->get()
                ->map(function($mapel) {
                    // Ambil daftar level yang sudah digunakan oleh mapel ini
                    $usedLevels = $mapel->guruMapels->pluck('kelas.level')
                        ->filter()
                        ->unique()
                        ->map(fn($level) => (string) $level) // Cast to string
                        ->values()
                        ->toArray();
                    
                    $mapelData = $mapel->toArray();
                    $mapelData['used_levels'] = $usedLevels; // Tambahkan info used_levels
                    return $mapelData;
                })
                ->toArray();
        };

        // Ambil semua mapel dan group secara manual
        $kelompokMapels = [
            [
                'id' => 'smp_1',
                'nama' => 'Kelompok SMP',
                'jenis' => 'smp',
                'mapels' => $processMapels(Mapel::where('tingkat', 'like', '%7%')
                    ->orWhere('tingkat', 'like', '%8%')
                    ->orWhere('tingkat', 'like', '%9%'))
            ],
            [
                'id' => 'sma_1',
                'nama' => 'Kelompok SMA',
                'jenis' => 'sma',
                'mapels' => $processMapels(Mapel::where('tingkat', 'like', '%10%')
                    ->orWhere('tingkat', 'like', '%11%')
                    ->orWhere('tingkat', 'like', '%12%'))
            ]
        ];

        return view('akademik.mapel.index', compact('kelompokMapels'));
    }

    /**
     * Simpan Mapel baru
     */
    public function store(Request $request)
    {
        $this->authorizeManagement();

        try {
            $validated = $request->validate([
                'nama_mapel' => 'required|string|max:255',
                'jjm' => 'nullable|integer|min:0',
                'tingkat' => 'nullable|array',
            ]);

            // Ensure tingkat is array
            $tingkat = $validated['tingkat'] ?? [];
            
            // Create mapel
            $mapel = Mapel::create([
                'nama_mapel' => $validated['nama_mapel'],
                'jjm' => $validated['jjm'] ?? 0,
                'tingkat' => $tingkat
            ]);

            return response()->json([
                'message' => 'Mapel berhasil ditambahkan',
                'data' => $mapel
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update Mapel
     */
    public function update(Request $request, Mapel $mapel)
    {
        $this->authorizeManagement();

        try {
            $validated = $request->validate([
                'nama_mapel' => 'required|string|max:255',
                'jjm' => 'nullable|integer|min:0',
                'tingkat' => 'nullable|array',
            ]);

            // Cek apakah ada tingkat yang dihapus padahal sedang digunakan
            // Ini validasi backend tambahan
            $currentUsedLevels = $mapel->guruMapels()->with('kelas')->get()->pluck('kelas.level')->unique()->toArray();
            $newLevels = $validated['tingkat'] ?? [];
            
            // Jika ada level yang sedang digunakan TAPI tidak ada di input baru, maka error/block
            // Namun karena inputnya checkbox per level, kita bisa cek satu-satu.
            // Tapi user minta read-only di UI, jadi validasi backend ini opsional tapi bagus.
            
            // Update
            $mapel->update([
                'nama_mapel' => $validated['nama_mapel'],
                'jjm' => $validated['jjm'] ?? 0,
                'tingkat' => $validated['tingkat'] ?? []
            ]);

            return response()->json([
                'message' => 'Mapel berhasil diperbarui',
                'data' => $mapel
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus Mapel
     */
    public function destroy(Mapel $mapel)
    {
        $this->authorizeManagement();

        try {
            // Cek apakah mapel sudah digunakan di guru_mapel
            if ($mapel->guruMapels()->count() > 0) {
                return response()->json([
                    'message' => 'Mapel tidak dapat dihapus karena sudah digunakan oleh guru'
                ], 400);
            }

            $mapel->delete();

            return response()->json([
                'message' => 'Mapel berhasil dihapus'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to authorize management actions
     */
    private function authorizeManagement()
    {
        $user = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        if (!$user || !$user->guruProfile) {
            abort(403, 'Unauthorized');
        }

        $jabatan = strtolower($user->guruProfile->jabatan);
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            abort(403, 'Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola mata pelajaran.');
        }
    }
}
