<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Penilaian;
use App\Models\Akademik\Mapel;
use App\Models\Akademik\Absensi;
use App\Models\User\SantriProfile;
use App\Models\User\GuruProfile;
use App\Models\Akademik\GuruMapel; // Model untuk tabel pivot guru_mapel

use Spatie\PdfToText\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\Rule; // Pastikan ini ada jika menggunakan Rule::unique

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
             
             $mapelIdsDiajar = GuruMapel::where('guru_profile_id', $guruProfileId)
                                             ->pluck('mapel_id');
             
             $currentMapel = Mapel::whereIn('id', $mapelIdsDiajar)->first();
     
             if ($currentMapel) {
                 $mapelIdsTampil = collect([$currentMapel->id]); 
             }
         } 
         
         // Jika bukan guru ATAU guru tidak terikat mapel, kembalikan tampilan kosong
         // DAN jika user adalah guru tetapi mapelIdsTampil kosong
         if (($mapelIdsTampil->isEmpty() && $userRole === 'guru') || ($userRole === 'guru' && !$currentMapel)) {
             return view("akademik.penilaian.index", [
                 "santriProfiles" => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]), 
                 "penilaians" => collect([]), 
                 "currentKelas" => $request->input("kelas", "7"),
                 "mapelIdsTampil" => null, 
                 "mapelSaatIni" => null, 
             ])->with('error', 'Anda belum terikat pada mata pelajaran manapun atau mata pelajaran yang terikat tidak ditemukan.');
         }
         
        // Pastikan Mapel ID tersedia setelah logic di atas
        $mapelId = $mapelIdsTampil->first();
        if (!$mapelId) { // Ini seharusnya sudah ditangani oleh kondisi di atas, tapi bisa jadi fallback
            return view("akademik.penilaian.index", [
                "santriProfiles" => new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10, 1, ['path' => $request->url()]), 
                "penilaians" => collect([]), 
                "currentKelas" => $request->input("kelas", "7"), 
                "mapelIdsTampil" => null, 
                "mapelSaatIni" => null, 
            ])->with('error', 'Data mata pelajaran tidak ditemukan atau Anda tidak memiliki akses.');
        }

        // 2. Ambil data Mapel saat ini dan cek apakah ada Absensi (pertemuan) terkait guru+mapel
        $mapelSaatIni = Mapel::find($mapelId);

        // Jika role guru, cari Absensi untuk guru_profile yang sedang login
        $jumlahBabDefault = $mapelSaatIni->jumlah_bab ?? 0;
        if ($userRole === 'guru' && isset($guruProfileId)) {
            $absensiRecord = Absensi::where('mapel_id', $mapelId)
                ->where('guru_profile_id', $guruProfileId)
                ->first();

            if ($absensiRecord) {
                // Overwrite jumlah_bab pada objek mapel untuk UI jika Absensi menyediakan nilai
                $mapelSaatIni->jumlah_bab = $absensiRecord->jumlah_bab ?? $mapelSaatIni->jumlah_bab ?? 0;
                // Lampirkan juga jumlah_pertemuan ke objek mapel (jika diperlukan di view)
                $mapelSaatIni->jumlah_pertemuan = $absensiRecord->jumlah_pertemuan ?? 0;
            }
        }

        // 3. Ambil daftar santri untuk kelas yang diminta dan nilai penilaian terkait
        $currentKelas = $request->input('kelas', '7');
        $perPage = 20;
        $santriProfiles = SantriProfile::where('kelas', $currentKelas)->paginate($perPage);

        // Ambil semua penilaian untuk santri yang tampil di halaman ini untuk mapel yang sedang dipilih
        $penilaianRows = Penilaian::where('mapel_id', $mapelId)
            ->whereIn('santri_profile_id', $santriProfiles->pluck('id')->toArray())
            ->get()
            ->keyBy('santri_profile_id');

        return view('akademik.penilaian.index', [
            'santriProfiles' => $santriProfiles,
            'penilaians' => $penilaianRows,
            'currentKelas' => $currentKelas,
            'mapelIdsTampil' => $mapelIdsTampil,
            'mapelSaatIni' => $mapelSaatIni,
        ]);
     }

    /**
     * Store a newly created resource in storage.
     */
    public function getSantriList($guruMapelId)
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

        $absensi = Absensi::firstOrCreate(
            [
                'mapel_id' => $guruMapel->mapel_id,
                'guru_profile_id' => $guruMapel->guru_profile_id
            ],
            [
                'jumlah_pertemuan' => 16, // Default value
                'jumlah_bab' => 8 // Default value
            ]
        );

        return response()->json([
            'santri' => $santriList,
            'maxPertemuan' => $absensi->jumlah_pertemuan
        ]);
    }

    public function storeAbsensi(Request $request, $guruMapelId)
    {
        $request->validate([
            'pertemuan_ke' => 'required|integer|min:1',
            'absensi' => 'required|array',
            'absensi.*.id' => 'required|exists:santri_profile,id',
            'absensi.*.status' => 'required|in:hadir,sakit,izin,alpha,X',
            'absensi.*.keterangan' => 'nullable|string'
        ]);

        $guruMapel = GuruMapel::findOrFail($guruMapelId);

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

    public function store(Request $request)
    {
        // 1. Validasi Input Dasar
        $rules = [
            'santri_profile_id' => 'required|exists:santri_profiles,id',
            'mapel_id' => 'required|exists:mapels,id',
            'nilai_uts' => 'nullable|numeric|min:0|max:100',
            'nilai_uas' => 'nullable|numeric|min:0|max:100',
            // 'catatan' => 'nullable|string|max:255', // CATATAN DIHAPUS
            'kelas' => 'required|string',
        ];
        
        // Ambil data mapel untuk menentukan jumlah bab
        $mapel = Mapel::findOrFail($request->mapel_id);
        $jumlahBab = $mapel->jumlah_bab ?? 0;

        // Tambahkan validasi dinamis untuk Bab (bab_1, bab_2, dst.)
        for ($i = 1; $i <= $jumlahBab; $i++) {
            $rules['bab_' . $i] = 'nullable|numeric|min:0|max:100';
        }

        $request->validate($rules);

        $authData = $this->getAuthenticatedUserAndGuard();
        $guruProfileId = $authData['guard'] === 'guru' ? $authData['user']->guruProfile->id : null;

        // 2. Kumpulkan Nilai Bab ke dalam Array (untuk disimpan sebagai JSON)
        $nilaiBabArray = [];
        for ($i = 1; $i <= $jumlahBab; $i++) {
            // Gunakan null jika tidak ada input atau input kosong
            $nilaiBabArray[] = is_numeric($request->input('bab_' . $i)) ? (int)$request->input('bab_' . $i) : null;
        }

        // 3. Simpan/Perbarui data Penilaian
        // Gunakan updateOrCreate untuk CREATE dan juga bertindak seperti UPDATE jika sudah ada
        Penilaian::updateOrCreate(
            [
                'santri_profile_id' => $request->santri_profile_id,
                'mapel_id' => $request->mapel_id,
            ],
            [
                // 'nilai' => $request->nilai_harian, // DIHAPUS (diganti nilai_harian_json)
                'nilai_harian_json' => json_encode($nilaiBabArray), // REVISI: Simpan nilai bab sebagai JSON
                'uts' => $request->nilai_uts,
                'uas' => $request->nilai_uas,
                // 'bab1' => $request->bab1, // DIHAPUS
                // 'bab2' => $request->bab2, // DIHAPUS
                // 'bab3' => $request->bab3, // DIHAPUS
                // 'bab4' => $request->bab4, // DIHAPUS
                // 'bab5' => $request->bab5, // DIHAPUS
                // 'catatan' => $request->catatan, // CATATAN DIHAPUS
                'guru_profile_id' => $guruProfileId,
                'kelas' => $request->kelas,
            ]
        );

        return redirect()->route('penilaian.index', ['kelas' => $request->kelas])->with('success', 'Nilai santri berhasil disimpan/diperbarui.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $penilaian = Penilaian::findOrFail($id);

        // 1. Validasi Input Dasar
        $rules = [
            'nilai_uts' => 'nullable|numeric|min:0|max:100',
            'nilai_uas' => 'nullable|numeric|min:0|max:100',
            // 'catatan' => 'nullable|string|max:255', // CATATAN DIHAPUS
        ];
        
        // Ambil data mapel terkait dengan Penilaian ini
        $mapel = Mapel::findOrFail($penilaian->mapel_id);
        $jumlahBab = $mapel->jumlah_bab ?? 0;

        // Tambahkan validasi dinamis untuk Bab (bab_1, bab_2, dst.)
        for ($i = 1; $i <= $jumlahBab; $i++) {
            $rules['bab_' . $i] = 'nullable|numeric|min:0|max:100';
        }

        $request->validate($rules);

        // 2. Kumpulkan Nilai Bab ke dalam Array (untuk disimpan sebagai JSON)
        $nilaiBabArray = [];
        for ($i = 1; $i <= $jumlahBab; $i++) {
             // Gunakan null jika tidak ada input atau input kosong
            $nilaiBabArray[] = is_numeric($request->input('bab_' . $i)) ? (int)$request->input('bab_' . $i) : null;
        }

        // 3. Update Penilaian
        $penilaian->update([
            // 'nilai' => $request->nilai_harian, // DIHAPUS (diganti nilai_harian_json)
            'nilai_harian_json' => json_encode($nilaiBabArray), // REVISI: Simpan nilai bab sebagai JSON
            'uts' => $request->nilai_uts,
            'uas' => $request->nilai_uas,
            // 'bab1' => $request->bab1, // DIHAPUS
            // 'bab2' => $request->bab2, // DIHAPUS
            // 'bab3' => $request->bab3, // DIHAPUS
            // 'bab4' => $request->bab4, // DIHAPUS
            // 'bab5' => $request->bab5, // DIHAPUS
            // 'catatan' => $request->catatan, // CATATAN DIHAPUS
        ]);

        // Arahkan kembali ke halaman index dengan kelas yang sama
        $kelas = $request->input('kelas', $penilaian->kelas); // Ambil kelas dari request atau dari penilaian itu sendiri
        return redirect()->route('penilaian.index', ['kelas' => $kelas])->with('success', 'Nilai santri berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Penilaian $penilaian)
    {
        // Logika destroy dipertahankan seperti aslinya
        $kelas = $penilaian->kelas; // Ambil kelas sebelum dihapus untuk redirect
        $penilaian->delete();
        return redirect()->route('penilaian.index', ['kelas' => $kelas])->with('success', 'Nilai berhasil dihapus.');
    }

    /**
     * Upload and process PDF for nilai import.
     */
    public function uploadAndProcessPdf(Request $request)
    {
        $request->validate([
            'file_pdf' => 'required|file|mimes:pdf|max:10240', // Max 10MB
            'mapel_id' => 'required|exists:mapels,id', // Harus ada di form upload
            'kelas' => 'required|string', // Harus ada di form upload
        ]);

        $authData = $this->getAuthenticatedUserAndGuard();
        $guruProfileId = $authData['guard'] === 'guru' ? $authData['user']->guruProfile->id : null;
        $mapelId = $request->mapel_id;
        $kelas = $request->kelas;

        // Store the uploaded PDF
        $pdfPath = $request->file('file_pdf')->store('uploads/pdf_nilai', 'public');

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
            // Regex mencari pola: NIS diikuti Nilai Harian, UTS, dan UAS (dipisahkan koma)
            if (preg_match('/(\d+):\s*(\d+),\s*(\d+),\s*(\d+)/', $line, $matches)) {
                $nis = $matches[1];
                $nilai_harian_dari_pdf = $matches[2]; // Nilai ini TIDAK akan disimpan ke kolom 'nilai' lama
                $uts = $matches[3];
                $uas = $matches[4];

                // Find santri by NIS
                $santriProfile = SantriProfile::whereHas('santri', function($q) use ($nis) {
                    $q->where('nis', $nis);
                })->first();

                if ($santriProfile) {
                    // Karena kolom `nilai_harian` dan `babX` sudah dihapus dan diganti dengan `nilai_harian_json`,
                    // nilai harian dari PDF ini (jika hanya satu nilai) akan diabaikan
                    // atau Anda bisa memutuskan untuk memasukkannya ke bab pertama jika itu yang diinginkan.
                    // Untuk saat ini, kita akan menyimpan nilai_harian_json sebagai array kosong jika tidak ada data bab spesifik di PDF.
                    // Jika PDF Anda akan memiliki format nilai bab spesifik di masa depan,
                    // logika parsing di sini perlu disesuaikan.
                    
                    Penilaian::updateOrCreate(
                        [
                            'santri_profile_id' => $santriProfile->id,
                            'mapel_id' => $mapelId,
                        ],
                        [
                            'nilai_harian_json' => json_encode([]), // Default kosong, karena PDF ini tidak menyediakan nilai per bab
                            'uts' => $uts,
                            'uas' => $uas,
                            // 'nilai' => $nilai_harian_dari_pdf, // DIHAPUS
                            // 'catatan' => null, // CATATAN DIHAPUS (default ke null)
                            'guru_profile_id' => $guruProfileId,
                            'kelas' => $kelas,
                        ]
                    );
                    $processedCount++;
                }
            }
        }

        return redirect()->route('penilaian.index', ['kelas' => $kelas])->with('success', "Berhasil memproses {$processedCount} nilai dari PDF.");
    }
}