<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Penilaian;
use App\Models\Akademik\Mapel;
use App\Models\User\SantriProfile;
use App\Models\User\GuruProfile;
use App\Models\Akademik\GuruMapel; // Model untuk tabel pivot guru_mapel

use Spatie\PdfToText\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class PenilaianController extends Controller
{
    /**
     * Helper untuk mendapatkan user yang sedang aktif dan guard-nya.
     */
    protected function getAuthenticatedUserAndGuard()
    {
        // Guard yang diizinkan untuk PenilaianController (sesuai route: web, guru)
        $allowedGuards = ["web", "guru"];

        foreach ($allowedGuards as $guardName) {
            if (Auth::guard($guardName)->check()) {
                return [
                    "user" => Auth::guard($guardName)->user(),
                    "guard" => $guardName, // Akan mengembalikan 'web' atau 'guru'
                ];
            }
        }
        return ["user" => null, "guard" => null];
    }

    /**
     * Menampilkan daftar santri dan nilai yang relevan (dengan filter guru).
     */
    public function index(Request $request)
    {
        $authData = $this->getAuthenticatedUserAndGuard();
        $user = $authData["user"];
        $userRole = $authData["guard"];
        
        // 1. Otorisasi dan Penentuan Mata Pelajaran
        $mapelIdsTampil = collect([]);
        $currentMapel = null;

        if ($userRole === 'guru' && $user && $user->guruProfile) {
            $guruProfileId = $user->guruProfile->id;
            
            // 🌟 REVISI 1: Ambil semua Mapel ID dari tabel pivot guru_mapel
            $mapelIdsDiajar = GuruMapel::where('guru_profile_id', $guruProfileId)
                                            ->pluck('mapel_id');
            
            // Ambil detail Mapel PERTAMA yang diajar (untuk menentukan kelas default)
            $currentMapel = Mapel::whereIn('id', $mapelIdsDiajar)->first();

            if ($currentMapel) {
                // HANYA ambil ID Mapel PERTAMA untuk ditampilkan di halaman index ini
                // (Diasumsikan guru hanya menilai SATU Mapel dalam satu tampilan)
                $mapelIdsTampil = collect([$currentMapel->id]); 
            }
        } 
        
        // Jika bukan guru atau guru tidak terikat mapel, kembalikan tampilan kosong
        if ($mapelIdsTampil->isEmpty() && $userRole === 'guru') {
            return view("akademik.penilaian.index", [
                "santriProfiles" => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]), 
                "penilaians" => collect([]), 
                "currentKelas" => $request->input("kelas", "7"), // Default ke kelas umum '7'
                "mapelIdsTampil" => null, // Tandai bahwa tidak ada mapel yang dipilih/ditampilkan
            ])->with('error', 'Anda belum terikat pada mata pelajaran manapun.');
        }

        // 2. Ambil filter dari request
        // Gunakan kelas dari request, atau default ke kelas Mapel yang diajar (misal: '7')
        $defaultKelas = $currentMapel ? $currentMapel->kelas : '7';
        $currentKelasGeneral = $request->input("kelas", $defaultKelas); // Nilainya adalah '7'

        // 🌟 REVISI 2: Buat Filter LIKE untuk mencakup kelas spesifik (7A, 7B, dst.)
        $kelasFilter = $currentKelasGeneral . '%'; // Menghasilkan '7%' untuk kelas 7

        // 3. Ambil ID SantriProfile yang sesuai dengan filter Kelas
        $santriProfiles = SantriProfile::query()
            ->with("santri:id,nis,username")
            // 🌟 REVISI 3: Ganti where('kelas', ...) menjadi where('kelas', 'LIKE', ...)
            ->where("kelas", 'LIKE', $kelasFilter) 
            ->orderBy("nama")
            ->paginate(10);

        $profileIds = $santriProfiles->pluck("id");

        // 4. Ambil semua Penilaian yang relevan (Filter berdasarkan Santri dan Mapel)
        // Kita hanya mengambil nilai untuk SATU Mapel ID ($mapelIdsTampil->first())
        $penilaiansData = Penilaian::whereIn("santri_profile_id", $profileIds)
            ->whereIn("mapel_id", $mapelIdsTampil) // Hanya mapel yang diajar guru
            // Jika Anda memiliki semester/tahun ajaran, tambahkan di sini:
            // ->where('tahun_ajaran', $currentTahunAjaran)
            // ->where('semester', $currentSemester) 
            ->get();

        // 5. Transformasi data Penilaian menjadi pivot array: [santri_profile_id => Penilaian Object]
        $penilaians = $penilaiansData->keyBy('santri_profile_id');

        // 6. Tampilkan View
        return view("akademik.penilaian.index", [
            "santriProfiles" => $santriProfiles, // Data SantriProfile (Paginasi)
            "penilaians" => $penilaians, // Data nilai per santri
            // Kirim kelas general (misal: '7') untuk navigasi dan tampilan
            "currentKelas" => $currentKelasGeneral,
            "mapelIdsTampil" => $mapelIdsTampil->first(), // Kirim Mapel ID tunggal
            "mapelSaatIni" => $currentMapel, // Kirim objek Mapel untuk tampilan
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'santri_profile_id' => 'required|exists:santri_profiles,id',
            'mapel_id' => 'required|exists:mapels,id',
            'nilai_harian' => 'nullable|numeric|min:0|max:100',
            'nilai_uts' => 'nullable|numeric|min:0|max:100',
            'nilai_uas' => 'nullable|numeric|min:0|max:100',
            'bab1' => 'nullable|numeric|min:0|max:100',
            'bab2' => 'nullable|numeric|min:0|max:100',
            'bab3' => 'nullable|numeric|min:0|max:100',
            'bab4' => 'nullable|numeric|min:0|max:100',
            'bab5' => 'nullable|numeric|min:0|max:100',
            'catatan' => 'nullable|string|max:255',
        ]);

        $authData = $this->getAuthenticatedUserAndGuard();
        $guruProfileId = $authData['guard'] === 'guru' ? $authData['user']->guruProfile->id : null;

        Penilaian::updateOrCreate(
            [
                'santri_profile_id' => $request->santri_profile_id,
                'mapel_id' => $request->mapel_id,
            ],
            [
                'nilai' => $request->nilai_harian,
                'uts' => $request->nilai_uts,
                'uas' => $request->nilai_uas,
                'bab1' => $request->bab1,
                'bab2' => $request->bab2,
                'bab3' => $request->bab3,
                'bab4' => $request->bab4,
                'bab5' => $request->bab5,
                'catatan' => $request->catatan,
                'guru_profile_id' => $guruProfileId,
                'kelas' => $request->kelas,
            ]
        );

        return redirect()->back()->with('success', 'Nilai santri berhasil disimpan/diperbarui.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $penilaian = Penilaian::findOrFail($id);

        $request->validate([
            'nilai_harian' => 'nullable|numeric|min:0|max:100',
            'nilai_uts' => 'nullable|numeric|min:0|max:100',
            'nilai_uas' => 'nullable|numeric|min:0|max:100',
            'bab1' => 'nullable|numeric|min:0|max:100',
            'bab2' => 'nullable|numeric|min:0|max:100',
            'bab3' => 'nullable|numeric|min:0|max:100',
            'bab4' => 'nullable|numeric|min:0|max:100',
            'bab5' => 'nullable|numeric|min:0|max:100',
            'catatan' => 'nullable|string|max:255',
        ]);

        $penilaian->update([
            'nilai' => $request->nilai_harian,
            'uts' => $request->nilai_uts,
            'uas' => $request->nilai_uas,
            'bab1' => $request->bab1,
            'bab2' => $request->bab2,
            'bab3' => $request->bab3,
            'bab4' => $request->bab4,
            'bab5' => $request->bab5,
            'catatan' => $request->catatan,
        ]);

        return redirect()->back()->with('success', 'Nilai santri berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penilaian $penilaian)
    {
        // Logika destroy dipertahankan seperti aslinya
        $penilaian->delete();
        return redirect()->back()->with('success', 'Nilai berhasil dihapus.');
    }

    /**
     * Upload and process PDF for nilai import.
     */
    public function uploadAndProcessPdf(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:10240', // Max 10MB
            'mapel_id_upload' => 'required|exists:mapels,id',
            'kelas_upload' => 'required|string',
        ]);

        $authData = $this->getAuthenticatedUserAndGuard();
        $guruProfileId = $authData['guard'] === 'guru' ? $authData['user']->guruProfile->id : null;

        // Store the uploaded PDF
        $pdfPath = $request->file('pdf_file')->store('uploads/pdf_nilai', 'public');

        // Extract text from PDF using Spatie\PdfToText
        try {
            $pdfText = Pdf::getText(storage_path('app/public/' . $pdfPath));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membaca PDF: ' . $e->getMessage());
        }

        // Simple parsing logic (assuming format: NIS: nilai_harian, uts, uas)
        // This is a basic example; adjust based on actual PDF format
        $lines = explode("\n", $pdfText);
        $processedCount = 0;

        foreach ($lines as $line) {
            // Example line: "12345: 85, 78, 92"
            if (preg_match('/(\d+):\s*(\d+),\s*(\d+),\s*(\d+)/', $line, $matches)) {
                $nis = $matches[1];
                $nilai_harian = $matches[2];
                $uts = $matches[3];
                $uas = $matches[4];

                // Find santri by NIS
                $santriProfile = SantriProfile::whereHas('santri', function($q) use ($nis) {
                    $q->where('nis', $nis);
                })->first();

                if ($santriProfile) {
                    Penilaian::updateOrCreate(
                        [
                            'santri_profile_id' => $santriProfile->id,
                            'mapel_id' => $request->mapel_id_upload,
                        ],
                        [
                            'nilai' => $nilai_harian,
                            'uts' => $uts,
                            'uas' => $uas,
                            'guru_profile_id' => $guruProfileId,
                            'kelas' => $request->kelas_upload,
                        ]
                    );
                    $processedCount++;
                }
            }
        }

        return redirect()->back()->with('success', "Berhasil memproses {$processedCount} nilai dari PDF.");
    }
}