@extends('layouts.app')

@section('title', 'Dashboard Santri')

@section('content')
<div class="space-y-6">
    
    {{-- Welcome Section --}}
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, {{ $santriProfile->nama ?? 'Santri' }}</h1>
                <p class="text-gray-500 mt-1">Semoga hari Anda menyenangkan dan semangat belajar!</p>
            </div>
            <div class="hidden md:block">
                @if($kelas)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Kelas {{ $kelas->level }} {{ $kelas->nama_unik ?? '' }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-600">
                        Belum Terdaftar
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- Stats Cards - Absensi Bulan Ini --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        {{-- Card 1: Hadir --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 flex items-center space-x-4">
            <div class="p-3 bg-green-100 rounded-full text-green-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <p class="text-gray-500 text-sm font-medium">Hadir</p>
                <p class="text-2xl font-bold text-gray-800">{{ $absensiStats['hadir'] }}</p>
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
                <p class="text-gray-500 text-sm font-medium">Sakit</p>
                <p class="text-2xl font-bold text-gray-800">{{ $absensiStats['sakit'] }}</p>
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
                <p class="text-gray-500 text-sm font-medium">Izin</p>
                <p class="text-2xl font-bold text-gray-800">{{ $absensiStats['izin'] }}</p>
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
                <p class="text-gray-500 text-sm font-medium">Alpa</p>
                <p class="text-2xl font-bold text-gray-800">{{ $absensiStats['alpa'] }}</p>
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

            {{-- Info Card --}}
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-5 rounded-xl border border-blue-100">
                <div class="flex items-start space-x-3">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-blue-800">Informasi</h3>
                        <p class="text-sm text-blue-600 mt-1">
                            Pantau terus absensi dan nilai Anda. Jika ada kendala, segera hubungi wali kelas atau guru pengampu.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        {{-- Right Column: Aksi Cepat, Daftar Mapel & Nilai Terbaru --}}
        <div class="lg:col-span-2 space-y-6">
            
            {{-- Jadwal Pelajaran Hari Ini --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
                <div class="p-5 bg-gradient-to-r from-green-600 to-green-500 text-white">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <i class="fas fa-calendar-day"></i>
                        Jadwal Pelajaran Hari Ini - {{ $hariIni }}
                    </h2>
                    <p class="text-green-100 text-sm mt-1">{{ now()->translatedFormat('d F Y') }}</p>
                </div>
                
                @if($jadwalHariIni->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <div class="text-5xl mb-3">📚</div>
                        <p class="text-sm">Tidak ada jadwal pelajaran hari ini</p>
                        <p class="text-xs text-gray-400 mt-1">Gunakan waktu untuk belajar mandiri!</p>
                    </div>
                @else
                    <div class="p-5 space-y-3 max-h-96 overflow-y-auto">
                        @foreach($jadwalHariIni as $jadwal)
                            <div class="bg-gradient-to-r from-green-50 to-white p-4 rounded-xl border-l-4 border-green-600 hover:shadow-md transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-14 h-14 bg-green-600 rounded-lg flex flex-col items-center justify-center text-white">
                                            <div class="text-xs font-semibold">Jam</div>
                                            <div class="text-xl font-bold">{{ $jadwal->jam_ke }}</div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-gray-800">{{ $jadwal->mapel->nama_mapel }}</div>
                                            <div class="text-sm text-gray-600">{{ $jadwal->guruProfile->nama ?? 'Guru' }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-sm font-semibold text-green-700">
                                            {{ date('H:i', strtotime($jadwal->jam_mulai)) }} - {{ date('H:i', strtotime($jadwal->jam_selesai)) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Aksi Cepat --}}
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Aksi Cepat</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <a href="{{ route('santri.kehadiran') }}" class="block px-4 py-3 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 rounded-lg transition duration-150 ease-in-out group border border-blue-200">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-blue-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-gray-700 text-sm">Rekap Kehadiran</span>
                        </div>
                    </a>
                    
                    <a href="{{ route('santri.mapel') }}" class="block px-4 py-3 bg-gradient-to-br from-green-50 to-green-100 hover:from-green-100 hover:to-green-200 rounded-lg transition duration-150 ease-in-out group border border-green-200">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-green-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-gray-700 text-sm">Mata Pelajaran</span>
                        </div>
                    </a>

                    <a href="{{ route('santri.nilai') }}" class="block px-4 py-3 bg-gradient-to-br from-purple-50 to-purple-100 hover:from-purple-100 hover:to-purple-200 rounded-lg transition duration-150 ease-in-out group border border-purple-200">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center text-purple-600 shadow-sm group-hover:scale-110 transition-transform">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                            </div>
                        </div>
                        <div class="text-center">
                            <span class="font-semibold text-gray-700 text-sm">Nilai Saya</span>
                        </div>
                    </a>
                </div>
            </div>

            {{-- Daftar Mata Pelajaran --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800">Mata Pelajaran Saya</h2>
                    <span class="text-sm text-gray-500">{{ $mapels->count() }} Mapel</span>
                </div>
                
                @if($mapels->isEmpty())
                    <div class="p-8 text-center">
                        <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                @foreach($mapels as $gm)
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
                                        <div class="text-sm text-gray-900">{{ $gm->guruProfile->nama ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
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

            {{-- Nilai Terbaru --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-800">Nilai Terbaru</h2>
                    <span class="text-sm text-blue-600 font-medium">Total: {{ $totalPenilaian }}</span>
                </div>
                
                @if($penilaianTerbaru->isEmpty())
                    <div class="p-8 text-center">
                        <div class="inline-block p-4 rounded-full bg-gray-50 mb-3">
                            <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <p class="text-gray-500">Belum ada penilaian yang masuk.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-100">
                        @foreach($penilaianTerbaru as $nilai)
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center justify-between">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-semibold text-gray-900">{{ $nilai->guruMapel->mapel->nama_mapel ?? '-' }}</h3>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ ucfirst($nilai->jenis_penilaian) }} • 
                                            {{ \Carbon\Carbon::parse($nilai->tanggal)->format('d M Y') }}
                                        </p>
                                        @if($nilai->keterangan)
                                            <p class="text-xs text-gray-600 mt-1">{{ $nilai->keterangan }}</p>
                                        @endif
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-2xl font-bold {{ $nilai->nilai >= 75 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $nilai->nilai }}
                                        </div>
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
@endsection
