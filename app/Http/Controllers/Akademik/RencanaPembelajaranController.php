<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Akademik\RencanaPembelajaran;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class RencanaPembelajaranController extends Controller
{
    /**
     * TAMPILAN INDEX: Menyiapkan data untuk calendar view (initial state Alpine.js)
     */
    public function index(Request $request)
    {
        // optional: query param month=YYYY-MM, default current month
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        
        // Ambil SEMUA entri agar filtering Tahun Ajaran (selectedYear) dapat dilakukan di Alpine.js (client-side)
        $entries = RencanaPembelajaran::orderBy('from_date')
                    ->get()
                    ->map(fn($e) => [
                        'id' => $e->id,
                        'from' => $e->from_date->format('Y-m-d'),
                        'to' => $e->to_date->format('Y-m-d'),
                        'jenis' => $e->jenis,
                        'judul' => $e->judul,
                        'catatan' => $e->catatan,
                        'created_by' => $e->created_by,
                    ])->values();

        // Buat index per tanggal (entriesByDate) untuk INITIAL STATE di Alpine.js
        $entriesByDate = [];
        foreach ($entries as $e) {
            $from = Carbon::parse($e['from']);
            $to = Carbon::parse($e['to']);
            // Iterasi dari tanggal 'from' sampai 'to'
            for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                $key = $d->format('Y-m-d');
                $entriesByDate[$key][] = [
                    'id' => $e['id'],
                    'jenis' => $e['jenis'],
                    'judul' => $e['judul'],
                    'catatan' => $e['catatan'],
                ];
            }
        }

        // Kirim data ke view
        return view('Akademik.Kalender.index', [
            'month' => $month,
            'dataForAlpine' => [
                'entries' => $entries,
                'entriesByDate' => $entriesByDate,
            ],
        ]);
    }

    /**
     * STORE: Membuat entri baru.
     */
    public function store(Request $request)
    {
        // Cek Permission Waka/Kepsek
        $currentUser = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        $jabatan = strtolower($currentUser->guruProfile->jabatan ?? '');
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            return response()->json(['status' => 'error', 'message' => 'Akses Ditolak. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola data ini.'], 403);
        }

        $v = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'jenis' => 'required|string|max:50',
            'judul' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $v->errors()
            ], 422);
        }

        // Hapus entri yang tumpang tindih (Overwrite)
        RencanaPembelajaran::where('from_date', '<=', $request->input('to'))
            ->where('to_date', '>=', $request->input('from'))
            ->delete();

        $entry = RencanaPembelajaran::create([
            'from_date' => $request->input('from'),
            'to_date' => $request->input('to'),
            'jenis' => $request->input('jenis'),
            'judul' => $request->input('judul'),
            'catatan' => $request->input('catatan'),
        ]);

        // Return JSON entri baru untuk Alpine.js (Fetch API)
        return response()->json([
            'status' => 'ok',
            'entry' => [
                'id' => $entry->id,
                'from' => $entry->from_date->format('Y-m-d'),
                'to' => $entry->to_date->format('Y-m-d'),
                'jenis' => $entry->jenis,
                'judul' => $entry->judul,
                'catatan' => $entry->catatan,
            ]
        ], 201); // 201 Created
    }

    /**
     * UPDATE: Memperbarui entri yang sudah ada.
     */
    public function update(Request $request, $id)
    {
        // Cek Permission Waka/Kepsek
        $currentUser = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        $jabatan = strtolower($currentUser->guruProfile->jabatan ?? '');
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            return response()->json(['status' => 'error', 'message' => 'Akses Ditolak. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola data ini.'], 403);
        }

        $v = Validator::make($request->all(), [
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
            'jenis' => 'required|string|max:50',
            'judul' => 'nullable|string|max:255',
            'catatan' => 'nullable|string',
        ]);

        if ($v->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $v->errors()
            ], 422);
        }

        $entry = RencanaPembelajaran::find($id);
        if (!$entry) {
            return response()->json(['status' => 'not_found'], 404);
        }

        // Hapus entri LAIN yang tumpang tindih (Overwrite), kecuali diri sendiri
        RencanaPembelajaran::where('id', '!=', $id)
            ->where('from_date', '<=', $request->input('to'))
            ->where('to_date', '>=', $request->input('from'))
            ->delete();

        $entry->update([
            'from_date' => $request->input('from'),
            'to_date' => $request->input('to'),
            'jenis' => $request->input('jenis'),
            'judul' => $request->input('judul'),
            'catatan' => $request->input('catatan'),
        ]);

        // Return JSON entri yang diperbarui
        return response()->json([
            'status' => 'ok',
            'entry' => [
                'id' => $entry->id,
                'from' => $entry->from_date->format('Y-m-d'),
                'to' => $entry->to_date->format('Y-m-d'),
                'jenis' => $entry->jenis,
                'judul' => $entry->judul,
                'catatan' => $entry->catatan,
            ]
        ]);
    }

    /**
     * DESTROY: Menghapus satu entri.
     */
    public function destroy($id)
    {
        // Cek Permission Waka/Kepsek
        $currentUser = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        $jabatan = strtolower($currentUser->guruProfile->jabatan ?? '');
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            return response()->json(['status' => 'error', 'message' => 'Akses Ditolak. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola data ini.'], 403);
        }

        $e = RencanaPembelajaran::find($id);
        if (!$e) return response()->json(['status' => 'not_found'], 404);
        
        $e->delete();
        
        return response()->json(['status' => 'ok']);
    }

    /**
     * Mass DESTROY: Menghapus beberapa entri berdasarkan array ID.
     * Dipanggil oleh Alpine.js 'removeBySelection()'.
     */
    public function massDestroy(Request $request)
    {
        // Cek Permission Waka/Kepsek
        $currentUser = \Illuminate\Support\Facades\Auth::guard('guru')->user();
        $jabatan = strtolower($currentUser->guruProfile->jabatan ?? '');
        if (!in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka'])) {
            return response()->json(['status' => 'error', 'message' => 'Akses Ditolak. Hanya Kepala Sekolah atau Wakil Kepala Sekolah yang dapat mengelola data ini.'], 403);
        }

        $v = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'exists:rencana_pembelajaran,id', 
        ]);

        if ($v->fails()) {
            return response()->json(['status' => 'error', 'errors' => $v->errors()], 422);
        }

        $ids = $request->input('ids');
        $deletedCount = RencanaPembelajaran::whereIn('id', $ids)->delete();

        return response()->json([
            'status' => 'ok',
            'deleted_count' => $deletedCount,
            'message' => "Berhasil menghapus {$deletedCount} entri."
        ]);
    }
}