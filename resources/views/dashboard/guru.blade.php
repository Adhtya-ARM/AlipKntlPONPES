@extends('layouts.app')

@section('title', 'Dashboard Guru')

@push('styles')
<style>
/* custom kecil untuk kalender tooltip & hide scrollbar */
.calendar-tooltip {
  @apply absolute z-50 bg-gray-900 text-white text-xs rounded-lg p-2 shadow-xl w-48 left-1/2 transform -translate-x-1/2 top-full mt-1 transition-opacity duration-200 opacity-0 pointer-events-none;
}
.group:hover .calendar-tooltip { @apply opacity-100 pointer-events-auto; }

/* hide native scrollbar for specific scroll areas */
.hide-scrollbar::-webkit-scrollbar { display: none; }
.hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endpush

@section('content')
<div class="space-y-6">
    {{-- Welcome Section --}}
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Selamat Datang, {{ $guruProfile->nama ?? 'Guru' }}</h1>
                <p class="text-gray-500 mt-1">Semoga hari Anda menyenangkan dan produktif.</p>
            </div>
            <div class="hidden md:block">
                <span class="px-4 py-2 bg-blue-50 text-blue-600 rounded-lg text-sm font-semibold">
                    {{ now()->translatedFormat('l, d F Y') }}
                </span>
            </div>
        </div>
    </div>

    {{-- Stats & Quick Actions Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        {{-- LEFT: Stats Cards (2x2 Grid) --}}
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Total Siswa -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                    <i class="fas fa-user-graduate text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Siswa</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalSantri }}</p>
                </div>
            </div>

            <!-- Total Mapel -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                    <i class="fas fa-book text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Mapel Diampu</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $mapels->count() }}</p>
                </div>
            </div>

            <!-- Total Kelas -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                    <i class="fas fa-chalkboard text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Total Kelas</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalKelas ?? 0 }}</p>
                </div>
            </div>

            <!-- Total Penilaian -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition">
                <div class="w-14 h-14 flex items-center justify-center rounded-xl bg-green-50 text-green-600">
                    <i class="fas fa-star text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500 font-medium">Penilaian Masuk</p>
                    <p class="text-2xl font-bold text-gray-800">{{ $totalPenilaian }}</p>
                </div>
            </div>
        </div>

        {{-- RIGHT: Quick Actions --}}
        <div class="lg:col-span-1 bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-center h-full">
            <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-bolt text-yellow-500"></i> Aksi Cepat
            </h3>
            <div class="space-y-3">
                <a href="{{ route('akademik.absensi.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-blue-50 hover:text-blue-700 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm text-blue-600 group-hover:scale-110 transition">
                            <i class="fas fa-user-check text-sm"></i>
                        </div>
                        <span class="font-medium text-gray-700 group-hover:text-blue-700">Input Absensi</span>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-blue-500"></i>
                </a>

                <a href="{{ route('akademik.penilaian.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-green-50 hover:text-green-700 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm text-green-600 group-hover:scale-110 transition">
                            <i class="fas fa-edit text-sm"></i>
                        </div>
                        <span class="font-medium text-gray-700 group-hover:text-green-700">Input Nilai</span>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-green-500"></i>
                </a>

                <a href="{{ route('akademik.rekap-kehadiran.index') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 hover:bg-purple-50 hover:text-purple-700 transition group">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-sm text-purple-600 group-hover:scale-110 transition">
                            <i class="fas fa-chart-bar text-sm"></i>
                        </div>
                        <span class="font-medium text-gray-700 group-hover:text-purple-700">Rekap Kehadiran</span>
                    </div>
                    <i class="fas fa-chevron-right text-gray-400 text-xs group-hover:text-purple-500"></i>
                </a>
            </div>
        </div>
    </div>

    {{-- Kalender & Jadwal Hari Ini --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Kalender Akademik --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-calendar-alt text-blue-600"></i> Kalender Akademik
                </h2>
                <a href="{{ route('akademik.rencana-pembelajaran.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-800 bg-blue-50 px-3 py-1 rounded-full">Lihat Semua</a>
            </div>

            @php
                $now = \Carbon\Carbon::now();
                $currentMonth = $now->format('Y-m');
                $monthStart = \Carbon\Carbon::parse($currentMonth . '-01');
                $monthName = $monthStart->translatedFormat('F Y');
                $daysInMonth = $monthStart->daysInMonth;
                $firstDayOfWeek = $monthStart->dayOfWeek; // 0 (Sun) - 6 (Sat)
                // Adjust for Monday start if needed, but standard calendar usually starts Sunday
            @endphp

            <div class="text-center mb-4"><h3 class="text-sm font-bold text-gray-700 uppercase tracking-wide">{{ $monthName }}</h3></div>

            <div class="grid grid-cols-7 gap-2 mb-2">
                @foreach(['Min','Sen','Sel','Rab','Kam','Jum','Sab'] as $day)
                    <div class="text-center text-xs font-bold text-gray-400">{{ $day }}</div>
                @endforeach
            </div>

            <div class="grid grid-cols-7 gap-2">
                @for($i=0;$i<$firstDayOfWeek;$i++)
                    <div></div>
                @endfor

                @for($day=1;$day<=$daysInMonth;$day++)
                    @php
                        $date = $monthStart->copy()->day($day);
                        $dateKey = $date->format('Y-m-d');
                        $isToday = $date->isToday();
                        $events = $kalenderEvents[$dateKey] ?? [];
                        $hasEvent = count($events) > 0;
                        $eventColor = 'bg-gray-50 text-gray-700';
                        
                        if ($hasEvent) {
                            $firstEvent = $events[0];
                            switch($firstEvent['jenis']) {
                                case 'pembelajaran': $eventColor = 'bg-blue-100 text-blue-700 font-bold'; break;
                                case 'ujian': $eventColor = 'bg-red-100 text-red-700 font-bold'; break;
                                case 'libur': $eventColor = 'bg-orange-100 text-orange-700 font-bold'; break;
                                case 'kegiatan': $eventColor = 'bg-green-100 text-green-700 font-bold'; break;
                                default: $eventColor = 'bg-purple-100 text-purple-700 font-bold';
                            }
                        }
                        if ($isToday) {
                            $eventColor = 'bg-blue-600 text-white font-bold shadow-lg ring-2 ring-blue-200';
                        }
                    @endphp

                    <div class="aspect-square flex items-center justify-center relative group cursor-default">
                        <div class="w-8 h-8 flex items-center justify-center text-sm rounded-full transition-all {{ $eventColor }}">
                            {{ $day }}
                        </div>

                        @if($hasEvent)
                            <div class="calendar-tooltip">
                                <div class="space-y-1">
                                    @foreach($events as $event)
                                        <div class="border-b border-gray-700 pb-1 mb-1 last:border-0 last:pb-0 last:mb-0">
                                            <div class="font-semibold text-yellow-400">{{ ucfirst($event['jenis']) }}</div>
                                            @if(!empty($event['judul']))<div class="text-white">{{ $event['judul'] }}</div>@endif
                                            @if(!empty($event['jam']))<div class="text-gray-400 text-xs">{{ $event['jam'] }}</div>@endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endfor
            </div>

            <div class="mt-6 flex flex-wrap gap-3 justify-center text-xs">
                <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-blue-500"></div><span class="text-gray-600">KBM</span></div>
                <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-red-500"></div><span class="text-gray-600">Ujian</span></div>
                <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-orange-500"></div><span class="text-gray-600">Libur</span></div>
                <div class="flex items-center gap-1.5"><div class="w-2 h-2 rounded-full bg-green-500"></div><span class="text-gray-600">Kegiatan</span></div>
            </div>
        </div>

        {{-- Jadwal Mengajar Hari Ini --}}
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex flex-col h-full">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-clock text-blue-600"></i> Jadwal Hari Ini
                </h2>
                <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-3 py-1 rounded-full">{{ $hariIni }}</span>
            </div>

            @if($jadwalHariIni->isEmpty())
                <div class="flex-1 flex flex-col items-center justify-center text-center py-8">
                    <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-mug-hot text-3xl text-gray-300"></i>
                    </div>
                    <p class="text-gray-600 font-medium">Tidak ada jadwal mengajar hari ini</p>
                    <p class="text-sm text-gray-400 mt-1">Silakan cek jadwal untuk hari lain atau istirahat sejenak.</p>
                </div>
            @else
                <div class="flex-1 overflow-y-auto hide-scrollbar space-y-4 pr-1">
                    @foreach($jadwalHariIni as $jadwal)
                        <div class="group p-4 rounded-xl border border-gray-100 hover:border-blue-200 hover:shadow-md transition bg-white">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex flex-col items-center justify-center shadow-sm">
                                        <span class="text-[10px] font-medium opacity-80">Jam</span>
                                        <span class="text-lg font-bold leading-none">{{ $jadwal->jam_ke }}</span>
                                    </div>
                                    <div>
                                        <h4 class="font-bold text-gray-800">{{ $jadwal->mapel->nama_mapel }}</h4>
                                        <p class="text-sm text-gray-500">Kelas {{ $jadwal->kelas->level }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-2 py-1 bg-blue-50 text-blue-700 text-xs font-bold rounded-md">
                                        {{ date('H:i', strtotime($jadwal->jam_mulai)) }} - {{ date('H:i', strtotime($jadwal->jam_selesai)) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="flex gap-2 mt-3 pt-3 border-t border-gray-50">
                                <a href="{{ route('akademik.absensi.index') }}" class="flex-1 text-center py-1.5 rounded-lg bg-gray-50 text-gray-600 text-xs font-semibold hover:bg-blue-50 hover:text-blue-600 transition">
                                    <i class="fas fa-user-check mr-1"></i> Absen
                                </a>
                                <a href="{{ route('akademik.penilaian.index') }}" class="flex-1 text-center py-1.5 rounded-lg bg-gray-50 text-gray-600 text-xs font-semibold hover:bg-green-50 hover:text-green-600 transition">
                                    <i class="fas fa-star mr-1"></i> Nilai
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Daftar Mapel & Kelas --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-chalkboard-teacher text-blue-600"></i> Daftar Mapel & Kelas
            </h2>
            <a href="{{ route('akademik.guru-mapel.index') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-800">Kelola Mapel &rarr;</a>
        </div>

        @if($guruMapels->isEmpty())
            <div class="p-12 text-center">
                <div class="inline-block p-4 rounded-full bg-gray-50 mb-4">
                    <i class="fas fa-folder-open text-4xl text-gray-300"></i>
                </div>
                <p class="text-gray-600 font-medium">Belum ada mata pelajaran yang diampu.</p>
                <p class="text-sm text-gray-400 mt-1">Tambahkan mapel yang Anda ajar untuk mulai mengelola.</p>
                <a href="{{ route('akademik.guru-mapel.index') }}" class="mt-4 inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium text-sm">Tambah Mapel Ajar</a>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($guruMapels as $gm)
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 font-bold text-lg">
                                        {{ substr($gm->mapel->nama_mapel ?? '?', 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-bold text-gray-900">{{ $gm->mapel->nama_mapel ?? '-' }}</div>
                                        <div class="text-xs text-gray-500">{{ $gm->mapel->kode_mapel ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    {{ $gm->kelas->nama_kelas ?? ($gm->kelas->nama_unik ?? 'Kelas ?') }}
                                </span>
                                <div class="text-xs text-gray-400 mt-1 ml-1">Tingkat {{ $gm->kelas->level ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex justify-center space-x-3">
                                    <a href="{{ route('akademik.absensi.index', ['guru_mapel_id' => $gm->id]) }}" class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition text-xs font-semibold flex items-center gap-1">
                                        <i class="fas fa-user-check"></i> Absen
                                    </a>
                                    <a href="{{ route('akademik.penilaian.index', ['guru_mapel_id' => $gm->id]) }}" class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1.5 rounded-lg transition text-xs font-semibold flex items-center gap-1">
                                        <i class="fas fa-star"></i> Nilai
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
@endsection
