@extends('layouts.app')

@section('title', 'Mata Pelajaran Saya')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Mata Pelajaran Saya</h2>
        <p class="text-sm text-gray-500 mt-1">Daftar mata pelajaran yang Anda ikuti</p>
    </div>

    {{-- Info Kelas --}}
    @if($kelas)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-door-open text-blue-600 text-xl"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Kelas Anda</p>
                    <p class="text-lg font-bold text-gray-800">Kelas {{ $kelas->level }} {{ $kelas->nama_unik ?? '' }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Daftar Mata Pelajaran --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="p-5 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-800">Daftar Mata Pelajaran</h3>
            <p class="text-sm text-gray-500">Total: {{ $mapels->count() }} mata pelajaran</p>
        </div>

        @if($mapels->isEmpty())
            <div class="p-12 text-center text-gray-500">
                <div class="text-5xl mb-3">📚</div>
                <div class="text-lg font-medium">Belum ada mata pelajaran terdaftar</div>
                <p class="text-sm mt-2">Hubungi wali kelas atau admin jika ini adalah kesalahan</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guru Pengampu</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">JJM</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Semester</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($mapels as $index => $gm)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                {{ $index + 1 }}
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-400 to-purple-500 rounded-lg flex items-center justify-center text-white font-bold shadow-md">
                                        {{ substr($gm->mapel->nama_mapel ?? '?', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $gm->mapel->nama_mapel ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $gm->mapel->kode_mapel ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $gm->guruProfile->nama ?? '-' }}</div>
                                <div class="text-xs text-gray-500">Guru Pengampu</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                    {{ $gm->mapel->jjm ?? 0 }} JP
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ ucfirst($gm->semester ?? 'Ganjil') }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Info Card --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 text-lg"></i>
            </div>
            <div class="text-sm text-blue-700">
                <p class="font-semibold mb-1">Informasi</p>
                <p>Mata pelajaran di atas adalah daftar mapel yang Anda ikuti di kelas saat ini. Jika ada perbedaan, segera hubungi wali kelas atau admin.</p>
            </div>
        </div>
    </div>
</div>
@endsection
