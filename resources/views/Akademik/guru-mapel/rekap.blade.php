@extends('layouts.app')

@section('title', 'Rekap Mapel - ' . $guruMapel->mapel->nama_mapel)

@section('content')
<div class="container mx-auto p-6">
    
    {{-- HEADER --}}
    <div class="mb-6">
        <a href="{{ route('akademik.guru-mapel.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150 mb-4">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar Mapel
        </a>
        <h1 class="text-3xl font-bold text-gray-800">Rekap: {{ $guruMapel->mapel->nama_mapel }}</h1>
        <p class="text-sm text-gray-500 mt-1">
            Kelas: {{ $guruMapel->kelas->tingkat }} {{ $guruMapel->kelas->nama_unik }} | 
            Semester {{ $guruMapel->semester }} | 
            {{ $guruMapel->tahun_ajaran }}
        </p>
    </div>

    {{-- STATISTIK CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-users text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Total Siswa</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $siswa->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Rata-rata Kehadiran</p>
                    <p class="text-2xl font-bold text-gray-800">{{ number_format($siswa->avg('kehadiran.persentase'), 1) }}%</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-star text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Rata-rata Nilai</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ number_format($siswa->whereNotNull('nilai_data.rata_rata')->avg('nilai_data.rata_rata'), 1) }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-clipboard-check text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-500">Sudah Dinilai</p>
                    <p class="text-2xl font-bold text-gray-800">
                        {{ $siswa->whereNotNull('nilai_data.rata_rata')->count() }} / {{ $siswa->count() }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- TABEL REKAP --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 bg-gray-50 border-b flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-800">Rekap Nilai & Kehadiran Siswa</h2>
            <div class="flex gap-2">
                <button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 text-sm">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </button>
                <button class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 text-sm">
                    <i class="fas fa-file-pdf mr-1"></i> Export PDF
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase border">#</th>
                        <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase border">NIS</th>
                        <th rowspan="2" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase border">Nama Siswa</th>
                        <th colspan="4" class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase border bg-blue-50">Nilai</th>
                        <th colspan="5" class="px-4 py-3 text-center text-xs font-semibold text-gray-700 uppercase border bg-green-50">Kehadiran</th>
                    </tr>
                    <tr>
                        {{-- Nilai Headers --}}
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border bg-blue-50">Tugas</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border bg-blue-50">UTS</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border bg-blue-50">UAS</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border bg-blue-50">Rata-rata</th>
                        {{-- Kehadiran Headers --}}
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border bg-green-50">H</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border bg-green-50">S</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border bg-green-50">I</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border bg-green-50">A</th>
                        <th class="px-4 py-2 text-center text-xs font-semibold text-gray-700 uppercase border bg-green-50">%</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($siswa as $index => $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-600 border">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600 border">{{ $s->santri->nisn ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-800 border">{{ $s->nama }}</td>
                        
                        {{-- Nilai --}}
                        <td class="px-4 py-3 text-center text-sm border">
                            {{ $s->nilai_data['tugas'] ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm border">
                            {{ $s->nilai_data['uts'] ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm border">
                            {{ $s->nilai_data['uas'] ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold border">
                            @if($s->nilai_data['rata_rata'])
                                <span class="px-2 py-1 rounded text-white {{ $s->nilai_data['rata_rata'] >= 75 ? 'bg-green-500' : ($s->nilai_data['rata_rata'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}">
                                    {{ number_format($s->nilai_data['rata_rata'], 1) }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        
                        {{-- Kehadiran --}}
                        <td class="px-4 py-3 text-center text-sm border">
                            <span class="text-green-600 font-medium">{{ $s->kehadiran['hadir'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm border">
                            <span class="text-yellow-600 font-medium">{{ $s->kehadiran['sakit'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm border">
                            <span class="text-blue-600 font-medium">{{ $s->kehadiran['izin'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm border">
                            <span class="text-red-600 font-medium">{{ $s->kehadiran['alpha'] }}</span>
                        </td>
                        <td class="px-4 py-3 text-center text-sm font-semibold border">
                            @if($s->kehadiran['total'] > 0)
                                <span class="px-2 py-1 rounded text-white {{ $s->kehadiran['persentase'] >= 80 ? 'bg-green-500' : ($s->kehadiran['persentase'] >= 60 ? 'bg-yellow-500' : 'bg-red-500') }}">
                                    {{ number_format($s->kehadiran['persentase'], 1) }}%
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="12" class="px-6 py-8 text-center text-gray-500">
                            Belum ada data siswa
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- KETERANGAN --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-blue-800 mb-2">Keterangan:</h3>
        <div class="grid grid-cols-2 gap-2 text-xs text-blue-700">
            <div><strong>H:</strong> Hadir</div>
            <div><strong>S:</strong> Sakit</div>
            <div><strong>I:</strong> Izin</div>
            <div><strong>A:</strong> Alpha</div>
            <div><strong>%:</strong> Persentase Kehadiran (Hadir / Total Pertemuan)</div>
        </div>
    </div>
</div>
@endsection
