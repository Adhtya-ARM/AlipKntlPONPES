@extends('layouts.app')

@section('title', 'Dashboard Guru')

@section('content')
<div class="space-y-6">
    
    {{-- Welcome Section --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ $guruProfile->nama ?? 'Guru' }}</h1>
                <p class="text-gray-500 mt-1">Semoga hari Anda menyenangkan dan produktif.</p>
            </div>
            <div class="hidden md:block">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    {{ $activeRole == 'both' ? 'MTS & MA' : strtoupper($activeRole ?? 'Guru') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Card 1: Total Santri --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-purple-100 rounded-full text-purple-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Siswa Diajar</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalSantri }}</p>
            </div>
        </div>

        {{-- Card 2: Total Mapel --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Mata Pelajaran</p>
                <p class="text-2xl font-bold text-gray-800">{{ $mapels->count() }}</p>
            </div>
        </div>

        {{-- Card 3: Total Penilaian --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-green-100 rounded-full text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Penilaian Masuk</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalPenilaian }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column: Kalender Akademik --}}
        <div class="space-y-6">
            
            {{-- Kalender Akademik --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Kalender Akademik</h2>
                
                @php
                    $now = \Carbon\Carbon::now();
                    $currentMonth = $now->format('Y-m');
                    $monthStart = \Carbon\Carbon::parse($currentMonth . '-01');
                    $monthName = $monthStart->translatedFormat('F Y');
                    $daysInMonth = $monthStart->daysInMonth;
                    $firstDayOfWeek = $monthStart->dayOfWeek;
                @endphp
                
                {{-- Month Header --}}
                <div class="text-center mb-3">
                    <h3 class="text-sm font-semibold text-gray-700">{{ $monthName }}</h3>
                </div>
                
                {{-- Calendar Grid --}}
                <div class="grid grid-cols-7 gap-1">
                    {{-- Day Headers --}}
                    @foreach(['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'] as $day)
                        <div class="text-center text-xs font-medium text-gray-500 py-1">
                            {{ $day }}
                        </div>
                    @endforeach
                    
                    {{-- Empty cells before first day --}}
                    @for($i = 0; $i < $firstDayOfWeek; $i++)
                        <div class="aspect-square"></div>
                    @endfor
                    
                    {{-- Calendar days --}}
                    @for($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $date = $monthStart->copy()->day($day);
                            $dateKey = $date->format('Y-m-d');
                            $isToday = $date->isToday();
                            $events = $kalenderEvents[$dateKey] ?? [];
                            $hasEvent = count($events) > 0;
                            
                            $eventColor = 'bg-gray-100';
                            if ($hasEvent) {
                                $firstEvent = $events[0];
                                switch($firstEvent['jenis']) {
                                    case 'pembelajaran':
                                        $eventColor = 'bg-blue-100 text-blue-700';
                                        break;
                                    case 'ujian':
                                        $eventColor = 'bg-red-100 text-red-700';
                                        break;
                                    case 'libur':
                                        $eventColor = 'bg-orange-100 text-orange-700';
                                        break;
                                    case 'kegiatan':
                                        $eventColor = 'bg-green-100 text-green-700';
                                        break;
                                    default:
                                        $eventColor = 'bg-purple-100 text-purple-700';
                                }
                            }
                        @endphp
                        
                        <div class="aspect-square relative group">
                            <div class="w-full h-full flex items-center justify-center text-xs rounded-md transition-all
                                {{ $isToday ? 'bg-blue-600 text-white font-bold shadow-md' : ($hasEvent ? $eventColor . ' font-semibold' : 'text-gray-600 hover:bg-gray-50') }}
                                {{ $hasEvent && !$isToday ? 'cursor-pointer' : '' }}">
                                {{ $day }}
                            </div>
                            
                            {{-- Tooltip for events --}}
                            @if($hasEvent)
                                <div class="absolute hidden group-hover:block z-50 bg-gray-900 text-white text-xs rounded-lg p-2 shadow-xl 
                                    w-48 left-1/2 transform -translate-x-1/2 top-full mt-1 pointer-events-none">
                                    <div class="space-y-1">
                                        @foreach($events as $event)
                                            <div class="border-b border-gray-700 pb-1 mb-1 last:border-0 last:pb-0 last:mb-0">
                                                <div class="font-semibold">{{ ucfirst($event['jenis']) }}</div>
                                                @if($event['judul'])
                                                    <div class="text-gray-300">{{ $event['judul'] }}</div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="absolute -top-1 left-1/2 transform -translate-x-1/2 w-2 h-2 bg-gray-900 rotate-45"></div>
                                </div>
                            @endif
                        </div>
                    @endfor
                </div>
                
                {{-- Legend --}}
                <div class="mt-4 pt-3 border-t border-gray-100">
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded bg-blue-100 mr-1.5"></div>
                            <span class="text-gray-600">Pembelajaran</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded bg-red-100 mr-1.5"></div>
                            <span class="text-gray-600">Ujian</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded bg-orange-100 mr-1.5"></div>
                            <span class="text-gray-600">Libur</span>
                        </div>
                        <div class="flex items-center">
                            <div class="w-3 h-3 rounded bg-green-100 mr-1.5"></div>
                            <span class="text-gray-600">Kegiatan</span>
                        </div>
                    </div>
                    
                    <a href="{{ route('akademik.rencana-pembelajaran.index') }}" 
                       class="mt-3 block text-center text-xs text-blue-600 hover:text-blue-800 font-medium">
                        Lihat Kalender Lengkap &rarr;
                    </a>
                </div>
            </div>

            {{-- Information / Tips --}}
            <div class="bg-blue-50 p-5 rounded-xl border border-blue-100">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-blue-800">Tips Penggunaan</h3>
                        <p class="text-sm text-blue-600 mt-1">
                            Gunakan tombol "Absen" atau "Nilai" pada tabel di sebelah kanan untuk akses cepat ke input data per kelas.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Quick Actions & Daftar Mapel --}}
        <div class="lg:col-span-2 space-y-6">
            
                @php
                    $jabatan = strtolower($guruProfile->jabatan ?? '');
                    $isWakaOrKepsek = in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka']);
                @endphp

                <h2 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    {{-- Input Absensi (All Gurus) --}}
                    <a href="{{ route('akademik.absensi.index') }}" class="block px-4 py-3 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-lg transition duration-150 ease-in-out group border border-blue-200">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-gray-700 text-sm">Input Absensi</span>
                        </div>
                    </a>
                    
                    {{-- Rekap Kehadiran (All Gurus) --}}
                    <a href="{{ route('akademik.rekap-kehadiran.index') }}" class="block px-4 py-3 bg-gradient-to-br from-indigo-50 to-indigo-100 hover:from-indigo-100 hover:to-indigo-200 rounded-lg transition duration-150 ease-in-out group border border-indigo-200">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-indigo-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-gray-700 text-sm">Rekap Kehadiran</span>
                        </div>
                    </a>

                    {{-- Rekap Penilaian (All Gurus) --}}
                    <a href="{{ route('akademik.rekap-penilaian.index') }}" class="block px-4 py-3 bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-lg transition duration-150 ease-in-out group border border-green-200">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-green-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-gray-700 text-sm">Rekap Penilaian</span>
                        </div>
                    </a>

                    @if($isWakaOrKepsek)
                        {{-- Data Santri (Waka/Kepsek Only) --}}
                        <a href="{{ route('santri.index') }}" class="block px-4 py-3 bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-lg transition duration-150 ease-in-out group border border-purple-200">
                            <div class="flex items-center justify-center mb-2">
                                <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-purple-600 shadow-sm group-hover:scale-110 transition-transform">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                </div>
                            </div>
                            <div class="text-center">
                                <span class="font-semibold text-gray-700 text-sm">Data Santri</span>
                            </div>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Daftar Mapel & Kelas --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800">Jadwal & Kelas Anda</h2>
                    <a href="{{ route('akademik.guru-mapel.index') }}" class="text-sm text-blue-600 hover:text-blue-800 font-medium">Kelola Mapel &rarr;</a>
                </div>
                
                @if($guruMapels->isEmpty())
                    <div class="p-8 text-center">
                        <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                        <p class="text-gray-500">Belum ada mata pelajaran yang diampu.</p>
                        <a href="{{ route('akademik.guru-mapel.index') }}" class="mt-2 inline-block text-blue-600 hover:underline">Tambah Mapel Ajar</a>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                                    <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($guruMapels as $gm)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600">
                                                <span class="font-bold text-lg">{{ substr($gm->mapel->nama_mapel ?? '?', 0, 1) }}</span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">{{ $gm->mapel->nama_mapel ?? '-' }}</div>
                                                <div class="text-xs text-gray-500">{{ $gm->mapel->kode_mapel ?? '' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            {{ $gm->kelas->nama_kelas ?? ($gm->kelas->nama_unik ?? 'Kelas ?') }}
                                        </span>
                                        <div class="text-xs text-gray-400 mt-1">{{ $gm->kelas->level ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center space-x-2">
                                            <a href="{{ route('akademik.absensi.getSantri', $gm->id) }}" class="text-white bg-blue-600 hover:bg-blue-700 px-3 py-1.5 rounded-md text-xs transition duration-150 ease-in-out flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                                Absen
                                            </a>
                                            <a href="{{ route('akademik.penilaian.getSantri', $gm->id) }}" class="text-white bg-green-600 hover:bg-green-700 px-3 py-1.5 rounded-md text-xs transition duration-150 ease-in-out flex items-center">
                                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                Nilai
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection