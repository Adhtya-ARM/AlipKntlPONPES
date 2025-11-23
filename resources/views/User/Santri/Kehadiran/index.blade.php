@extends('layouts.app')

@section('title', 'Rekap Kehadiran Saya')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Rekap Kehadiran Saya</h2>
        <p class="text-sm text-gray-500 mt-1">Pantau kehadiran Anda per bulan</p>
    </div>

    {{-- Filter Bulan --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('santri.kehadiran') }}" class="flex items-center gap-4">
            <div class="flex-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Bulan</label>
                <input type="month" name="bulan" value="{{ $bulan }}" onchange="this.form.submit()" class="w-full border-gray-300 rounded-md text-sm">
            </div>
        </form>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white p-4 rounded-lg shadow-sm border border-green-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Hadir</p>
                    <p class="text-2xl font-bold text-green-600">{{ $stats['hadir'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-sm border border-blue-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-thermometer-half text-blue-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Sakit</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['sakit'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-sm border border-yellow-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-alt text-yellow-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Izin</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $stats['izin'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-sm border border-red-200">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-times text-red-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Alpa</p>
                    <p class="text-2xl font-bold text-red-600">{{ $stats['alpa'] }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Kehadiran --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Detail Kehadiran</h3>
            <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }}</p>
        </div>

        @if($absensi->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <div class="text-5xl mb-3">📚</div>
                <div class="text-lg font-medium">Belum ada data kehadiran bulan ini</div>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($absensi as $item)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d F Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $item->kelas->level ?? '-' }} {{ $item->kelas->nama_unik ?? '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
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
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $item->keterangan ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $absensi->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
