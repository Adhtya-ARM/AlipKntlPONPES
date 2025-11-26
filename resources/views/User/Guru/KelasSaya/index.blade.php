@extends('layouts.app')

@section('title', 'Kelas Saya')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Kelas Saya</h2>
        <p class="text-gray-600 mt-2">Daftar kelas dan mata pelajaran yang Anda ampu dengan informasi lengkap.</p>
    </div>

    {{-- Content --}}
    @if(count($dataKelas) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($dataKelas as $item)
                <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition duration-300 border border-gray-100 overflow-hidden">
                    {{-- Card Header dengan Gradient --}}
                    <div class="bg-gradient-to-br from-blue-600 to-blue-500 p-6 text-white">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <i class="fas fa-school text-2xl"></i>
                                </div>
                                <div>
                                    <h3 class="text-2xl font-bold">
                                        Kelas {{ $item['kelas']->level }}
                                    </h3>
                                    <p class="text-blue-100 text-sm">Wali Kelas: {{ $item['kelas']->waliKelas->nama ?? 'Belum ada' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Statistik Utama --}}
                        <div class="grid grid-cols-2 gap-3 mt-4">
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                                <div class="flex items-center gap-2 text-blue-100 text-xs mb-1">
                                    <i class="fas fa-users"></i>
                                    <span>Total Siswa</span>
                                </div>
                                <div class="text-2xl font-bold">{{ $item['kelas']->santriKelas->count() ?? 0 }}</div>
                            </div>
                            <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                                <div class="flex items-center gap-2 text-blue-100 text-xs mb-1">
                                    <i class="fas fa-book"></i>
                                    <span>Mapel Diampu</span>
                                </div>
                                <div class="text-2xl font-bold">{{ count($item['mapels']) }}</div>
                            </div>
                        </div>
                    </div>

                    {{-- Card Body - Daftar Mapel --}}
                    <div class="p-6">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                            <i class="fas fa-list"></i> Mata Pelajaran
                        </h4>
                        <div class="space-y-2 max-h-60 overflow-y-auto">
                            @foreach($item['mapels'] as $mapel)
                                <div class="group">
                                    <div class="flex items-center justify-between bg-gradient-to-r from-gray-50 to-white hover:from-blue-50 hover:to-blue-50 p-4 rounded-xl transition-all border border-gray-100 hover:border-blue-200">
                                        <div class="flex items-center gap-3 flex-1">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-500 transition">
                                                <i class="fas fa-book-open text-blue-600 group-hover:text-white transition"></i>
                                            </div>
                                            <div class="flex-1">
                                                <span class="font-semibold text-gray-800 block">{{ $mapel->nama_mapel }}</span>
                                                <div class="flex items-center gap-3 mt-1">
                                                    <span class="text-xs text-gray-500 flex items-center gap-1">
                                                        <i class="fas fa-calendar-alt text-[10px]"></i>
                                                        {{ $mapel->pivot->semester == 'ganjil' ? 'Ganjil' : 'Genap' }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 flex items-center gap-1">
                                                        <i class="fas fa-clock text-[10px]"></i>
                                                        {{ $mapel->pivot->tahun_ajaran }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        {{-- Quick Action --}}
                                        @php
                                            $guruMapel = $item['guru_mapels']->where('mapel_id', $mapel->id)->first();
                                        @endphp
                                        @if($guruMapel)
                                        <div class="flex gap-2">
                                            <a href="{{ route('akademik.absensi.index', ['guru_mapel_id' => $guruMapel->id]) }}" 
                                               class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center hover:bg-green-500 group/btn transition" 
                                               title="Input Absensi">
                                                <i class="fas fa-clipboard-check text-green-600 group-hover/btn:text-white text-sm"></i>
                                            </a>
                                            <a href="{{ route('akademik.penilaian.index', ['guru_mapel_id' => $guruMapel->id]) }}" 
                                               class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center hover:bg-yellow-500 group/btn transition" 
                                               title="Input Nilai">
                                                <i class="fas fa-star text-yellow-600 group-hover/btn:text-white text-sm"></i>
                                            </a>
                                            <a href="{{ route('akademik.guru-mapel.rekap', $guruMapel->id) }}" 
                                               class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center hover:bg-blue-500 group/btn transition" 
                                               title="Lihat Rekap">
                                                <i class="fas fa-chart-bar text-blue-600 group-hover/btn:text-white text-sm"></i>
                                            </a>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card Footer - Ringkasan --}}
                    <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
                        <div class="flex justify-between items-center">
                            <div class="flex items-center gap-4 text-xs">
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                                    <span class="text-gray-600 font-medium">{{ $item['kelas']->santriKelas->where('status', 'aktif')->count() ?? 0 }} Aktif</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <span class="text-gray-600 font-medium">{{ count($item['mapels']) }} Mapel</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100 text-center">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-full mb-6">
                <i class="fas fa-book-open text-5xl text-blue-400"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-2">Belum ada kelas</h3>
            <p class="text-gray-500 max-w-md mt-2 mb-6">Anda belum ditugaskan mengajar di kelas manapun. Silakan hubungi admin akademik untuk penugasan kelas.</p>
        </div>
    @endif
</div>
@endsection
