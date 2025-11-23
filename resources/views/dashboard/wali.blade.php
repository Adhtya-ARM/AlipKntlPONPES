@extends('layouts.app')

@section('title', 'Dashboard Wali')

@section('content')
<div class="space-y-6">
    
    {{-- Welcome Section --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ $waliProfile->nama ?? 'Wali' }}</h1>
                <p class="text-gray-500 mt-1">Pantau perkembangan putra-putri Anda di sini.</p>
            </div>
            <div class="hidden md:block">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                    {{ $totalAnak }} Santri
                </span>
            </div>
        </div>
    </div>

    {{-- Stats Cards - Absensi Gabungan Bulan Ini --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        {{-- Card 1: Hadir --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-green-100 rounded-full text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Hadir</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalAbsensiHadir }}</p>
            </div>
        </div>

        {{-- Card 2: Sakit --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-yellow-100 rounded-full text-yellow-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Sakit</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalAbsensiSakit }}</p>
            </div>
        </div>

        {{-- Card 3: Izin --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-blue-100 rounded-full text-blue-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Izin</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalAbsensiIzin }}</p>
            </div>
        </div>

        {{-- Card 4: Alpa --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-red-100 rounded-full text-red-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Total Alpa</p>
                <p class="text-2xl font-bold text-gray-800">{{ $totalAbsensiAlpa }}</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- Left Column: Kalender & Info --}}
        <div class="space-y-6">
            
            {{-- Kalender Akademik Kecil --}}
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
                </div>
            </div>

            {{-- Total Nilai Card --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Penilaian</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalPenilaian }}</p>
                    </div>
                </div>
                <p class="text-xs text-gray-500">Gabungan semua putra-putri Anda</p>
            </div>

            {{-- Info Card --}}
            <div class="bg-gradient-to-br from-purple-50 to-pink-50 p-5 rounded-xl border border-purple-100">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-purple-800">Informasi untuk Wali</h3>
                        <p class="text-sm text-purple-600 mt-1">
                            Pantau perkembangan akademik putra-putri Anda secara berkala. Jika ada pertanyaan, hubungi wali kelas.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Aksi Cepat & Daftar Anak --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Aksi Cepat --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <a href="{{ route('wali.kehadiran') }}" class="block px-4 py-3 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-lg transition duration-150 ease-in-out group border border-blue-200">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-gray-700 text-sm">Rekap Kehadiran</span>
                        </div>
                    </a>
                    
                    <a href="{{ route('wali.mapel') }}" class="block px-4 py-3 bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-lg transition duration-150 ease-in-out group border border-green-200">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-green-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253"></path></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-gray-700 text-sm">Mata Pelajaran</span>
                        </div>
                    </a>

                    <a href="{{ route('wali.nilai') }}" class="block px-4 py-3 bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-lg transition duration-150 ease-in-out group border border-purple-200">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-purple-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-gray-700 text-sm">Nilai Anak</span>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Daftar Santri (Anak) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800">Putra-Putri Saya</h2>
                    <span class="text-sm text-gray-500">{{ $totalAnak }} Santri</span>
                </div>
                
                @if($santriAnak->isEmpty())
                    <div class="p-8 text-center">
                        <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <p class="text-gray-500">Belum ada data santri terdaftar.</p>
                    </div>
                @else
                    @foreach($santriAnak as $santri)
                        @php
                            $kelas = $santri->santriKelas->kelas ?? null;
                            $bulanIni = now()->format('Y-m');
                            
                            // Hitung absensi per anak
                            $hadir = $santri->absensis()->where('status', 'hadir')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])->count();
                            $sakit = $santri->absensis()->where('status', 'sakit')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])->count();
                            $izin = $santri->absensis()->where('status', 'izin')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])->count();
                            $alpa = $santri->absensis()->where('status', 'alpa')->whereRaw('DATE_FORMAT(tanggal, "%Y-%m") = ?', [$bulanIni])->count();
                            
                            // Hitung nilai rata-rata
                            $avgNilai = $santri->penilaians()->avg('nilai') ?? 0;
                        @endphp
                        
                        <div class="p-5 border-b border-gray-100 last:border-0">
                            {{-- Header Info Santri --}}
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center space-x-4">
                                    <div class="w-12 h-12 bg-gradient-to-br from-purple-400 to-indigo-500 rounded-full flex items-center justify-center text-white font-bold text-lg shadow-md">
                                        {{ substr($santri->nama, 0, 1) }}
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-gray-900">{{ $santri->nama }}</h3>
                                        <div class="flex items-center gap-2 mt-1">
                                            @if($kelas)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                    Kelas {{ $kelas->level }} {{ $kelas->nama_unik ?? '' }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                    Belum Terdaftar
                                                </span>
                                            @endif
                                            <span class="text-xs text-gray-500">
                                                NIS: {{ $santri->santri->nis ?? '-' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm text-gray-500">Rata-rata Nilai</div>
                                    <div class="text-2xl font-bold {{ $avgNilai >= 75 ? 'text-green-600' : 'text-orange-600' }}">
                                        {{ number_format($avgNilai, 1) }}
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Absensi Stats per Anak --}}
                            <div class="grid grid-cols-4 gap-3">
                                <div class="bg-green-50 rounded-lg p-3 text-center">
                                    <div class="text-xs text-gray-600 mb-1">Hadir</div>
                                    <div class="text-lg font-bold text-green-700">{{ $hadir }}</div>
                                </div>
                                <div class="bg-yellow-50 rounded-lg p-3 text-center">
                                    <div class="text-xs text-gray-600 mb-1">Sakit</div>
                                    <div class="text-lg font-bold text-yellow-700">{{ $sakit }}</div>
                                </div>
                                <div class="bg-blue-50 rounded-lg p-3 text-center">
                                    <div class="text-xs text-gray-600 mb-1">Izin</div>
                                    <div class="text-lg font-bold text-blue-700">{{ $izin }}</div>
                                </div>
                                <div class="bg-red-50 rounded-lg p-3 text-center">
                                    <div class="text-xs text-gray-600 mb-1">Alpa</div>
                                    <div class="text-lg font-bold text-red-700">{{ $alpa }}</div>
                                </div>
                            </div>
                            
                            {{-- Mapel (5 teratas) --}}
                            @if($kelas)
                                @php
                                    $mapelsAnak = \App\Models\Akademik\GuruMapel::where('kelas_id', $kelas->id)
                                        ->with('mapel')
                                        ->limit(5)
                                        ->get();
                                @endphp
                                
                                @if($mapelsAnak->count() > 0)
                                    <div class="mt-4 pt-4 border-t border-gray-100">
                                        <h4 class="text-xs font-semibold text-gray-600 mb-2">Mata Pelajaran:</h4>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($mapelsAnak as $gm)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-gray-100 text-gray-700">
                                                    {{ $gm->mapel->nama_mapel ?? '-' }}
                                                </span>
                                            @endforeach
                                            @if(\App\Models\Akademik\GuruMapel::where('kelas_id', $kelas->id)->count() > 5)
                                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs bg-gray-200 text-gray-600 font-medium">
                                                    +{{ \App\Models\Akademik\GuruMapel::where('kelas_id', $kelas->id)->count() - 5 }} lainnya
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
