@extends('layouts.app')

@section('title', 'E-Raport Madrasah')

@section('content')
<div class="min-h-screen bg-gray-50 py-8" x-data="{ 
    tahunAjaranId: '{{ request('tahun_ajaran_id', '') }}',
    kelasId: '{{ request('kelas_id', '') }}',
    search: '{{ request('search', '') }}',
    searchTimeout: null,
    
    submitForm() {
        $refs.filterForm.submit();
    },
    
    debounceSearch() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            this.submitForm();
        }, 800);
    }
}">
    <div class="max-w-7xl mx-auto px-6">
        
        {{-- Header Section --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8 mb-8 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-64 h-64 bg-green-50 rounded-full -mr-32 -mt-32 opacity-50 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-blue-50 rounded-full -ml-32 -mb-32 opacity-50 blur-3xl"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="flex items-center gap-6">
                    <div class="w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg transform rotate-3 hover:rotate-0 transition-all duration-300">
                        <i class="fas fa-award text-white text-4xl"></i> 
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 tracking-tight">E-Raport Madrasah</h1>
                        <p class="text-gray-500 mt-2 text-lg">Sistem Laporan Hasil Belajar & Peringkat Siswa</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-3">
                    @if(isset($selectedKelasId) && $selectedKelasId)
                        <a href="{{ route('akademik.eraport.cetak-semua', ['kelas' => $selectedKelasId, 'tahun_ajaran_id' => $selectedTahunId]) }}"
                           target="_blank"
                           class="group relative inline-flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl transition-all shadow-md hover:shadow-lg overflow-hidden">
                            <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                            <i class="fas fa-file-pdf relative z-10"></i> 
                            <span class="font-semibold relative z-10">Export PDF Semua</span>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Filter Section --}}
            <div class="mt-8 pt-8 border-t border-gray-100">
                <form method="GET" x-ref="filterForm" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    {{-- Tahun Ajaran --}}
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">
                            <i class="fas fa-calendar-alt mr-1 text-green-600"></i> Tahun Ajaran
                        </label>
                        <div class="relative">
                            <select name="tahun_ajaran_id" 
                                    x-model="tahunAjaranId"
                                    @change="submitForm()"
                                    class="block w-full pl-4 pr-10 py-3 text-sm rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 bg-gray-50 focus:bg-white shadow-sm transition-all appearance-none cursor-pointer">
                                <option value="">-- Semua Tahun Ajaran --</option>
                                @foreach($allTahunAjaran as $ta)
                                    <option value="{{ $ta->id }}">
                                        {{ $ta->nama }} - {{ ucfirst($ta->semester) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Kelas --}}
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">
                            <i class="fas fa-school mr-1 text-green-600"></i> Kelas
                        </label>
                        <div class="relative">
                            <select name="kelas_id" 
                                    x-model="kelasId"
                                    @change="submitForm()"
                                    class="block w-full pl-4 pr-10 py-3 text-sm rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 bg-gray-50 focus:bg-white shadow-sm transition-all appearance-none cursor-pointer">
                                <option value="">-- Semua Kelas --</option>
                                @foreach($allKelas as $kls)
                                    <option value="{{ $kls->id }}">
                                        {{ $kls->level }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                <i class="fas fa-chevron-down text-xs"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Search --}}
                    <div class="group">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 ml-1">
                            <i class="fas fa-search mr-1 text-green-600"></i> Cari Siswa
                        </label>
                        <div class="relative">
                            <input type="text" 
                                   name="search" 
                                   x-model="search"
                                   @input="debounceSearch()"
                                   placeholder="Nama atau NISN..." 
                                   class="block w-full pl-10 pr-4 py-3 text-sm rounded-xl border-gray-200 focus:border-green-500 focus:ring-green-500 bg-gray-50 focus:bg-white shadow-sm transition-all">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Content Section --}}
        @if(isset($santris) && $santris->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm p-16 text-center border border-gray-100">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-user-graduate text-gray-300 text-5xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Data Siswa Tidak Ditemukan</h3>
                <p class="text-gray-500 max-w-md mx-auto">
                    Belum ada siswa yang terdaftar sesuai filter yang dipilih. Silakan sesuaikan filter pencarian Anda.
                </p>
            </div>
        @else
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <div class="px-8 py-6 bg-white border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-800 flex items-center gap-3">
                        <span class="w-8 h-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                            <i class="fas fa-list-ol"></i>
                        </span>
                        Daftar Peringkat Siswa
                    </h3>
                    <div class="text-sm text-gray-500">
                        Total <span class="font-bold text-gray-900">{{ $santris->count() }}</span> Siswa
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-100">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-5 text-center w-20 text-xs font-bold text-gray-500 uppercase tracking-wider">Rank</th>
                                <th class="px-6 py-5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Siswa</th>
                                <th class="px-6 py-5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">NISN</th>
                                <th class="px-6 py-5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Rata-Rata</th>
                                <th class="px-6 py-5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Predikat</th>
                                <th class="px-6 py-5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @foreach($santris as $index => $santri)
                            @php
                                // Determine class for this student
                                $kelasSiswa = null;
                                if($tahunAjaran) {
                                    $riwayat = $santri->riwayatKelas->where('tahun_ajaran_id', $tahunAjaran->id)->first();
                                    $kelasSiswa = $riwayat ? $riwayat->kelas : null;
                                } else {
                                    $kelasSiswa = $santri->kelasAktif ? $santri->kelasAktif->kelas : null;
                                }
                                
                                // Calculate predicate
                                $predikat = '-';
                                if(isset($santri->rata_rata_nilai) && $santri->rata_rata_nilai > 0) {
                                    $nilai = $santri->rata_rata_nilai;
                                    if($nilai >= 92) $predikat = 'A';
                                    elseif($nilai >= 83) $predikat = 'B';
                                    elseif($nilai >= 75) $predikat = 'C';
                                    else $predikat = 'D';
                                }

                                // Ranking Badge Color
                                $rank = $santri->ranking ?? '-';
                                $rankColor = 'bg-gray-100 text-gray-600';
                                if($rank == 1) $rankColor = 'bg-yellow-100 text-yellow-700 ring-4 ring-yellow-50';
                                elseif($rank == 2) $rankColor = 'bg-gray-200 text-gray-700 ring-4 ring-gray-50';
                                elseif($rank == 3) $rankColor = 'bg-orange-100 text-orange-800 ring-4 ring-orange-50';
                            @endphp
                            <tr class="group hover:bg-green-50/30 transition-colors duration-200">
                                <td class="px-6 py-5 text-center">
                                    <div class="inline-flex items-center justify-center w-10 h-10 rounded-full font-bold text-sm {{ $rankColor }}">
                                        {{ $rank }}
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center text-white font-bold text-lg shadow-sm group-hover:scale-110 transition-transform duration-200">
                                            {{ strtoupper(substr($santri->nama, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-900 text-base">{{ $santri->nama }}</div>
                                            @if($kelasSiswa)
                                                <div class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                                                    <i class="fas fa-chalkboard-teacher text-gray-400"></i>
                                                    {{ $kelasSiswa->nama_kelas }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <span class="font-mono text-sm text-gray-600 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">
                                        {{ $santri->santri->nisn ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if(isset($santri->rata_rata_nilai) && $santri->rata_rata_nilai > 0)
                                        <div class="text-lg font-bold text-gray-900">
                                            {{ number_format($santri->rata_rata_nilai, 1) }}
                                        </div>
                                    @else
                                        <span class="text-gray-300 font-bold">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($predikat != '-')
                                        <span class="inline-flex items-center justify-center px-4 py-1.5 rounded-full font-bold text-sm
                                            {{ $predikat == 'A' ? 'bg-green-100 text-green-700' : 
                                               ($predikat == 'B' ? 'bg-blue-100 text-blue-700' : 
                                               ($predikat == 'C' ? 'bg-yellow-100 text-yellow-700' : 
                                                'bg-red-100 text-red-700')) }}">
                                            {{ $predikat }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 font-bold">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @if($kelasSiswa)
                                        <a href="{{ route('akademik.eraport.detail-santri', ['kelas' => $kelasSiswa->id, 'santri' => $santri->id, 'tahun_ajaran_id' => $selectedTahunId]) }}" 
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-200 hover:border-green-500 hover:text-green-600 text-gray-600 rounded-lg transition-all shadow-sm hover:shadow text-sm font-semibold">
                                            <i class="fas fa-eye"></i> Detail
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection