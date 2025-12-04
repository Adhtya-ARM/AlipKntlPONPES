@extends('layouts.app')

@section('title', 'Detail Arsip Kelas')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('akademik.guru.arsip.show', $guruMapel->tahun_ajaran_id) }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
                <i class="fas fa-arrow-left mr-2"></i> Kembali ke Semester
            </a>
            
            <div class="bg-white rounded-2xl shadow-lg border-2 border-amber-200 p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <div class="flex items-center gap-4 mb-3">
                            <div class="w-16 h-16 bg-gradient-to-br from-blue-600 to-blue-500 rounded-2xl flex items-center justify-center shadow-lg text-white">
                                <span class="font-bold text-2xl">{{ $guruMapel->kelas->level }}</span>
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold text-gray-800">{{ $guruMapel->mapel->nama_mapel }}</h2>
                                <p class="text-gray-600">Kelas {{ $guruMapel->kelas->level }} - {{ $guruMapel->kelas->nama_unik ?? '' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                <i class="fas fa-lock mr-1"></i> Read-Only
                            </span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                {{ $guruMapel->tahunAjaran->nama }} - {{ ucfirst($guruMapel->tahunAjaran->semester) }}
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-600">Total Santri</div>
                        <div class="text-3xl font-bold text-blue-600">{{ $santris->count() }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ activeTab: 'santri' }" class="bg-white rounded-2xl shadow-lg overflow-hidden">
            {{-- Tab Headers --}}
            <div class="border-b border-gray-200 bg-gray-50">
                <nav class="flex -mb-px">
                    <button @click="activeTab = 'santri'" 
                            class="flex-1 py-4 px-6 text-sm font-medium transition-all"
                            :class="activeTab === 'santri' ? 'border-b-2 border-blue-500 text-blue-600 bg-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'">
                        <i class="fas fa-users mr-2"></i> Daftar Santri ({{ $santris->count() }})
                    </button>
                    <button @click="activeTab = 'absensi'" 
                            class="flex-1 py-4 px-6 text-sm font-medium transition-all"
                            :class="activeTab === 'absensi' ? 'border-b-2 border-green-500 text-green-600 bg-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'">
                        <i class="fas fa-clipboard-check mr-2"></i> Riwayat Kehadiran ({{ $absensiByDate->count() }} pertemuan)
                    </button>
                    <button @click="activeTab = 'penilaian'" 
                            class="flex-1 py-4 px-6 text-sm font-medium transition-all"
                            :class="activeTab === 'penilaian' ? 'border-b-2 border-yellow-500 text-yellow-600 bg-white' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'">
                        <i class="fas fa-star mr-2"></i> Data Penilaian  
                    </button>
                </nav>
            </div>

            {{-- Tab Content --}}
            <div class="p-6">
                {{-- Santri Tab --}}
                <div x-show="activeTab === 'santri'" x-transition>
                    @if($santris->isEmpty())
                        <div class="text-center py-12">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">Tidak ada data santri</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NISN</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($santris as $index => $santri)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $santri->santri->nisn ?? '-' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $santri->nama }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @if($santri->foto)
                                                    <img src="{{ asset('storage/' . $santri->foto) }}" alt="{{ $santri->nama }}" class="w-10 h-10 rounded-full object-cover">
                                                @else
                                                    <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                                                        {{ strtoupper(substr($santri->nama, 0, 2)) }}
                                                    </div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                {{-- Absensi Tab --}}
                <div x-show="activeTab === 'absensi'" x-transition>
                    @if($absensiByDate->isEmpty())
                        <div class="text-center py-12">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">Tidak ada data kehadiran</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($absensiByDate as $tanggal => $absensiList)
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4 border-b border-green-200">
                                        <div class="flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-green-500 rounded-lg flex items-center justify-center text-white">
                                                    <i class="fas fa-calendar-day"></i>
                                                </div>
                                                <div>
                                                    <h4 class="font-bold text-gray-800">{{ \Carbon\Carbon::parse($tanggal)->format('d F Y') }}</h4>
                                                    <p class="text-xs text-gray-600">{{ $absensiList->count() }} santri tercatat</p>
                                                </div>
                                            </div>
                                            <div class="text-sm">
                                                <span class="px-3 py-1 bg-green-500 text-white rounded-full font-bold">
                                                    {{ $absensiList->where('status', 'H')->count() }} Hadir
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                            @foreach($absensiList as $absensi)
                                                <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 {{ $absensi->status === 'H' ? 'bg-green-50' : ($absensi->status === 'I' ? 'bg-blue-50' : ($absensi->status === 'S' ? 'bg-yellow-50' : 'bg-red-50')) }}">
                                                    <div class="w-10 h-10 rounded-full {{ $absensi->status === 'H' ? 'bg-green-500' : ($absensi->status === 'I' ? 'bg-blue-500' : ($absensi->status === 'S' ? 'bg-yellow-500' : 'bg-red-500')) }} flex items-center justify-center text-white font-bold text-sm">
                                                        {{ $absensi->status }}
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-semibold text-sm text-gray-800 truncate">{{ $absensi->santriProfile->nama ?? '-' }}</p>
                                                        <p class="text-xs text-gray-600">{{ $absensi->santriProfile->santri->nisn ?? '-' }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Penilaian Tab --}}
                <div x-show="activeTab === 'penilaian'" x-transition>
                    @if($penilaianByType->isEmpty())
                        <div class="text-center py-12">
                            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
                            <p class="text-gray-500">Tidak ada data penilaian</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($penilaianByType as $jenis => $penilaianList)
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <div class="bg-gradient-to-r from-yellow-50 to-yellow-100 px-6 py-4 border-b border-yellow-200">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center text-white">
                                                <i class="fas fa-star"></i>
                                            </div>
                                            <div>
                                                <h4 class="font-bold text-gray-800">{{ $jenis }}</h4>
                                                <p class="text-xs text-gray-600">{{ $penilaianList->count() }} data nilai</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">NISN</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Nilai</th>
                                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach($penilaianList as $index => $nilai)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $nilai->santriProfile->santri->nisn ?? '-' }}</td>
                                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $nilai->santriProfile->nama ?? '-' }}</td>
                                                            <td class="px-4 py-3 text-sm text-gray-600">{{ \Carbon\Carbon::parse($nilai->tanggal)->format('d M Y') }}</td>
                                                            <td class="px-4 py-3 text-center">
                                                                <span class="px-3 py-1 rounded-full text-sm font-bold {{ $nilai->nilai >= 75 ? 'bg-green-100 text-green-700' : ($nilai->nilai >= 60 ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                                                                    {{ $nilai->nilai }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $nilai->keterangan ?? '-' }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
