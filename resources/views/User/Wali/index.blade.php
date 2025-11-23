@extends('layouts.app')

@section('title', 'Rekap Kehadiran Anak')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Rekap Kehadiran Anak</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau kehadiran putra-putri Anda per bulan</p>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('wali.kehadiran') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" class="w-full border-gray-300 rounded-md text-sm">
            </div>
        </form>
    </div>

    {{-- Data Per Anak --}}
    @if(empty($kehadiranData))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center text-gray-500">
            <div class="text-5xl mb-3">👨‍👩‍👧</div>
            <div class="text-lg font-medium">Belum ada data santri terdaftar</div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($kehadiranData as $data)
                @php
                    $santri = $data['santri'];
                    $stats = $data['stats'];
                    $absensiList = $data['absensi'];
                    $kelas = $santri->santriKelas->kelas ?? null;
                @endphp

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Header Santri --}}
                    <div class="p-5 bg-gradient-to-r from-purple-50 to-pink-50 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
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
                                <p class="text-xs text-gray-500">NISN</p>
                                <p class="text-sm font-semibold text-gray-800">{{ $santri->santri->nisn ?? '-' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Stats Cards --}}
                    <div class="p-5 border-b border-gray-200">
                        <div class="grid grid-cols-4 gap-4">
                            <div class="bg-green-50 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-600 mb-1">Hadir</p>
                                <p class="text-2xl font-bold text-green-600">{{ $stats['hadir'] }}</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-600 mb-1">Sakit</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $stats['sakit'] }}</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-600 mb-1">Izin</p>
                                <p class="text-2xl font-bold text-yellow-600">{{ $stats['izin'] }}</p>
                            </div>
                            <div class="bg-red-50 rounded-lg p-3 text-center">
                                <p class="text-xs text-gray-600 mb-1">Alpa</p>
                                <p class="text-2xl font-bold text-red-600">{{ $stats['alpa'] }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Detail Kehadiran (10 Terakhir) --}}
                    @if($absensiList->isEmpty())
                        <div class="p-8 text-center text-gray-500">
                            <p class="text-sm">Belum ada data kehadiran bulan ini</p>
                        </div>
                    @else
                        <div class="p-5">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Kehadiran Terakhir</h4>
                            <div class="space-y-2">
                                @foreach($absensiList as $item)
                                    <div class="flex items-center justify-between py-2 px-3 bg-gray-50 rounded-lg">
                                        <div class="flex items-center gap-3">
                                            <i class="fas fa-calendar-day text-gray-400 text-sm"></i>
                                            <span class="text-sm text-gray-700">
                                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}
                                            </span>
                                        </div>
                                        @php
                                            $badgeColor = [
                                                'hadir' => 'bg-green-100 text-green-800',
                                                'sakit' => 'bg-blue-100 text-blue-800',
                                                'izin' => 'bg-yellow-100 text-yellow-800',
                                                'alpa' => 'bg-red-100 text-red-800',
                                            ][$item->status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded {{ $badgeColor }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
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
    <div class="mt-6 bg-purple-50 border border-purple-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-purple-500 text-lg"></i>
            </div>
            <div class="text-sm text-purple-700">
                <p class="font-semibold mb-1">Informasi</p>
                <p>Data kehadiran menampilkan 10 kehadiran terakhir untuk setiap anak. Untuk melihat detail lengkap, Anda dapat memfilter berdasarkan bulan dan anak tertentu.</p>
            </div>
        </div>
    </div>
</div>
@endsection
