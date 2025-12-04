@extends('layouts.app')

@section('title', 'Riwayat Pembelajaran')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-archive text-white text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Riwayat Pembelajaran</h2>
                    <p class="text-gray-600 mt-1">Data historis semester yang telah diarsipkan</p>
                </div>
            </div>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex">
                    <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Informasi:</strong> Halaman ini menampilkan data pembelajaran dari semester yang sudah diarsipkan. 
                            Data bersifat <strong>read-only</strong> dan tidak dapat diubah.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        @if($archivedSemesters->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm p-16 text-center">
                <div class="w-24 h-24 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-archive text-amber-500 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-800 mb-2">Belum Ada Riwayat</h3>
                <p class="text-gray-600 max-w-lg mx-auto mb-4">
                    Anda belum memiliki riwayat pembelajaran dari semester yang telah diarsipkan.
                </p>
                <p class="text-sm text-gray-500 max-w-lg mx-auto">
                    Riwayat akan muncul di sini setelah Anda mengajar pada semester yang kemudian diarsipkan oleh admin.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($archivedSemesters as $semester)
                    <a href="{{ route('akademik.guru.arsip.show', $semester->id) }}" 
                       class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-amber-400 overflow-hidden">
                        
                        {{-- Card Header --}}
                        <div class="bg-gradient-to-br from-amber-600 to-orange-600 p-6 text-white">
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-2xl font-bold mb-2">{{ $semester->nama }}</h3>
                                    <div class="flex items-center gap-2">
                                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $semester->semester == 'Genap' ? 'bg-blue-400 text-blue-900' : 'bg-yellow-400 text-yellow-900' }}">
                                            {{ ucfirst($semester->semester) }}
                                        </span>
                                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/20">
                                            {{ $semester->jenjang ?? 'Semua' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <i class="fas fa-calendar-alt text-2xl"></i>
                                </div>
                            </div>

                            {{-- Stats --}}
                            <div class="grid grid-cols-2 gap-3 mt-4">
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                                    <div class="text-xs text-amber-100 mb-1">Kelas/Mapel</div>
                                    <div class="text-2xl font-bold">{{ $semester->guru_mapels_count }}</div>
                                </div>
                                <div class="bg-white/10 backdrop-blur-sm rounded-lg p-3">
                                    <div class="text-xs text-amber-100 mb-1">Status</div>
                                    <div class="text-sm font-bold flex items-center gap-1">
                                        <i class="fas fa-lock text-xs"></i>
                                        <span>Terarsip</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="p-6 bg-gradient-to-br from-gray-50 to-white">
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-gray-500">
                                    <i class="fas fa-clock mr-1"></i>
                                    Diarsipkan: {{ $semester->updated_at->format('d M Y') }}
                                </div>
                                <div class="flex items-center gap-2 text-amber-600 font-semibold text-sm group-hover:text-amber-700">
                                    <span>Lihat Detail</span>
                                    <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition-transform"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
