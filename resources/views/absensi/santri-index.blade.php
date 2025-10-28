@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold">Rekap Absensi</h1>
    </div>

    <div class="bg-white shadow rounded overflow-hidden">
        @forelse($absensis as $mapelId => $mapelAbsensi)
            @php
                $mapel = $mapelAbsensi->first()->mapel;
                $totalAbsen = $mapelAbsensi->where('status', 'alpha')->count();
                $totalHadir = $mapelAbsensi->where('status', 'hadir')->count();
                $status = $totalAbsen >= 7 ? 'TIDAK LULUS' : 'LULUS';
                $statusColor = $totalAbsen >= 7 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800';
            @endphp

            <div class="border-b border-gray-200 p-4">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $mapel->nama_mapel }}</h3>
                        <p class="text-gray-600">{{ $mapel->kelas }}</p>
                        <p class="text-sm text-gray-500">Pengajar: {{ optional($mapelAbsensi->first()->guruProfile)->nama ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-3 py-1 rounded-full {{ $statusColor }}">
                            {{ $status }}
                        </span>
                        <p class="text-sm text-gray-600 mt-1">
                            Total Kehadiran: {{ $totalHadir }}/{{ $mapelAbsensi->count() }}
                        </p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pertemuan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Keterangan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($mapelAbsensi->sortBy('pertemuan_ke') as $absensi)
                                <tr>
                                    <td class="px-6 py-4">{{ $absensi->pertemuan_ke }}</td>
                                    <td class="px-6 py-4">
                                        @if($absensi->status === 'hadir')
                                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Hadir</span>
                                        @elseif($absensi->status === 'sakit')
                                            <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Sakit</span>
                                        @elseif($absensi->status === 'izin')
                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs">Izin</span>
                                        @elseif($absensi->status === 'alpha')
                                            <span class="px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">Alpha</span>
                                        @else
                                            <span class="px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">{{ $absensi->status }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">{{ $absensi->keterangan ?? '-' }}</td>
                                    <td class="px-6 py-4">{{ $absensi->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="p-4 text-center text-gray-500">
                Belum ada data absensi
            </div>
        @endforelse
    </div>
</div>
@endsection