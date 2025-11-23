@extends('layouts.app')

@section('title', 'Nilai Saya')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Nilai Saya</h2>
        <p class="text-sm text-gray-500 mt-1">Daftar nilai dan hasil penilaian Anda</p>
    </div>

    {{-- Filter & Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        {{-- Filter Jenis Penilaian --}}
        <div class="md:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <form method="GET" action="{{ route('santri.nilai') }}">
                <label class="block text-xs font-medium text-gray-500 mb-2">Filter Jenis Penilaian</label>
                <select name="jenis" onchange="this.form.submit()" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="semua" {{ $jenis == 'semua' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="harian" {{ $jenis == 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="uts" {{ $jenis == 'uts' ? 'selected' : '' }}>UTS</option>
                    <option value="uas" {{ $jenis == 'uas' ? 'selected' : '' }}>UAS</option>
                    <option value="tugas" {{ $jenis == 'tugas' ? 'selected' : '' }}>Tugas</option>
                    <option value="praktik" {{ $jenis == 'praktik' ? 'selected' : '' }}>Praktik</option>
                </select>
            </form>
        </div>

        {{-- Rata-rata Nilai --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Rata-rata Nilai</p>
            <p class="text-3xl font-bold {{ $avgNilai >= 75 ? 'text-green-600' : 'text-orange-600' }}">
                {{ number_format($avgNilai, 1) }}
            </p>
            <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full {{ $avgNilai >= 75 ? 'bg-green-500' : 'bg-orange-500' }}" 
                     style="width: {{ min($avgNilai, 100) }}%"></div>
            </div>
        </div>

        {{-- Total Penilaian --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <p class="text-xs text-gray-500 mb-1">Total Penilaian</p>
            <p class="text-3xl font-bold text-blue-600">{{ $totalNilai }}</p>
            <p class="text-xs text-gray-500 mt-2">Nilai yang masuk</p>
        </div>
    </div>

    {{-- Daftar Nilai --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Detail Nilai</h3>
            <p class="text-sm text-gray-500">
                @if($jenis == 'semua')
                    Menampilkan semua jenis penilaian
                @else
                    Menampilkan penilaian: {{ ucfirst($jenis) }}
                @endif
            </p>
        </div>

        @if($nilai->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <div class="text-5xl mb-3">📊</div>
                <div class="text-lg font-medium">Belum ada nilai yang masuk</div>
                <p class="text-sm mt-2">Nilai akan ditampilkan setelah guru menginput penilaian</p>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($nilai as $item)
                <div class="p-5 hover:bg-gray-50 transition">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            {{-- Mapel & Guru --}}
                            <div class="flex items-center gap-3 mb-2">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-lg flex items-center justify-center text-white font-bold shadow-md">
                                    {{ substr($item->guruMapel->mapel->nama_mapel ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <h4 class="text-sm font-semibold text-gray-900">{{ $item->guruMapel->mapel->nama_mapel ?? '-' }}</h4>
                                    <p class="text-xs text-gray-500">{{ $item->guruMapel->guruProfile->nama ?? '-' }}</p>
                                </div>
                            </div>

                            {{-- Detail Penilaian --}}
                            <div class="flex items-center gap-4 text-xs text-gray-600">
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-calendar-alt text-gray-400"></i>
                                    <span>{{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="fas fa-tag text-gray-400"></i>
                                    <span class="px-2 py-0.5 bg-gray-100 rounded text-gray-700 font-medium">
                                        {{ ucfirst($item->jenis_penilaian) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Keterangan --}}
                            @if($item->keterangan)
                                <p class="text-xs text-gray-600 mt-2">
                                    <i class="fas fa-info-circle text-gray-400 mr-1"></i>
                                    {{ $item->keterangan }}
                                </p>
                            @endif
                        </div>

                        {{-- Nilai --}}
                        <div class="ml-6 text-center">
                            <div class="text-4xl font-bold {{ $item->nilai >= 75 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $item->nilai }}
                            </div>
                            <div class="text-xs text-gray-500 mt-1">
                                {{ $item->nilai >= 75 ? 'Tuntas' : 'Belum Tuntas' }}
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $nilai->links() }}
            </div>
        @endif
    </div>

    {{-- Info KKM --}}
    <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-600 text-lg"></i>
            </div>
            <div class="text-sm text-yellow-800">
                <p class="font-semibold mb-1">Kriteria Ketuntasan Minimal (KKM)</p>
                <p>Nilai minimal untuk dinyatakan tuntas adalah <strong>75</strong>. Jika nilai Anda di bawah KKM, silakan belajar lebih giat atau konsultasi dengan guru pengampu.</p>
            </div>
        </div>
    </div>
</div>
@endsection
