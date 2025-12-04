<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Akademik\TahunAjaran;
use App\Models\Akademik\Kelas;
use App\Models\User\SantriProfile;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Absensi;
use App\Models\Akademik\Penilaian;
use Barryvdh\DomPDF\Facade\Pdf;
use ZipArchive;
use Illuminate\Support\Str;

class EraportController extends Controller
{
    /**
     * Display E-raport index - showing all active students by class
     */
    public function index(Request $request)
    {
        // Get filter parameters
        $selectedTahunId = $request->query('tahun_ajaran_id');
        $selectedKelasId = $request->query('kelas_id');
        $search = $request->query('search');
        
        // Get tahun ajaran based on filter or active one
        $tahunAjaran = null;
        if ($selectedTahunId) {
            $tahunAjaran = TahunAjaran::find($selectedTahunId);
        } else {
            $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        }

        // Get all options for filters
        $allTahunAjaran = TahunAjaran::orderBy('id', 'desc')->get();
        $allKelas = Kelas::where('status', 'Aktif')->orderBy('level')->get();
        
        // Query santris with filters
        $santrisQuery = SantriProfile::with(['santri:id,nisn', 'riwayatKelas.kelas.waliKelas']);
        
        // Search filter
        if ($search) {
            $santrisQuery->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhereHas('santri', function($subQ) use ($search) {
                      $subQ->where('nisn', 'like', "%{$search}%");
                  });
            });
        }
        
        // If tahun ajaran and/or kelas is selected, filter by riwayat kelas
        if ($tahunAjaran || $selectedKelasId) {
            $santrisQuery->whereHas('riwayatKelas', function($q) use ($tahunAjaran, $selectedKelasId) {
                if ($tahunAjaran) {
                    $q->where('tahun_ajaran_id', $tahunAjaran->id);
                }
                if ($selectedKelasId) {
                    $q->where('kelas_id', $selectedKelasId);
                }
            });
        } else {
            // If no filter, show students with active class
            $santrisQuery->whereHas('kelasAktif');
        }
        
        $santris = $santrisQuery->orderBy('nama')->get();

        // Get Kelas object if selected
        $kelas = $selectedKelasId ? Kelas::find($selectedKelasId) : null;

        // Calculate Average Grades and Ranking
        if ($tahunAjaran) {
            // 1. Calculate Averages
            foreach ($santris as $santri) {
                // Get student's class for this tahun ajaran
                $riwayat = $santri->riwayatKelas->where('tahun_ajaran_id', $tahunAjaran->id)->first();
                
                if ($riwayat) {
                    $studentKelas = $riwayat->kelas;
                    
                    // Get all guru mapel for this class and tahun ajaran
                    $guruMapels = GuruMapel::where('kelas_id', $studentKelas->id)
                        ->where('tahun_ajaran_id', $tahunAjaran->id)
                        ->get();
                    
                    $totalNilai = 0;
                    $countMapel = 0;
                    
                    foreach ($guruMapels as $gm) {
                        $avg = $santri->penilaians()
                            ->where('guru_mapel_id', $gm->id)
                            ->avg('nilai');
                        
                        if ($avg) {
                            $totalNilai += $avg;
                            $countMapel++;
                        }
                    }
                    
                    $santri->rata_rata_nilai = $countMapel > 0 ? round($totalNilai / $countMapel, 1) : 0;
                    $santri->kelas_id_for_rank = $studentKelas->id; // Store for grouping
                } else {
                    $santri->rata_rata_nilai = 0;
                    $santri->kelas_id_for_rank = null;
                }
            }

            // 2. Calculate Ranking (Group by Class)
            // We need to rank students within their own class
            $groupedSantris = $santris->groupBy('kelas_id_for_rank');

            foreach ($groupedSantris as $kelasId => $classSantris) {
                if (!$kelasId) continue;

                // Sort by average score descending
                $sortedSantris = $classSantris->sortByDesc('rata_rata_nilai')->values();

                foreach ($classSantris as $santri) {
                    // Find index in sorted array + 1
                    $rank = $sortedSantris->search(function($item) use ($santri) {
                        return $item->id === $santri->id;
                    });
                    $santri->ranking = $rank !== false ? $rank + 1 : '-';
                    $santri->total_siswa_kelas = $sortedSantris->count();
                }
            }
        }
   
        return view('Akademik.E-Raport.index', compact(
            'allTahunAjaran', 
            'allKelas', 
            'santris',
            'selectedTahunId',
            'selectedKelasId',
            'tahunAjaran',
            'kelas',
            'search'
        ));
    }

    public function detailSantri(Request $request, $kelasId, $santriId)
    {
        // Prepare selectable lists
        $tahunAjarans = TahunAjaran::orderBy('id', 'desc')->get();
        $kelasList = Kelas::where('status', 'Aktif')->orderBy('level')->get();

        // Determine selected filters: prefer query params, fallback to active tahun ajaran
        $selectedTahunId = $request->query('tahun_ajaran_id');
        $selectedSemester = $request->query('semester');
        $selectedKelasId = $request->query('kelas_id') ?? $kelasId;

        if ($selectedTahunId) {
            $tahunAjaran = TahunAjaran::find($selectedTahunId);
        } else {
            if ($selectedSemester) {
                $tahunAjaran = TahunAjaran::where('semester', $selectedSemester)
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $tahunAjaran = TahunAjaran::where('is_active', true)->first();
            }
        }
        
        if (!$tahunAjaran) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif');
        }

        $kelas = Kelas::with('waliKelas')->findOrFail($selectedKelasId);
        $santri = SantriProfile::with('santri')->findOrFail($santriId);
        $sekolah = \App\Models\Akademik\SekolahProfile::first();

        // Get subjects
        $guruMapels = GuruMapel::with('mapel')
            ->where('kelas_id', $selectedKelasId)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->get();

        $nilaiMapel = [];
        $totalNilai = 0;
        $countMapel = 0;

        foreach ($guruMapels as $gm) {
            $avg = $santri->penilaians()
                ->where('guru_mapel_id', $gm->id)
                ->avg('nilai');
            
            $nilai = $avg ? round($avg, 0) : null;
            
            // Predikat Logic
            $predikat = '-';
            if ($nilai) {
                if ($nilai >= 92) $predikat = 'A';
                elseif ($nilai >= 83) $predikat = 'B';
                elseif ($nilai >= 75) $predikat = 'C';
                else $predikat = 'D';
            }

            $nilaiMapel[] = [
                'mapel' => $gm->mapel,
                'nilai' => $nilai,
                'predikat' => $predikat,
                'deskripsi' => $nilai ? 'Mencapai kompetensi dengan baik' : '-' 
            ];

            if ($nilai) {
                $totalNilai += $nilai;
                $countMapel++;
            }
        }

        $rataRata = $countMapel > 0 ? round($totalNilai / $countMapel, 1) : 0;

        // Calculate Rank
        $rankData = $this->calculateRank($santriId, $selectedKelasId, $tahunAjaran->id);
        $ranking = $rankData['rank'];
        $totalSiswa = $rankData['total'];

        // Absensi
        $absensiRaw = $santri->absensis()
            ->whereIn('mapel_id', $guruMapels->pluck('mapel_id'))
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->get();

        $absensi = [
            'S' => $absensiRaw->where('status', 'S')->count(),
            'I' => $absensiRaw->where('status', 'I')->count(),
            'A' => $absensiRaw->where('status', 'A')->count(),
        ];

        return view('Akademik.E-Raport.detail', compact(
            'kelas', 'tahunAjaran', 'santri', 'sekolah',
            'nilaiMapel', 'rataRata', 'absensi',
            'tahunAjarans', 'kelasList', 'selectedSemester', 'selectedTahunId', 'selectedKelasId',
            'ranking', 'totalSiswa'
        ));
    }

    private function calculateRank($santriId, $kelasId, $tahunAjaranId)
    {
        // Get all students in the class for this academic year
        $studentsInClass = SantriProfile::whereHas('riwayatKelas', function($q) use ($kelasId, $tahunAjaranId) {
            $q->where('kelas_id', $kelasId)
              ->where('tahun_ajaran_id', $tahunAjaranId);
        })->get();

        $studentAverages = [];

        foreach ($studentsInClass as $student) {
            $guruMapels = GuruMapel::where('kelas_id', $kelasId)
                ->where('tahun_ajaran_id', $tahunAjaranId)
                ->get();
            
            $total = 0;
            $count = 0;

            foreach ($guruMapels as $gm) {
                $avg = $student->penilaians()
                    ->where('guru_mapel_id', $gm->id)
                    ->avg('nilai');
                
                if ($avg) {
                    $total += $avg;
                    $count++;
                }
            }

            $average = $count > 0 ? $total / $count : 0;
            $studentAverages[] = [
                'id' => $student->id,
                'average' => $average
            ];
        }

        // Sort by average descending
        usort($studentAverages, function($a, $b) {
            return $b['average'] <=> $a['average'];
        });

        // Find rank
        $rank = '-';
        foreach ($studentAverages as $index => $data) {
            if ($data['id'] == $santriId) {
                $rank = $index + 1;
                break;
            }
        }

        return ['rank' => $rank, 'total' => count($studentAverages)];
    }

    private function buildRaportData(SantriProfile $santri, $kelasId, TahunAjaran $tahunAjaran)
    {
        $kelas = Kelas::with('waliKelas')->findOrFail($kelasId);
        $sekolah = \App\Models\Akademik\SekolahProfile::first();

        $guruMapels = GuruMapel::with('mapel')
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->get();

        $nilaiMapel = [];
        $totalNilai = 0;
        $countMapel = 0;

        foreach ($guruMapels as $gm) {
            $avg = $santri->penilaians()
                ->where('guru_mapel_id', $gm->id)
                ->avg('nilai');
            
            $nilai = $avg ? round($avg, 0) : null;
            
            $predikat = '-';
            if ($nilai !== null) {
                if ($nilai >= 92) $predikat = 'A';
                elseif ($nilai >= 83) $predikat = 'B';
                elseif ($nilai >= 75) $predikat = 'C';
                else $predikat = 'D';
            }

            $nilaiMapel[] = [
                'mapel'     => $gm->mapel,
                'nilai'     => $nilai,
                'predikat'  => $predikat,
                'kkm'       => 75,
                'deskripsi' => $nilai !== null ? 'Mencapai kompetensi dengan baik' : '-' 
            ];

            if ($nilai !== null) {
                $totalNilai += $nilai;
                $countMapel++;
            }
        }

        $rataRata = $countMapel > 0 ? round($totalNilai / $countMapel, 1) : 0;

        // Calculate Rank
        $rankData = $this->calculateRank($santri->id, $kelasId, $tahunAjaran->id);
        $ranking = $rankData['rank'];
        $totalSiswa = $rankData['total'];

        $absensiRaw = $santri->absensis()
            ->whereIn('mapel_id', $guruMapels->pluck('mapel_id'))
            ->where('kelas_id', $kelasId)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->get();

        $absensi = [
            'S' => $absensiRaw->where('status', 'S')->count(),
            'I' => $absensiRaw->where('status', 'I')->count(),
            'A' => $absensiRaw->where('status', 'A')->count(),
        ];

        return compact('kelas', 'sekolah', 'guruMapels', 'nilaiMapel', 'rataRata', 'absensi', 'ranking', 'totalSiswa');
    }

    public function cetakSantri($kelasId, $santriId)
    {
        $request = request();
        $selectedTahunId = $request->query('tahun_ajaran_id');
        $selectedSemester = $request->query('semester');

        if ($selectedTahunId) {
            $tahunAjaran = TahunAjaran::find($selectedTahunId);
        } else {
            if ($selectedSemester) {
                $tahunAjaran = TahunAjaran::where('semester', $selectedSemester)
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $tahunAjaran = TahunAjaran::where('is_active', true)->first();
            }
        }
        
        if (!$tahunAjaran) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif');
        }

        $santri = SantriProfile::with('santri')->findOrFail($santriId);

        $raportData = $this->buildRaportData($santri, $kelasId, $tahunAjaran);

        $pdf = Pdf::loadView('Akademik.E-Raport.cetak-persantri', array_merge(
            $raportData,
            [
                'santri'      => $santri,
                'tahunAjaran' => $tahunAjaran,
            ]
        ));
        
        $pdf->setPaper('A4', 'portrait');
        
        $filename = 'Raport_' . Str::slug($santri->nama, '_') . '_' . $raportData['kelas']->nama_kelas . '.pdf';
        
        return $pdf->download($filename);
    }

    public function cetakSemua(Request $request, $kelasId = null)
    {
        $selectedTahunId = $request->query('tahun_ajaran_id');
        $selectedSemester = $request->query('semester');

        if ($selectedTahunId) {
            $tahunAjaran = TahunAjaran::find($selectedTahunId);
        } else {
            if ($selectedSemester) {
                $tahunAjaran = TahunAjaran::where('semester', $selectedSemester)
                    ->orderBy('id', 'desc')
                    ->first();
            } else {
                $tahunAjaran = TahunAjaran::where('is_active', true)->first();
            }
        }
        
        if (!$tahunAjaran) {
            return redirect()->back()->with('error', 'Tidak ada tahun ajaran aktif');
        }

        $santrisQuery = SantriProfile::with(['santri', 'riwayatKelas.kelas.waliKelas']);

        if ($kelasId) {
            $kelas = Kelas::with('waliKelas')->findOrFail($kelasId);
            $santrisQuery->whereHas('riwayatKelas', function($q) use ($kelasId, $tahunAjaran) {
                $q->where('kelas_id', $kelasId)
                  ->where('tahun_ajaran_id', $tahunAjaran->id);
            });
            $zipFileName = 'Raport_Kelas_' . Str::slug($kelas->nama_kelas, '_') . '.zip';
        } else {
            // If no class selected, get all students with history in this academic year
            $santrisQuery->whereHas('riwayatKelas', function($q) use ($tahunAjaran) {
                $q->where('tahun_ajaran_id', $tahunAjaran->id);
            });
            $zipFileName = 'Raport_Semua_Siswa_' . Str::slug($tahunAjaran->nama, '_') . '.zip';
        }

        $santris = $santrisQuery->orderBy('nama')->get();

        if ($santris->isEmpty()) {
            return redirect()->back()->with('error', 'Tidak ada santri ditemukan');
        }

        // path file zip sementara
        $zipPath = storage_path('app/temp_raport_' . time() . '.zip');

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return redirect()->back()->with('error', 'Gagal membuat file ZIP');
        }

        foreach ($santris as $santri) {
            // Determine class for this student
            $riwayat = $santri->riwayatKelas->where('tahun_ajaran_id', $tahunAjaran->id)->first();
            if (!$riwayat) continue; // Should not happen due to query, but safety check
            
            $studentKelasId = $riwayat->kelas_id;
            $studentKelas = $riwayat->kelas;

            // build data raport per santri
            $raportData = $this->buildRaportData($santri, $studentKelasId, $tahunAjaran);

            $pdf = Pdf::loadView('Akademik.E-Raport.cetak-persantri', array_merge(
                $raportData,
                [
                    'santri'      => $santri,
                    'tahunAjaran' => $tahunAjaran,
                ]
            ));

            $pdf->setPaper('A4', 'portrait');

            $pdfContent = $pdf->output();

            $pdfFileName = 'Raport_' . Str::slug($santri->nama, '_') . '_' . Str::slug($studentKelas->nama_kelas, '_') . '.pdf';

            // masukin ke zip tanpa nyimpan fisik masing2 pdf di disk
            $zip->addFromString($pdfFileName, $pdfContent);
        }

        $zip->close();

        // download zip dan hapus setelah dikirim
        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }
}
