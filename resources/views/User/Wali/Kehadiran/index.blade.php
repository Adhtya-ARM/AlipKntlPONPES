@extends('layouts.app')

@section('title', 'Rekap Kehadiran Anak')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Rekap Kehadiran Anak</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau kehadiran putra-putri Anda per bulan</p>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('wali.kehadiran') }}" class="flex flex-col md:flex-row gap-4">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" class="w-full border-gray-300 rounded-md text-sm">
            </div>
            <div class="flex-1">
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
                    $absensi = $data['absensi'];
                @endphp

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    {{-- Header Santri --}}
                    <div class="p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                    {{ substr($santri->nama, 0, 1) }}
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-gray-900">{{ $santri->nama }}</h3>
                                    <p class="text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        {{-- Stats Cards --}}
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="bg-green-50 p-3 rounded-lg border border-green-100">
                                <div class="text-xs text-gray-500 mb-1">Hadir</div>
                                <div class="text-xl font-bold text-green-600">{{ $stats['hadir'] }}</div>
                            </div>
                            <div class="bg-blue-50 p-3 rounded-lg border border-blue-100">
                                <div class="text-xs text-gray-500 mb-1">Sakit</div>
                                <div class="text-xl font-bold text-blue-600">{{ $stats['sakit'] }}</div>
                            </div>
                            <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-100">
                                <div class="text-xs text-gray-500 mb-1">Izin</div>
                                <div class="text-xl font-bold text-yellow-600">{{ $stats['izin'] }}</div>
                            </div>
                            <div class="bg-red-50 p-3 rounded-lg border border-red-100">
                                <div class="text-xs text-gray-500 mb-1">Alpa</div>
                                <div class="text-xl font-bold text-red-600">{{ $stats['alpa'] }}</div>
                            </div>
                        </div>

                        {{-- Tabel Kehadiran --}}
                        @if($absensi->isEmpty())
                            <div class="text-center text-gray-500 py-4">
                                <p class="text-sm">Belum ada data kehadiran bulan ini</p>
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($absensi as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-900">
                                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}
                                            </td>
                                            <td class="px-6 py-3 whitespace-nowrap text-center">
                                                @php
                                                    $badgeColor = [
                                                        'hadir' => 'bg-green-100 text-green-800',
                                                        'sakit' => 'bg-blue-100 text-blue-800',
                                                        'izin' => 'bg-yellow-100 text-yellow-800',
                                                        'alpa' => 'bg-red-100 text-red-800',
                                                    ][$item->status] ?? 'bg-gray-100 text-gray-800';
                                                @endphp
                                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeColor }}">
                                                    {{ ucfirst($item->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-3 text-sm text-gray-600">
                                                {{ $item->keterangan ?? '-' }}
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
