<?php

namespace App\Http\Controllers\Informasi;

use App\Http\Controllers\Controller;
use App\Models\User\GuruProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StrukturOrganisasiController extends Controller
{
    public function index()
    {
        // Check authorization (Kepsek/Waka only)
        $user = Auth::guard('guru')->user();
        if (!$user || !$user->guruProfile) {
             abort(403, 'Akses ditolak.');
        }

        $jabatan = strtolower($user->guruProfile->jabatan ?? '');
        $isAuthorized = $this->checkJabatan($jabatan, ['kepala sekolah', 'kepsek', 'wakil', 'waka']);

        if (!$isAuthorized) {
            abort(403, 'Hanya Kepala Sekolah dan Wakil yang dapat mengakses halaman ini.');
        }

        // Filter: Exclude jabatan = "guru" (case insensitive)
        // Only show structural positions
        $gurus = GuruProfile::whereRaw('LOWER(jabatan) != ?', ['guru'])
            ->whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->orderBy('nama')
            ->get();

        // Max limit for display on landing page
        $maxDisplayLimit = 12;
        $currentDisplayCount = GuruProfile::where('tampilkan_di_landing', true)
            ->whereRaw('LOWER(jabatan) != ?', ['guru'])
            ->count();

        return view('Informasi.StrukturOrganisasi.index', compact('gurus', 'maxDisplayLimit', 'currentDisplayCount'));
    }

    public function update(Request $request)
    {
        // Check authorization
        $user = Auth::guard('guru')->user();
        if (!$user || !$user->guruProfile) {
             return response()->json(['message' => 'Unauthorized'], 403);
        }

        $jabatan = strtolower($user->guruProfile->jabatan ?? '');
        $isAuthorized = $this->checkJabatan($jabatan, ['kepala sekolah', 'kepsek', 'wakil', 'waka']);

        if (!$isAuthorized) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'guru_id' => 'required|exists:guru_profile,id',
            'tampilkan' => 'required|boolean'
        ]);

        $guru = GuruProfile::findOrFail($validated['guru_id']);
        
        // Check limit if trying to enable
        if ($validated['tampilkan']) {
            $maxLimit = 12;
            $currentCount = GuruProfile::where('tampilkan_di_landing', true)
                ->whereRaw('LOWER(jabatan) != ?', ['guru'])
                ->where('id', '!=', $guru->id)
                ->count();
                
            if ($currentCount >= $maxLimit) {
                return response()->json([
                    'message' => "Maksimal {$maxLimit} guru dapat ditampilkan di landing page. Silakan nonaktifkan guru lain terlebih dahulu."
                ], 422);
            }
        }
        
        $guru->tampilkan_di_landing = $validated['tampilkan'];
        $guru->save();

        return response()->json(['message' => 'Status berhasil diperbarui.']);
    }

    private function checkJabatan($jabatan, array $keywords)
    {
        $jabatan = strtolower($jabatan ?? '');
        foreach ($keywords as $keyword) {
            if (str_contains($jabatan, $keyword)) {
                return true;
            }
        }
        return false;
    }
}
