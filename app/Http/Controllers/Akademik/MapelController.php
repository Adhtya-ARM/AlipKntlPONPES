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
        // Ambil semua mapel dan group secara manual
        $kelompokMapels = [
            [
                'id' => 'smp_1',
                'nama' => 'Kelompok SMP',
                'jenis' => 'smp',
                'mapels' => Mapel::where('tingkat', 'like', '%7%')
                    ->orWhere('tingkat', 'like', '%8%')
                    ->orWhere('tingkat', 'like', '%9%')
                    ->get()
                    ->toArray()
            ],
            [
                'id' => 'sma_1',
                'nama' => 'Kelompok SMA',
                'jenis' => 'sma',
                'mapels' => Mapel::where('tingkat', 'like', '%10%')
                    ->orWhere('tingkat', 'like', '%11%')
                    ->orWhere('tingkat', 'like', '%12%')
                    ->get()
                    ->toArray()
            ]
        ];

        return view('akademik.mapel.index', compact('kelompokMapels'));
    }

    /**
     * Simpan Mapel baru
     */
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
