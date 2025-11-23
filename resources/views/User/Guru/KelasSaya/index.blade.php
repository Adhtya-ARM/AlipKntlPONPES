@extends('layouts.app')

@section('title', 'Kelas Saya')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    {{-- Header --}}
    <div class="mb-8">
        <h2 class="text-3xl font-bold text-gray-800">Kelas Saya</h2>
        <p class="text-gray-600 mt-2">Daftar kelas dan mata pelajaran yang Anda ampu.</p>
    </div>

    {{-- Content --}}
    @if(count($dataKelas) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($dataKelas as $item)
                <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition duration-300 border border-gray-100 overflow-hidden group">
                    {{-- Card Header --}}
                    <div class="bg-gradient-to-r from-blue-600 to-blue-500 p-4">
                        <div class="flex justify-between items-center">
                            <h3 class="text-xl font-bold text-white">
                                {{ $item['kelas']->level }} {{ $item['kelas']->nama_unik }}
                            </h3>
                            <div class="bg-white/20 backdrop-blur-sm rounded-full p-2">
                                <i class="fas fa-chalkboard text-white"></i>
                            </div>
                        </div>
                        <div class="mt-2 text-blue-100 text-xs flex items-center gap-1">
                            <i class="fas fa-user-graduate text-[10px]"></i>
                            <span>{{ $item['kelas']->santriKelas->count() ?? 0 }} Siswa</span>
                        </div>
                    </div>

                    {{-- Card Body --}}
                    <div class="p-5">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Mata Pelajaran</h4>
                        <div class="space-y-2">
                            @foreach($item['mapels'] as $mapel)
                                <div class="flex items-center justify-between text-gray-700 bg-gray-50 p-3 rounded-lg group-hover:bg-blue-50 transition-colors border border-gray-100">
                                    <div class="flex items-center gap-3">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                        <span class="font-medium text-sm">{{ $mapel->nama_mapel }}</span>
                                    </div>
                                    {{-- Action Button (Optional) --}}
                                    {{-- <a href="#" class="text-gray-400 hover:text-blue-600 transition">
                                        <i class="fas fa-chevron-right"></i>
                                    </a> --}}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Card Footer --}}
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex justify-between items-center">
                        <span class="text-xs text-gray-500 font-medium">
                            {{ count($item['mapels']) }} Mapel Diampu
                        </span>
                        {{-- Link ke detail kelas jika ada --}}
                        {{-- <a href="#" class="text-xs text-blue-600 hover:text-blue-700 font-semibold">Lihat Detail</a> --}}
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-12 bg-white rounded-xl shadow-sm border border-gray-100 text-center">
            <div class="bg-blue-50 p-4 rounded-full mb-4">
                <i class="fas fa-book-open text-4xl text-blue-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Belum ada kelas</h3>
            <p class="text-gray-500 mt-2 max-w-sm">Anda belum ditugaskan mengajar di kelas manapun. Silakan hubungi admin akademik.</p>
        </div>
    @endif
</div>
@endsection
