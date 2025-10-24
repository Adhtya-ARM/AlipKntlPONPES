<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\Penilaian;
use App\Models\Akademik\Mapel;
use App\Models\User\SantriProfile;
// Pastikan path Model Anda benar. Jika Wali, Guru, Santri ada di App\Models, 
// maka sesuaikan pathnya di sini, atau asumsikan profile ada di App\Models\User
use App\Models\User\GuruProfile; 
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
        $allowedGuards = ['web', 'guru'];
        
        foreach ($allowedGuards as $guardName) {
            if (Auth::guard($guardName)->check()) {
                return [
                    'user' => Auth::guard($guardName)->user(),
                    'guard' => $guardName // Akan mengembalikan 'web' atau 'guru'
                ];
            }
        }
        return ['user' => null, 'guard' => null];
    }
    
    /**
     * Helper untuk mendapatkan semua Santri dan Mapel yang relevan untuk form modal.
     * @param \Illuminate\Database\Eloquent\Model|null $guruModel - Model Guru yang sudah terotentikasi
     * @param string $userRole
     * @return array
     */
    protected function getFormData($guruModel, $userRole)
    {
        // Ambil semua Santri (ID dan Nama)
        $allSantri = SantriProfile::select('id', 'nama', 'kelas')->orderBy('nama')->get();

        // Ambil semua Mapel (ID dan Nama) yang diizinkan
        $mapelQuery = Mapel::select('id', 'nama_mapel', 'kelas', 'tahun_ajaran', 'semester')->orderBy('nama_mapel');

        // Filter Mapel berdasarkan Guru yang mengajar (jika role-nya guru)
        // Kita langsung pakai $guruModel (yang merupakan instance dari App\Models\Guru)
        if ($userRole === 'guru' && $guruModel && method_exists($guruModel, 'mapels')) {
            $mapelIdsDiizinkan = $guruModel->mapels->pluck('id');
            $mapelQuery->whereIn('id', $mapelIdsDiizinkan);
        }

        $allMapel = $mapelQuery->get();

        // Ambil opsi Tahun Ajaran dan Semester dari Mapel
        $allTahunAjaran = Mapel::select('tahun_ajaran')->distinct()->pluck('tahun_ajaran')->sortDesc();
        $allSemester = ['Ganjil', 'Genap'];

        return compact('allSantri', 'allMapel', 'allTahunAjaran', 'allSemester');
    }

    /**
     * Menampilkan daftar nilai dalam format matriks (pivot) dengan filter.
     */
    public function index(Request $request)
    {
        // MENGAMBIL USER YANG SEDANG AKTIF (Guard sudah melindungi rute ini)
        $authData = $this->getAuthenticatedUserAndGuard();
        $user = $authData['user'];
        $guard = $authData['guard'];

        if (!$user) {
            // Seharusnya diredirect oleh middleware 'auth:web,guru' di route
            return redirect()->route('login');
        }

        // Tentukan role/guard yang aktif
        $userRole = $guard;
        $isGuru = $userRole === 'guru';
        
        // Model yang sedang login (Guru atau User/Admin). Ini digunakan untuk relasi mapels.
        $userModelForMapels = $isGuru ? $user : null; 

        // 1. Tentukan Opsi Filter Kelas
        $allKelas = SantriProfile::select('kelas')->distinct()->orderBy('kelas')->pluck('kelas');
        $defaultKelas = $allKelas->first() ?? 'VII';

        // 2. Tentukan Filter Opsi Unik Tahun Ajaran & Semester
        $allTahunAjaran = Mapel::select('tahun_ajaran')->distinct()->pluck('tahun_ajaran')->sortDesc();
        $allSemester = ['Ganjil', 'Genap'];

        $defaultTahun = $allTahunAjaran->first() ?? (string)(date('Y') . '/' . (date('Y') + 1));
        $defaultSemester = 'Ganjil';

        // 3. Tentukan Filter Aktif
        $currentKelas = $request->input('kelas', $defaultKelas);
        $currentTahunAjaran = $request->input('tahun_ajaran', $defaultTahun);
        $currentSemester = $request->input('semester', $request->input('semester') ?: $defaultSemester);

        // 4. Tentukan Mata Pelajaran yang Boleh Diakses (Header Kolom)
        $mapelQuery = Mapel::query();

        // Filter Mapel berdasarkan Kelas, Tahun Ajaran, dan Semester
        $mapelQuery->where('kelas', $currentKelas)
                   ->where('tahun_ajaran', $currentTahunAjaran)
                   ->where('semester', $currentSemester);

        if ($isGuru) {
            if ($userModelForMapels && method_exists($userModelForMapels, 'mapels')) {
                $mapelIdsDiizinkan = $userModelForMapels->mapels->pluck('id');
                $mapelQuery->whereIn('id', $mapelIdsDiizinkan);
            }
        }

        $mataPelajaran = $mapelQuery->get();
        $mapelIdsTampil = $mataPelajaran->pluck('id'); 

        // 5. Ambil Santri Profile yang sesuai dengan Kelas
        $santriQuery = SantriProfile::with('santri')
                                   ->where('kelas', $currentKelas)
                                   ->orderBy('nama');

        if ($request->filled('cari')) {
            $searchTerm = '%' . $request->input('cari') . '%';
            $santriQuery->where('nama', 'like', $searchTerm);
        }

        $santriProfiles = $santriQuery->paginate(20)->appends($request->query());

        // 6. Ambil semua Penilaian yang relevan
        $profileIds = $santriProfiles->pluck('id');

        $penilaianQuery = Penilaian::whereIn('santri_profile_id', $profileIds)
                                   ->whereIn('mapel_id', $mapelIdsTampil)
                                   ->with(['santriProfile:id,nama', 'mapel:id,nama']); 

        $penilaians = $penilaianQuery->get()
                                     ->map(function ($item) {
                                         $item->santri_nama = $item->santriProfile->nama ?? 'N/A';
                                         $item->mata_pelajaran = $item->mapel->nama ?? 'N/A';
                                         return $item;
                                     })
                                     ->groupBy('santri_profile_id')
                                     ->map(fn ($items) => $items->keyBy('mapel_id'));

        // 7. Ambil data Form Modal
        $formData = $this->getFormData($userModelForMapels, $userRole);

        // Kirim data pivot, filter options, dan data form ke view
        return view('Akademik.Nilai.index', array_merge($formData, compact(
            'santriProfiles',
            'mataPelajaran',
            'penilaians',
            'currentKelas',
            'currentTahunAjaran',
            'currentSemester',
            'allKelas'
        )));
    }

    /**
     * Menampilkan form untuk menambahkan nilai baru.
     */
    public function create()
    {
        $authData = $this->getAuthenticatedUserAndGuard();
        $user = $authData['user'];
        $userRole = $authData['guard']; 

        if (!$user) {
            return redirect()->route('login');
        }

        $userModelForMapels = ($userRole === 'guru') ? $user : null;

        // Ambil semua data yang diperlukan untuk form input:
        $formData = $this->getFormData($userModelForMapels, $userRole);

        // Data default untuk form (opsional)
        $defaultTahun = $formData['allTahunAjaran']->first() ?? (string)(date('Y') . '/' . (date('Y') + 1));
        $defaultSemester = 'Ganjil';

        return view('Akademik.Nilai.create', array_merge($formData, compact(
            'defaultTahun',
            'defaultSemester'
        )));
    }

    /**
     * Menyimpan atau memperbarui nilai dari form modal (Logika CREATE/UPDATE).
     */
    public function store(Request $request)
    {
        $authData = $this->getAuthenticatedUserAndGuard();
        $user = $authData['user'];
        $userRole = $authData['guard'];
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userModelForMapels = ($userRole === 'guru') ? $user : null;

        // Validasi
        $request->validate([
            'santri_profile_id' => 'required|exists:santri_profile,id',
            'mapel_id'          => 'required|exists:mapel,id',
            'nilai_tugas'       => 'required|numeric|min:0|max:100',
            'nilai_uts'         => 'required|numeric|min:0|max:100',
            'nilai_uas'         => 'required|numeric|min:0|max:100',
        ]);
        
        // Pengecekan Otorisasi Tambahan (opsional: apakah guru ini mengajar mapel ini?)
        // ...

        $data = $request->only([
            'santri_profile_id', 'mapel_id', 'nilai_tugas', 'nilai_uts', 'nilai_uas'
        ]);

        $penilaian = Penilaian::where('santri_profile_id', $data['santri_profile_id'])
                             ->where('mapel_id', $data['mapel_id'])
                             ->first();

        DB::beginTransaction();
        try {
            if ($penilaian) {
                $penilaian->update($data); 
                $message = 'Nilai berhasil diperbarui.';
            } else {
                // Guru Profile ID diambil dari user yang sedang login jika guardnya 'guru'
                // Jika Admin ('web'), gunakan ID mereka atau fallback (misal: 1)
                $data['guru_profile_id'] = $userModelForMapels ? $userModelForMapels->id : $user->id; 
                Penilaian::create($data);
                $message = 'Nilai berhasil ditambahkan.';
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menyimpan nilai: ' . $e->getMessage());
        }

        $redirectParams = $request->only('kelas', 'tahun_ajaran', 'semester', 'cari', 'page');

        return redirect()->route('penilaian.index', $redirectParams)->with('success', $message);
    }

    /**
     * Memperbarui nilai yang sudah ada (Logika UPDATE).
     */
    public function update(Request $request, Penilaian $penilaian)
    {
        $authData = $this->getAuthenticatedUserAndGuard();
        $user = $authData['user'];
        
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Validasi
        $request->validate([
            'nilai_tugas' => 'required|numeric|min:0|max:100',
            'nilai_uts'   => 'required|numeric|min:0|max:100',
            'nilai_uas'   => 'required|numeric|min:0|max:100',
        ]);

        $data = $request->only(['nilai_tugas', 'nilai_uts', 'nilai_uas']);

        DB::beginTransaction();
        try {
            $penilaian->update($data);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengupdate nilai: ' . $e->getMessage());
        }

        $redirectParams = $request->only('kelas', 'tahun_ajaran', 'semester', 'cari', 'page');

        return redirect()->route('penilaian.index', $redirectParams)->with('success', 'Nilai berhasil diperbarui.');
    }

    /**
     * Menghapus nilai (Logika DELETE).
     */
    public function destroy(Penilaian $penilaian, Request $request)
    {
        $authData = $this->getAuthenticatedUserAndGuard();
        $user = $authData['user'];

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        DB::beginTransaction();
        try {
            $penilaian->delete();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus nilai: ' . $e->getMessage());
        }

        $redirectParams = $request->query();

        return redirect()->route('penilaian.index', $redirectParams)->with('success', 'Nilai berhasil dihapus.');
    }
}
