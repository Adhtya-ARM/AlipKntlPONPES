<?php

namespace App\Http\Controllers\Akademik;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Mapel;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\RencanaPembelajaran;
use App\Models\Akademik\AbsensiHeader;
use App\Models\Akademik\AbsensiDetail;
use App\Models\Akademik\Penilaian;
use App\Models\User\SantriProfile;
use App\Models\Akademik\SantriMapel;

class MapelController extends Controller
{
    /**
     * Tampilkan daftar mapel yang diajar oleh guru yang login.
     */
    public function index()
    {
        $guruId = auth()->user()->guruProfile->id;

        $guruMapels = GuruMapel::with(["mapel", "kelas", "rencanaPembelajaran"])
            ->withCount("santriMapel") // ✅ ini penting!
            ->where("guru_profile_id", $guruId)
            ->get();

        $kelas = Kelas::orderBy("nama_kelas")->get();

        return view("akademik.mapel.index", compact("guruMapels", "kelas"));
    }

    /**
     * Simpan Mapel baru + GuruMapel + Rencana Pembelajaran kosong.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            "nama_mapel" => "required|string|max:255",
            "kelas_id" => "required|integer|exists:kelas,id",
            "tahun_ajaran" => "required|string",
            "semester" => "required|string|in:Ganjil,Genap",
        ]);

        $guruProfile = Auth::user()->guruProfile;

        if (!$guruProfile) {
            return response()->json(
                [
                    "message" =>
                        "Guru profile tidak ditemukan. Pastikan akun guru sudah lengkap.",
                ],
                404,
            );
        }

        // 1️⃣ Mapel Master - berdasarkan nama_mapel + periode
        $mapel = Mapel::firstOrCreate([
            "nama_mapel" => $validated["nama_mapel"],
        ]);

        // 2️⃣ GuruMapel - tambahkan tahun_ajaran & semester
        $guruMapel = GuruMapel::firstOrCreate([
            "guru_profile_id" => $guruProfile->id,
            "mapel_id" => $mapel->id,
            "kelas_id" => $validated["kelas_id"],
            "tahun_ajaran" => $validated["tahun_ajaran"],
            "semester" => $validated["semester"],
        ]);

        // 3️⃣ Buat Rencana Pembelajaran awal
        RencanaPembelajaran::firstOrCreate(
            ["guru_mapel_id" => $guruMapel->id],
            ["jumlah_pertemuan" => 0, "jumlah_bab" => 0, "keterangan" => null],
        );

        return response()->json(
            [
                "message" => "Mapel dan penugasan berhasil disimpan.",
            ],
            201,
        );
    }

    /**
     * Update Mapel dan Penugasan.
     */
    public function update(Request $request, Mapel $mapel)
    {
        try {
            $validated = $request->validate([
                "nama_mapel" => "required|string|max:255",
                "kelas_id" => "required|integer|exists:kelas,id",
                "tahun_ajaran" => "required|string",
                "semester" => "required|string|in:Ganjil,Genap",
            ]);
        } catch (ValidationException $e) {
            return response()->json(
                [
                    "message" => "Validasi gagal",
                    "errors" => $e->errors(),
                ],
                422,
            );
        }

        $guruProfile = Auth::user()->guruProfile;

        // Update Mapel Master
        $mapel->update([
            "nama_mapel" => $validated["nama_mapel"],
            "tahun_ajaran" => $validated["tahun_ajaran"],
            "semester" => $validated["semester"],
        ]);

        // Update GuruMapel (kelas_id)
        $guruMapel = GuruMapel::where("guru_profile_id", $guruProfile->id)
            ->where("mapel_id", $mapel->id)
            ->first();

        if ($guruMapel) {
            $guruMapel->update(["kelas_id" => $validated["kelas_id"]]);
            return response()->json(
                ["message" => "Mapel dan penugasan berhasil diperbarui!"],
                200,
            );
        }

        return response()->json(
            ["message" => "Penugasan guru tidak ditemukan."],
            404,
        );
    }

    /**
     * Update Rencana Pembelajaran (Target Pertemuan dan Bab).
     */
    public function updateRencana(Request $request, GuruMapel $guruMapel)
    {
        try {
            $validated = $request->validate([
                "jumlah_pertemuan" => "required|integer|min:0",
                "jumlah_bab" => "required|integer|min:0",
                "keterangan" => "nullable|string",
            ]);
        } catch (ValidationException $e) {
            return response()->json(
                [
                    "message" => "Validasi gagal",
                    "errors" => $e->errors(),
                ],
                422,
            );
        }

        // Cek kepemilikan
        if ($guruMapel->guru_profile_id !== Auth::user()->guruProfile->id) {
            return response()->json(["message" => "Akses ditolak."], 403);
        }

        // Cari atau buat rencana
        $rencana = RencanaPembelajaran::firstOrCreate(
            ["guru_mapel_id" => $guruMapel->id],
            ["jumlah_pertemuan" => 0, "jumlah_bab" => 0],
        );

        $rencana->update($validated);

        return response()->json(
            ["message" => "Rencana pembelajaran berhasil disimpan!"],
            200,
        );
    }

    public function getSiswa($guruMapelId)
    {
        $guruMapel = GuruMapel::with("kelas")->findOrFail($guruMapelId);

        if (!$guruMapel->kelas_id) {
            return response()->json(
                ["message" => "Kelas belum ditetapkan untuk mapel ini."],
                400,
            );
        }

        // Ambil santri aktif berdasarkan kelas di tabel santri_kelas
        $santri = SantriProfile::whereHas("santriKelas", function ($q) use (
            $guruMapel,
        ) {
            $q->where("kelas_id", $guruMapel->kelas_id);
        })
            ->select("id", "nama")
            ->orderBy("nama")
            ->get();

        // Ambil santri yang sudah mengikuti mapel ini
        $terpilih = SantriMapel::where("guru_mapel_id", $guruMapel->id)
            ->pluck("santri_profile_id")
            ->toArray();

        return response()->json([
            "santri" => $santri,
            "terpilih" => $terpilih,
        ]);
    }

    /**
     * Simpan daftar santri ke tabel santri_mapel
     */
    public function updateSiswa(Request $request, $guruMapelId)
    {
        try {
            $guruMapel = GuruMapel::findOrFail($guruMapelId, );

            // Ambil daftar santri dari request (dari JSON)
            $santriIds = $request->input("santri", []); // <--- ✅ key harus sama persis

            // Validasi ID agar benar-benar ada
            $validIds = SantriProfile::whereIn( "id", $santriIds, )->pluck("id")->toArray();
            SantriMapel::where("guru_mapel_id",$guruMapel->id,)->delete();

            foreach ($validIds as $id) { SantriMapel::create(["guru_mapel_id" => $guruMapel->id,"santri_profile_id" => $id,]);
            }

            return response()->json(
                ["message" => "Daftar siswa berhasil diperbarui."],
                200,
            );
        } catch (\Throwable $e) {
            return response()->json(
                [
                    "message" => "Gagal memperbarui daftar siswa.",
                    "error" => $e->getMessage(),
                ],
                500,
            );
        }
    }

    /**
     * Hapus Mapel dan Penugasan Guru.
     */
    public function destroy(Mapel $mapel)
    {
        $guruProfile = Auth::user()->guruProfile;

        $guruMapels = GuruMapel::where("guru_profile_id", $guruProfile->id)
            ->where("mapel_id", $mapel->id)
            ->get();

        foreach ($guruMapels as $gm) {
            // Hapus absensi & penilaian terkait (cascade manual)
            AbsensiHeader::where("guru_mapel_id", $gm->id)->each(function (
                $header,
            ) {
                AbsensiDetail::where(
                    "absensi_header_id",
                    $header->id,
                )->delete();
                $header->delete();
            });

            Penilaian::where("guru_mapel_id", $gm->id)->delete();
            RencanaPembelajaran::where("guru_mapel_id", $gm->id)->delete();

            $gm->delete();
        }

        // Jika tidak ada guru lain yang mengajar mapel ini, hapus mapel master
        if (!GuruMapel::where("mapel_id", $mapel->id)->exists()) {
            $mapel->delete();
        }

        return response()->json(
            ["message" => "Mapel dan semua relasi berhasil dihapus."],
            200,
        );
    }
}
