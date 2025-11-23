@extends('layouts.app')

@section('title', 'Mata Pelajaran Anak')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Mata Pelajaran Anak</h2>
        <p class="text-sm text-gray-500 mt-1">Daftar mata pelajaran yang diikuti putra-putri Anda</p>
    </div>

    {{-- Filter --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <form method="GET" action="{{ route('wali.mapel') }}">
            <label class="block text-xs font-medium text-gray-500 mb-1">Pilih Anak</label>
            <select name="santri_id" onchange="this.form.submit()" class="w-full border-gray-300 rounded-md text-sm">
                <option value="semua" {{ $santriId == 'semua' ? 'selected' : '' }}>Semua Anak</option>
                @foreach($santriAnak as $santri)
                    <option value="{{ $santri->id }}" {{ $santriId == $santri->id ? 'selected' : '' }}>
                        {{ $santri->nama }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Data Per Anak --}}
    @if(empty($mapelData))
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center text-gray-500">
            <div class="text-5xl mb-3">👨‍👩‍👧</div>
            <div class="text-lg font-medium">Belum ada data santri terdaftar</div>
        </div>
    @else
        <div class="space-y-6">
            @foreach($mapelData as $data)
                @php
                    $santri = $data['santri'];
                    $kelas = $data['kelas'];
                    $mapels = $data['mapels'];
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
                                        @if($kelas)
                                            Kelas {{ $kelas->level }} {{ $kelas->nama_unik ?? '' }}
                                        @else
                                            Belum terdaftar di kelas
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold">
                                {{ $mapels->count() }} Mapel
                            </div>
                        </div>
                    </div>

                    {{-- Daftar Mapel --}}
                    @if($mapels->isEmpty())
                        <div class="p-8 text-center text-gray-500">
                            <i class="fas fa-book text-4xl text-gray-300 mb-2"></i>
                            <p class="text-sm">Belum ada mata pelajaran terdaftar</p>
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
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($mapels as $index => $gm)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-600">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 bg-gradient-to-br from-indigo-400 to-purple-500 rounded flex items-center justify-center text-white text-xs font-bold">
                                                    {{ substr($gm->mapel->nama_mapel ?? '?', 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $gm->mapel->nama_mapel ?? '-' }}</div>
                                                    <div class="text-xs text-gray-500">{{ $gm->mapel->kode_mapel ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-sm text-gray-700">
                                            {{ $gm->guruProfile->nama ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3 whitespace-nowrap text-center">
                                            <span class="px-2 py-1 text-xs font-semibold rounded bg-blue-100 text-blue-800">
                                                {{ $gm->mapel->jjm ?? 0 }} JP
                                            </span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    {{-- Info Card --}}
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 text-lg"></i>
            </div>
            <div class="text-sm text-blue-700">
                <p class="font-semibold mb-1">Informasi</p>
                <p>Mata pelajaran yang ditampilkan adalah daftar mapel yang diikuti oleh putra-putri Anda sesuai dengan kelas masing-masing. JJM adalah singkatan dari Jam Jam Mengajar per minggu.</p>
            </div>
        </div>
    </div>
</div>
@endsection
