<?php

namespace App\Http\Controllers\Akademik;

use App\Models\Akademik\Absensi;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GuruProfile;
use App\Models\SantriProfile;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Mapel;

class AbsensiController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $guard = $this->getGuardName();

        if ($guard === 'guru') {
            $guruProfile = $user->guruProfile;
            if (!$guruProfile) {
                return redirect()->back()->with('error', 'Profile guru tidak ditemukan');
            }

            $guruMapels = GuruMapel::with(['mapel'])
                ->where('guru_profile_id', $guruProfile->id)
                ->get();

            return view('absensi.index', compact('guruMapels'));
        }

        // Jika santri yang login
        if ($guard === 'santri') {
            $santriProfile = $user->santriProfile;
            if (!$santriProfile) {
                return redirect()->back()->with('error', 'Profile santri tidak ditemukan');
            }

            $absensis = Absensi::with(['mapel', 'guruProfile'])
                ->where('santri_profile_id', $santriProfile->id)
                ->get()
                ->groupBy('mapel_id');

            return view('absensi.santri-index', compact('absensis'));
        }

        return redirect()->back()->with('error', 'Unauthorized');
    }

    private function getGuardName()
    {
        foreach (['web', 'guru', 'santri', 'wali'] as $guard) {
            if (auth()->guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function getSiswaList($guruMapelId)
    {
        $guruMapel = GuruMapel::with('mapel')->findOrFail($guruMapelId);
        $kelas = $guruMapel->mapel->kelas;
        
        // Get all santri in this class
        $santriList = SantriProfile::where('kelas', $kelas)
            ->select('id', 'nama')
            ->get()
            ->map(function($santri) use ($guruMapel) {
                // Get total absences for this santri
                $totalAbsen = Absensi::where('santri_profile_id', $santri->id)
                    ->where('mapel_id', $guruMapel->mapel_id)
                    ->where('status', 'alpha')
                    ->count();

                // Get total attendance
                $totalKehadiran = Absensi::where('santri_profile_id', $santri->id)
                    ->where('mapel_id', $guruMapel->mapel_id)
                    ->where('status', 'hadir')
                    ->count();

                return [
                    'id' => $santri->id,
                    'nama' => $santri->nama,
                    'status' => $totalAbsen >= 7 ? 'X' : 'hadir',
                    'total_absen' => $totalAbsen,
                    'total_kehadiran' => $totalKehadiran,
                    'keterangan' => ''
                ];
            });

        // Get max pertemuan from existing absensi records
        $maxPertemuan = Absensi::where('mapel_id', $guruMapel->mapel_id)
            ->where('guru_profile_id', $guruMapel->guru_profile_id)
            ->max('pertemuan_ke') ?? 16; // Default to 16 if no records

        return response()->json([
            'santri' => $santriList,
            'maxPertemuan' => $maxPertemuan
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'guru_mapel_id' => 'required|exists:guru_mapel,id',
            'pertemuan_ke' => 'required|integer|min:1',
            'absensi' => 'required|array',
            'absensi.*.id' => 'required|exists:santri_profile,id',
            'absensi.*.status' => 'required|in:hadir,sakit,izin,alpha,X',
            'absensi.*.keterangan' => 'nullable|string'
        ]);

        $guruMapel = GuruMapel::findOrFail($request->guru_mapel_id);

        foreach ($request->absensi as $data) {
            Absensi::updateOrCreate(
                [
                    'santri_profile_id' => $data['id'],
                    'mapel_id' => $guruMapel->mapel_id,
                    'guru_profile_id' => $guruMapel->guru_profile_id,
                    'pertemuan_ke' => $request->pertemuan_ke
                ],
                [
                    'status' => $data['status'],
                    'keterangan' => $data['keterangan'] ?? null
                ]
            );
        }

        return response()->json(['message' => 'Absensi berhasil disimpan']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $absensi = Absensi::findOrFail($id);

        $data = $request->validate([
            'guru_profile_id' => 'required|integer|exists:guru_profile,id',
            'mapel_id' => 'required|integer|exists:mapel,id',
            'jumlah_pertemuan' => 'required|integer|min:0',
            'jumlah_bab' => 'required|integer|min:0',
            'keterangan' => 'nullable|string'
        ]);

        $absensi->update($data);

        if ($request->wantsJson()) {
            return response()->json(['data' => $absensi]);
        }

        return redirect()->back()->with('success', 'Absensi berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Dihapus']);
        }

        return redirect()->back()->with('success', 'Absensi berhasil dihapus.');
    }
}