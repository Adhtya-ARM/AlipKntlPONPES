<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\GuruProfile;
use App\Models\SantriProfile;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\RencanaPertemuan; 

class AbsensiController extends Controller
{
    // ---
    // FUNGSI UTAMA (GURU & SANTRI)
    // ---
    public function index()
    {
        $user = auth()->user();
        $guard = $this->getGuardName();

        if ($guard === 'guru') {
            $guruProfile = $user->guruProfile;
            if (!$guruProfile) {
                return redirect()->back()->with('error', 'Profile guru tidak ditemukan');
            }

            // Eager load relasi baru: mapel, kelas, dan rencanaPembelajaran
            $guruMapels = GuruMapel::with(['mapel', 'kelas', 'rencanaPembelajaran']) 
                ->where('guru_profile_id', $guruProfile->id)
                ->get();

            // Ubah view jika perlu
            return view('absensi.index', compact('guruMapels'));
        }

        // Jika santri yang login
        if ($guard === 'santri') {
            $santriProfile = $user->santriProfile;
            if (!$santriProfile) {
                return redirect()->back()->with('error', 'Profile santri tidak ditemukan');
            }

            // Gunakan Query Builder untuk rekap absensi santri
            $rekapAbsensi = DB::table('absensi_detail AS ad')
                ->select(
                    'ad.status_kehadiran',
                    DB::raw('COUNT(*) as total'),
                    'm.nama_mapel',
                    'm.id as mapel_id'
                )
                ->join('absensi_header AS ah', 'ad.absensi_header_id', '=', 'ah.id')
                ->join('rencana_pembelajaran AS rp', 'ah.rencana_pembelajaran_id', '=', 'rp.id')
                ->join('guru_mapel AS gm', 'rp.guru_mapel_id', '=', 'gm.id')
                ->join('mapel AS m', 'gm.mapel_id', '=', 'm.id')
                ->where('ad.santri_id', $santriProfile->id)
                ->groupBy('ad.status_kehadiran', 'm.nama_mapel', 'm.id')
                ->get()
                ->groupBy('nama_mapel'); // Grouping untuk tampilan di view santri

            // Ganti variabel absensis dengan rekapAbsensi
            return view('absensi.santri-index', compact('rekapAbsensi')); 
        }

        return redirect()->back()->with('error', 'Unauthorized');
    }

    private function getGuardName()
    {
        // Fungsi ini tidak berubah
        foreach (['web', 'guru', 'santri', 'wali'] as $guard) {
            if (auth()->guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }

    // ---
    // FUNGSI UNTUK MENGISI ABSENSI (GURU)
    // ---

    /**
     * Mengambil daftar santri, rekap absensi, dan max pertemuan.
     */
    public function getSiswaList($guruMapelId)
    {
        $guruMapel = GuruMapel::findOrFail($guruMapelId);
        $kelasId = $guruMapel->kelas_id; // Menggunakan kelas_id dari GuruMapel
        
        // 1. Ambil Rencana Pembelajaran (setup awal)
        $rencana = RencanaPembelajaran::firstOrCreate(
            ['guru_mapel_id' => $guruMapelId],
            [
                'jumlah_pertemuan' => 16, // Nilai default
                'jumlah_bab' => 8 // Nilai default
            ]
        );

        // 2. Ambil ID Santri dari kelas yang diampu (Gunakan santri_kelas)
        $santriInClassIds = DB::table('santri_kelas')
            ->where('kelas_id', $kelasId)
            ->pluck('santri_profile_id');
            
        // 3. Ambil Rekap Absensi (Query Builder dengan JOIN)
        $rekapAbsensi = DB::table('absensi_detail AS ad')
            ->select(
                'ad.santri_id',
                'ad.status_kehadiran',
                DB::raw('COUNT(*) as total')
            )
            ->join('absensi_header AS ah', 'ad.absensi_header_id', '=', 'ah.id')
            ->join('rencana_pembelajaran AS rp', 'ah.rencana_pembelajaran_id', '=', 'rp.id')
            ->where('rp.guru_mapel_id', $guruMapelId) 
            ->whereIn('ad.santri_id', $santriInClassIds)
            ->groupBy('ad.santri_id', 'ad.status_kehadiran')
            ->get()
            ->keyBy(function($item) {
                return $item->santri_id . '_' . $item->status_kehadiran;
            });

        // 4. Ambil Nama Santri dan Gabungkan Rekap
        $santriList = SantriProfile::whereIn('id', $santriInClassIds)
            ->select('id', 'nama')
            ->get()
            ->map(function($santri) use ($rekapAbsensi) {
                $alphaKey = $santri->id . '_alpha';
                $totalAlpha = $rekapAbsensi->has($alphaKey) ? $rekapAbsensi->get($alphaKey)->total : 0;

                return [
                    'id' => $santri->id,
                    'nama' => $santri->nama,
                    // 'status_awal' adalah status default di form
                    'status_awal' => $totalAlpha >= 7 ? 'X' : 'hadir', 
                    'total_alpha' => $totalAlpha,
                    'keterangan' => ''
                ];
            });

        return response()->json([
            'santri' => $santriList,
            'maxPertemuan' => $rencana->jumlah_pertemuan
        ]);
    }

    /**
     * Menyimpan data absensi ke AbsensiHeader dan AbsensiDetail.
     */
    public function store(Request $request)
    {
        $request->validate([
            'guru_mapel_id' => 'required|exists:guru_mapel,id',
            'pertemuan_ke' => 'required|integer|min:1',
            'tanggal_absensi' => 'required|date', // Tambahkan validasi tanggal
            'absensi' => 'required|array',
            'absensi.*.id' => 'required|exists:santri_profile,id',
            // Ganti 'status' menjadi 'status_kehadiran'
            'absensi.*.status_kehadiran' => 'required|in:hadir,sakit,izin,alpha,X', 
            'absensi.*.catatan' => 'nullable|string' // Ganti 'keterangan' menjadi 'catatan'
        ]);

        $guruMapelId = $request->guru_mapel_id;
        
        // 1. Cari Rencana Pembelajaran ID
        $rencana = RencanaPembelajaran::where('guru_mapel_id', $guruMapelId)->firstOrFail();

        // 2. Buat record di AbsensiHeader (Query Builder updateOrInsert)
        DB::table('absensi_header')->updateOrInsert(
            [
                'rencana_pembelajaran_id' => $rencana->id,
                'pertemuan_ke' => $request->pertemuan_ke,
            ],
            [
                'tanggal_absensi' => $request->tanggal_absensi,
                'updated_at' => now(), 
            ]
        );

        // Dapatkan ID AbsensiHeader yang baru/di-update
        $absensiHeader = DB::table('absensi_header')
                           ->where('rencana_pembelajaran_id', $rencana->id)
                           ->where('pertemuan_ke', $request->pertemuan_ke)
                           ->first();
        $absensiHeaderId = $absensiHeader->id;


        $absensiDetailData = [];
        foreach ($request->absensi as $data) {
            $absensiDetailData[] = [
                'absensi_header_id' => $absensiHeaderId, 
                'santri_id' => $data['id'],
                'status_kehadiran' => $data['status_kehadiran'] === 'X' ? 'alpha' : $data['status_kehadiran'], 
                'catatan' => $data['catatan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // 3. Simpan massal ke AbsensiDetail (Query Builder upsert)
        DB::table('absensi_detail')->upsert(
            $absensiDetailData, 
            ['absensi_header_id', 'santri_id'], 
            ['status_kehadiran', 'catatan', 'updated_at']
        );

        return response()->json(['message' => 'Absensi berhasil disimpan']);
    }

    // ---
    // FUNGSI UNTUK MENGELOLA RENCANA PEMBELAJARAN (Mengganti 'update' lama)
    // ---
    
    /**
     * Mengubah data Rencana Pembelajaran (jumlah pertemuan/bab).
     * ID yang diterima adalah guruMapelId.
     */
    public function updateRencana(Request $request, $guruMapelId)
    {
        // Validasi data Rencana Pembelajaran
        $data = $request->validate([
            'jumlah_pertemuan' => 'required|integer|min:0',
            'jumlah_bab' => 'required|integer|min:0',
            'keterangan' => 'nullable|string'
        ]);
        
        // Cari Rencana berdasarkan guruMapelId
        $rencana = RencanaPembelajaran::where('guru_mapel_id', $guruMapelId)->firstOrFail();
        
        $rencana->update($data);

        if ($request->wantsJson()) {
            return response()->json(['data' => $rencana]);
        }

        return redirect()->back()->with('success', 'Rencana Pembelajaran berhasil diperbarui.');
    }

    /**
     * HAPUS FUNGSI DESTROY LAMA karena tidak lagi relevan
     * Anda dapat membuat fungsi destroy terpisah untuk AbsensiDetail jika diperlukan.
     */
    public function destroy(Request $request, $id)
    {
        // Tidak ada perubahan karena fungsi destroy ini mengacu pada ID Absensi lama.
        // Sebaiknya ganti ini menjadi fungsi untuk menghapus AbsensiHeader jika memang dibutuhkan.
        return redirect()->back()->with('error', 'Fungsi hapus absensi tidak diimplementasikan.');
    }
}