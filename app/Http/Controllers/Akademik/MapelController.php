<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Mapel;
use App\Models\Akademik\GuruMapel;
use App\Models\User\GuruProfile;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    /**
     * Menampilkan daftar mapel dengan jumlah guru pengampu.
     */
    public function index()
    {
        $query = Mapel::withCount('guruProfiles')
            ->orderBy('kelas')
            ->orderBy('nama_mapel');

        // Jika guru login, hanya tampilkan mapel yang dia ajar
        if (auth('guru')->check()) {
            $guru = auth('guru')->user();
            $guruProfile = $guru->profile; // relasi hasOne di model Guru

            if ($guruProfile) {
                $mapelIds = GuruMapel::where('guru_profile_id', $guruProfile->id)->pluck('mapel_id');
                $query->whereIn('id', $mapelIds);
            } else {
                $query->whereNull('id'); // tidak tampil jika belum ada profil
            }
        }

        $mapels = $query->paginate(15);

        return view('akademik.mapel.index', compact('mapels'));
    }

    /**
     * Menampilkan detail mapel dan guru pengampu.
     */
    public function show($id)
    {
        $mapel = Mapel::with(['guruProfiles.user:id,username'])->findOrFail($id);
        $gurus = $mapel->guruProfiles;

        return view('akademik.mapel.show', compact('mapel', 'gurus'));
    }

    /**
     * Form tambah mapel baru.
     */
    public function create()
    {
        $semesters = ['Ganjil', 'Genap'];
        $tahunAjarans = $this->generateTahunAjaranOptions();

        return view('akademik.mapel.create', compact('semesters', 'tahunAjarans'));
    }

    /**
     * Simpan mapel baru dan otomatis tambahkan ke tabel guru_mapel.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel' => 'required|string|max:255',
            'kelas' => 'required|string|max:10',
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:20',
        ]);

        // ✅ Simpan mapel baru
        $mapel = Mapel::create([
            'nama_mapel' => $request->nama_mapel,
            'kelas' => $request->kelas,
            'semester' => $request->semester,
            'tahun_ajaran' => $request->tahun_ajaran,
        ]);

        // ✅ Jika guru login, otomatis masukkan ke guru_mapel
        if (auth('guru')->check()) {
            $guru = auth('guru')->user();
            $guruProfile = $guru->profile; // relasi hasOne di model Guru

            if ($guruProfile) {
                GuruMapel::firstOrCreate([
                    'guru_profile_id' => $guruProfile->id,
                    'mapel_id' => $mapel->id,
                ]);
            }
        }

        return redirect()->route('akademik.mapel.index')
            ->with('success', 'Mapel berhasil ditambahkan dan dikaitkan dengan guru yang login.');
    }

    /**
     * Form edit mapel.
     */
    public function edit(Mapel $mapel)
    {
        $semesters = ['Ganjil', 'Genap'];
        $tahunAjarans = $this->generateTahunAjaranOptions();

        return view('akademik.mapel.edit', compact('mapel', 'semesters', 'tahunAjarans'));
    }

    /**
     * Update data mapel.
     */
    public function update(Request $request, Mapel $mapel)
    {
        $request->validate([
            'nama_mapel' => 'required|string|max:255',
            'kelas' => 'required|string|max:10',
            'semester' => 'required|in:Ganjil,Genap',
            'tahun_ajaran' => 'required|string|max:20',
        ]);

        $mapel->update([
            'nama_mapel' => $request->nama_mapel,
            'kelas' => $request->kelas,
            'semester' => $request->semester,
            'tahun_ajaran' => $request->tahun_ajaran,
        ]);

        return redirect()->route('akademik.mapel.index')
            ->with('success', 'Mapel berhasil diperbarui.');
    }

    /**
     * Hapus mapel.
     */
    public function destroy(Mapel $mapel)
    {
        // Hapus relasi guru_mapel juga
        GuruMapel::where('mapel_id', $mapel->id)->delete();

        $mapel->delete();

        return redirect()->route('akademik.mapel.index')
            ->with('success', 'Mapel berhasil dihapus.');
    }

    // ==========================================================
    // Helper Dropdown Tahun Ajaran
    // ==========================================================
    private function generateTahunAjaranOptions()
    {
        $currentYear = date('Y');
        $options = [];

        for ($i = -2; $i <= 2; $i++) {
            $start = $currentYear + $i;
            $end = $start + 1;
            $options[] = "$start/$end";
        }

        return $options;
    }
}
