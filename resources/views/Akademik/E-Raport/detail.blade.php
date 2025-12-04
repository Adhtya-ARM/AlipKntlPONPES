@extends('layouts.app')

@section('title', 'Detail Raport - ' . $santri->nama)

@section('content')
<div class="min-h-screen bg-gray-50 py-8" x-data>
    <div class="max-w-7xl mx-auto px-6">
        
        {{-- Header & Actions --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Hasil Belajar</h1>
                <p class="text-gray-600 mt-1 flex items-center gap-2">
                    <a href="{{ route('akademik.eraport.index', ['kelas_id' => $kelas->id, 'tahun_ajaran_id' => $selectedTahunId, 'semester' => $selectedSemester]) }}" class="hover:text-green-600 hover:underline transition">
                        E-Raport
                    </a>
                    <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                    <span class="text-gray-900 font-medium">{{ $santri->nama }}</span>
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('akademik.eraport.index', ['kelas_id' => $kelas->id, 'tahun_ajaran_id' => $selectedTahunId, 'semester' => $selectedSemester]) }}" 
                   class="px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 transition font-medium shadow-sm flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </a>
                <a href="{{ route('akademik.eraport.cetak-santri', ['kelas' => $kelas->id, 'santri' => $santri->id, 'tahun_ajaran_id' => $selectedTahunId, 'semester' => $selectedSemester]) }}" 
                   target="_blank"
                   class="px-5 py-2.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white rounded-xl transition font-medium shadow-lg hover:shadow-xl flex items-center gap-2">
                    <i class="fas fa-print"></i> Cetak Raport PDF
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            
            {{-- Left Column: Student Profile & Stats --}}
            <div class="lg:col-span-4 space-y-6">
                {{-- Profile Card --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden relative">
                    <div class="h-32 bg-gradient-to-br from-green-500 to-emerald-600"></div>
                    <div class="px-6 pb-6">
                        <div class="relative flex justify-center -mt-16 mb-4">
                            <div class="w-32 h-32 bg-white rounded-full p-1 shadow-xl">
                                <div class="w-full h-full rounded-full bg-gray-100 flex items-center justify-center text-4xl font-bold text-gray-400">
                                    {{ strtoupper(substr($santri->nama, 0, 1)) }}
                                </div>
                            </div>
                        </div>
                        <div class="text-center mb-6">
                            <h2 class="text-xl font-bold text-gray-900">{{ $santri->nama }}</h2>
                            <p class="text-gray-500 text-sm mt-1 font-mono">{{ $santri->santri->nisn ?? 'NISN Tidak Ada' }}</p>
                        </div>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-500 text-sm">Kelas</span>
                                <span class="font-bold text-gray-900">{{ $kelas->level }} - {{ $kelas->nama_kelas }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-500 text-sm">Semester</span>
                                <span class="font-bold text-gray-900">{{ ucfirst($tahunAjaran->semester) }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-500 text-sm">Tahun Ajaran</span>
                                <span class="font-bold text-gray-900">{{ $tahunAjaran->nama }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-xl">
                                <span class="text-gray-500 text-sm">Wali Kelas</span>
                                <span class="font-bold text-gray-900 text-right text-sm">{{ $kelas->waliKelas->nama ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Ranking Card --}}
                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white opacity-10 rounded-full -mr-10 -mt-10 blur-2xl"></div>
                    <div class="relative z-10">
                        <h3 class="text-indigo-100 font-medium mb-1">Peringkat Kelas</h3>
                        <div class="flex items-end gap-2">
                            <span class="text-5xl font-bold">{{ $ranking }}</span>
                            <span class="text-indigo-200 mb-2">/ {{ $totalSiswa }} Siswa</span>
                        </div>
                        <div class="mt-4 pt-4 border-t border-white/20 flex justify-between items-center">
                            <span class="text-indigo-100 text-sm">Rata-Rata Nilai</span>
                            <span class="text-2xl font-bold">{{ $rataRata }}</span>
                        </div>
                    </div>
                </div>

                {{-- Attendance Card --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-calendar-check text-green-600 mr-2"></i> Kehadiran
                    </h3>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                            <div class="text-2xl font-bold text-blue-600">{{ $absensi['S'] }}</div>
                            <div class="text-xs text-blue-600 font-bold mt-1 uppercase tracking-wide">Sakit</div>
                        </div>
                        <div class="bg-yellow-50 rounded-xl p-4 border border-yellow-100">
                            <div class="text-2xl font-bold text-yellow-600">{{ $absensi['I'] }}</div>
                            <div class="text-xs text-yellow-600 font-bold mt-1 uppercase tracking-wide">Izin</div>
                        </div>
                        <div class="bg-red-50 rounded-xl p-4 border border-red-100">
                            <div class="text-2xl font-bold text-red-600">{{ $absensi['A'] }}</div>
                            <div class="text-xs text-red-600 font-bold mt-1 uppercase tracking-wide">Alpha</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Grades --}}
            <div class="lg:col-span-8 space-y-6">
                
                {{-- Grades Table --}}
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                    <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Daftar Nilai Akademik</h3>
                            <p class="text-gray-500 text-sm mt-1">Hasil penilaian mata pelajaran semester ini</p>
                        </div>
                        <div class="w-12 h-12 bg-green-50 rounded-xl flex items-center justify-center text-green-600">
                            <i class="fas fa-book-open text-xl"></i>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="bg-gray-50/50">
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider w-12">No</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-20">KKM</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Nilai</th>
                                    <th class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-24">Predikat</th>
                                    <th class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Deskripsi Capaian</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-100">
                                @forelse($nilaiMapel as $index => $nilai)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 text-sm text-gray-500 text-center">{{ $loop->iteration }}</td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $nilai['mapel']->nama_mapel }}</div>
                                        <div class="text-xs text-gray-400 mt-0.5">{{ $nilai['mapel']->kode_mapel ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500 text-center font-medium">{{ $nilai['kkm'] ?? 75 }}</td>
                                    <td class="px-6 py-4 text-center">
                                        @if(isset($nilai['nilai']))
                                            <span class="text-sm font-bold text-gray-900">{{ $nilai['nilai'] }}</span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($nilai['predikat'] != '-')
                                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-bold
                                                {{ $nilai['predikat'] == 'A' ? 'bg-green-100 text-green-700' : 
                                                   ($nilai['predikat'] == 'B' ? 'bg-blue-100 text-blue-700' : 
                                                   ($nilai['predikat'] == 'C' ? 'bg-yellow-100 text-yellow-700' : 
                                                    'bg-red-100 text-red-700')) }}">
                                                {{ $nilai['predikat'] }}
                                            </span>
                                        @else
                                            <span class="text-gray-300">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-600 leading-relaxed">
                                        {{ $nilai['deskripsi'] }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-16 text-center text-gray-500">
                                        <div class="flex flex-col items-center justify-center">
                                            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                                <i class="fas fa-clipboard-list text-gray-400 text-2xl"></i>
                                            </div>
                                            <p class="font-medium">Belum ada data nilai untuk semester ini.</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection