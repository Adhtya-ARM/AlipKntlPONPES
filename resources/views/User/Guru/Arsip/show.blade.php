@extends('layouts.app')

@section('title', 'Detail Arsip Semester - ' . $semester->nama)

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <a href="{{ route('akademik.guru.arsip.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Riwayat
            </a>
            
            <div class="bg-white rounded-2xl shadow-lg border-2 border-amber-200 p-8">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-6">
                        <div class="w-20 h-20 bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg">
                            <i class="fas fa-archive text-white text-4xl"></i>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-gray-800">{{ $semester->nama }}</h2>
                            <div class="flex items-center gap-3 mt-2">
                                <span class="px-4 py-1.5 rounded-full text-sm font-bold {{ $semester->semester == 'Genap' ? 'bg-blue-100 text-blue-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    Semester {{ ucfirst($semester->semester) }}
                                </span>
                                <span class="px-4 py-1.5 rounded-full text-sm font-bold bg-amber-100 text-amber-700">
                                    <i class="fas fa-lock mr-1"></i> Terarsip
                                </span>
                                <span class="px-4 py-1.5 rounded-full text-sm font-bold bg-gray-100 text-gray-700">
                                    {{ $semester->jenjang ?? 'Semua' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right text-sm text-gray-600">
                        <div><i class="fas fa-clock mr-1"></i> Diarsipkan:</div>
                        <div class="font-semibold">{{ $semester->updated_at->format('d M Y, H:i') }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-amber-50 border-l-4 border-amber-400 p-4 rounded-lg mb-6">
            <div class="flex">
                <i class="fas fa-lock text-amber-600 mt-0.5"></i>
                <div class="ml-3">
                    <p class="text-sm text-amber-800">
                        <strong>Data Read-Only:</strong> Semester ini telah diarsipkan. Anda hanya dapat melihat data, tidak dapat melakukan perubahan.
                    </p>
                </div>
            </div>
        </div>

        {{-- Classes List --}}
        <h3 class="text-2xl font-bold text-gray-800 mb-4">Kelas & Mata Pelajaran</h3>
        
        @if($guruMapels->isEmpty())
            <div class="bg-white rounded-2xl shadow-sm p-16 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-inbox text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-700 mb-2">Tidak Ada Data</h3>
                <p class="text-gray-500">Tidak ditemukan data kelas untuk semester ini.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($guruMapels as $gm)
                    <a href="{{ route('akademik.guru.arsip.detail', $gm->id) }}" 
                       class="group bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-amber-400 overflow-hidden">
                        
                        {{-- Card Header --}}
                        <div class="bg-gradient-to-br from-blue-600 to-blue-500 p-6 text-white">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-2xl font-bold">Kelas {{ $gm->kelas->level }}</h4>
                                    <p class="text-blue-100 text-sm mt-1">{{ $gm->kelas->nama_unik ?? '-' }}</p>
                                </div>
                                <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-xl flex items-center justify-center">
                                    <i class="fas fa-school text-2xl"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="p-6">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="fas fa-book-open text-blue-600"></i>
                                </div>
                                <div class="flex-1">
                                    <div class="text-xs text-gray-500">Mata Pelajaran</div>
                                    <div class="font-semibold text-gray-800">{{ $gm->mapel->nama_mapel }}</div>
                                </div>
                            </div>

                            <div class="border-t border-gray-100 pt-4 mt-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Wali Kelas:</span>
                                    <span class="font-medium text-gray-800">{{ $gm->kelas->waliKelas->nama ?? 'Tidak ada' }}</span>
                                </div>
                            </div>
                        </div>

                        {{-- Card Footer --}}
                        <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-gray-100 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-gray-600">
                                    <i class="fas fa-lock mr-1"></i> Read-only
                                </span>
                                <div class="flex items-center gap-2 text-blue-600 font-semibold text-sm group-hover:text-blue-700">
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
