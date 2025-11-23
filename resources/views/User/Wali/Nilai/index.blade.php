@extends('layouts.app')

@section('title', 'Nilai Anak')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Nilai Anak</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau perkembangan nilai putra-putri Anda</p>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('wali.nilai') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Anak</label>
                <select name="santri_id" onchange="this.form.submit()" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="semua" {{ $santriId == 'semua' ? 'selected' : '' }}>Semua Anak</option>
                    @foreach($santriAnak as $santri)
                        <option value="{{ $santri->id }}" {{ $santriId == $santri->id ? 'selected' : '' }}>
                            {{ $santri->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Penilaian</label>
                <select name="jenis" onchange="this.form.submit()" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="semua" {{ $jenis == 'semua' ? 'selected' : '' }}>Semua Jenis</option>
                    <option value="harian" {{ $jenis == 'harian' ? 'selected' : '' }}>Harian</option>
                    <option value="uts" {{ $jenis == 'uts' ? 'selected' : '' }}>UTS</option>
                    <option value="uas" {{ $jenis == 'uas' ? 'selected' : '' }}>UAS</option>
                    <option value="tugas" {{ $jenis == 'tugas' ? 'selected' : '' }}>Tugas</option>
                    <option value="praktik" {{ $jenis == 'praktik' ? 'selected' : '' }}>Praktik</option>
                </select>
            </div>
        </form>
    </div>

    {{-- Data Per Anak --}}
    @if(empty($nilaiData))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center text-gray-500">
            <div class="text-5xl mb-3">👨‍👩‍👧</div>
            <div class="text-lg font-medium">Belum ada data santri terdaftar</div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($nilaiData as $data)
                @php
                    $santri = $data['santri'];
                    $nilai = $data['nilai'];
                    $avgNilai = $data['avgNilai'];
                    $totalNilai = $data['totalNilai'];
                    $kelas = $santri->santriKelas->kelas ?? null;
                @endphp

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Header Santri --}}
                    <div class="p-5 bg-gradient-to-r from-green-50 to-emerald-50 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                    {{ substr($santri->nama, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $santri->nama }}</h3>
                                    <p class="text-sm text-gray-600">
                                        @if($kelas)
                                            Kelas {{ $kelas->level }} {{ $kelas->nama_unik ?? '' }}
                                        @else
                                            Belum terdaftar di kelas
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-500">Rata-rata</p>
                                <p class="text-3xl font-bold {{ $avgNilai >= 75 ? 'text-green-600' : 'text-orange-600' }}">
                                    {{ number_format($avgNilai, 1) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="p-5 border-b border-gray-200 bg-gray-50">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-chart-line text-gray-400 text-xl"></i>
                                <div>
                                    <p class="text-xs text-gray-500">Total Penilaian</p>
                                    <p class="text-lg font-bold text-gray-800">{{ $totalNilai }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <i class="fas fa-award text-gray-400 text-xl"></i>
                                <div>
                                    <p class="text-xs text-gray-500">Status</p>
                                    <p class="text-lg font-bold {{ $avgNilai >= 75 ? 'text-green-600' : 'text-orange-600' }}">
                                        {{ $avgNilai >= 75 ? 'Tuntas' : 'Perlu Bimbingan' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        {{-- Progress Bar --}}
                        <div class="mt-3 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full {{ $avgNilai >= 75 ? 'bg-green-500' : 'bg-orange-500' }}" 
                                 style="width: {{ min($avgNilai, 100) }}%"></div>
                        </div>
                    </div>

                    {{-- Daftar Nilai (10 Terakhir) --}}
                    @if($nilai->isEmpty())
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-star text-4xl text-gray-300 mb-2"></i>
                            <p class="text-sm">Belum ada nilai yang masuk</p>
                        </div>
                    @else
                        <div class="p-5">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Nilai Terakhir</h4>
                            <div class="space-y-3">
                                @foreach($nilai as $item)
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2 mb-1">
                                                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-indigo-500 rounded flex items-center justify-center text-white text-xs font-bold">
                                                    {{ substr($item->guruMapel->mapel->nama_mapel ?? '?', 0, 1) }}
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $item->guruMapel->mapel->nama_mapel ?? '-' }}</p>
                                                    <p class="text-xs text-gray-500">{{ $item->guruMapel->guruProfile->nama ?? '-' }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-3 text-xs text-gray-600 ml-10">
                                                <span>
                                                    <i class="fas fa-calendar-alt mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                                                </span>
                                                <span class="px-2 py-0.5 bg-white rounded text-gray-700 font-medium">
                                                    {{ ucfirst($item->jenis_penilaian) }}
                                                </span>
                                            </div>
                                            @if($item->keterangan)
                                                <p class="text-xs text-gray-600 ml-10 mt-1">
                                                    <i class="fas fa-info-circle mr-1"></i>
                                                    {{ $item->keterangan }}
                                                </p>
                                            @endif
                                        </div>
                                        <div class="ml-4 text-center">
                                            <div class="text-3xl font-bold {{ $item->nilai >= 75 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $item->nilai }}
                                            </div>
                                            <div class="text-xs {{ $item->nilai >= 75 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $item->nilai >= 75 ? '✓ Tuntas' : '✗ Belum' }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Info Card --}}
    <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i class="fas fa-lightbulb text-green-600 text-lg"></i>
            </div>
            <div class="text-sm text-green-800">
                <p class="font-semibold mb-1">Tips untuk Orang Tua</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Nilai minimal tuntas (KKM) adalah <strong>75</strong></li>
                    <li>Pantau perkembangan nilai anak secara berkala</li>
                    <li>Jika ada nilai di bawah KKM, diskusikan dengan anak dan guru pengampu</li>
                    <li>Berikan motivasi dan dukungan untuk meningkatkan prestasi belajar</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
