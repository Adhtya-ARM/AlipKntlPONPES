<div class="w-64 bg-white shadow-xl flex-shrink-0 flex flex-col border-r border-gray-200 overflow-y-auto no-scrollbar custom-scroll-y">

    {{-- Logika PHP untuk mengambil data user dan guard --}}
    @php
        $guardName = 'web'; 
        $user = null;
    
        if (Auth::guard('guru')->check()) {
            $guardName = 'guru';
            $user = Auth::guard('guru')->user();
        } elseif (Auth::guard('santri')->check()) {
            $guardName = 'santri';
            $user = Auth::guard('santri')->user();
        } elseif (Auth::guard('wali')->check()) {
            $guardName = 'wali';
            $user = Auth::guard('wali')->user();
        } elseif (Auth::guard('web')->check()) {
            $guardName = 'web';
            $user = Auth::guard('web')->user();
        }
            
        if ($user) {
            $userName = $user->display_name ?? $user->username ?? 'N/A'; 
        } else {
            $userName = 'Pengguna';
        }
    
        $userInitial = strtoupper(substr(trim($userName), 0, 2));
        $currentPath = Request::path();
        $isWakaOrKepsek = false;
        if ($guardName === 'guru' && $user && $user->guruProfile) {
            $jabatan = strtolower($user->guruProfile->jabatan ?? '');
            $isWakaOrKepsek = in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka']);
        }
    @endphp

    {{-- Logo/Branding --}}
    <div class="p-4 flex items-center gap-3 h-16 border-b border-gray-200">
        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center">
            <i class="fas fa-graduation-cap text-white text-lg"></i>
        </div>
        <div>
            <h1 class="text-sm font-bold text-gray-800">{{ config('app.name', 'ADMIN SEKOLAH') }}</h1>
            <p class="text-xs text-gray-500">{{ strtoupper($guardName) }}</p>
        </div>
    </div>

    <nav class="flex-1 p-4 space-y-1 text-sm">
        
        {{-- ADMIN / GURU Section --}}
        @if($guardName === 'guru' || $guardName === 'web')
        
        {{-- Dashboard --}}
        <a href="{{ route($guardName.'.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is($guardName.'/dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition">
            <i class="fas fa-home w-5 mr-3 {{ Request::is($guardName.'/dashboard') ? 'text-blue-600' : 'text-gray-400' }}"></i>
            Dashboard
        </a>

        <div class="border-t border-gray-200 my-3"></div>

        {{-- MASTER DATA Section (Waka/Kepsek Only) --}}
        @if($isWakaOrKepsek || $guardName === 'web')
        <div x-data="{ open: {{ Request::is('santri*') || Request::is('guru*') || Request::is('wali*') ? 'true' : 'false' }} }" class="space-y-1">
            <h3 @click="open = !open" class="flex items-center justify-between cursor-pointer px-3 py-2 text-xs uppercase text-gray-500 font-semibold hover:bg-gray-50 rounded-lg transition">
                <div class="flex items-center gap-2">
                    <i class="fas fa-users-cog text-xs"></i>
                    <span>Manajemen User</span>
                </div>
                <i class="fas fa-chevron-right text-xs transform transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </h3>
            
            <div x-show="open" x-transition class="pl-4 space-y-1">
                <a href="/santri" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('santri*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-user-graduate w-5 mr-3 text-sm {{ Request::is('santri*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Data Santri
                </a>
                <a href="/guru" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('guru*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-chalkboard-teacher w-5 mr-3 text-sm {{ Request::is('guru*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Data Guru
                </a>
                <a href="/wali" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('wali*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-user-friends w-5 mr-3 text-sm {{ Request::is('wali*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Data Wali Murid
                </a>
            </div>
        </div>

        <div class="border-t border-gray-200 my-3"></div>
        @endif

        {{-- AKADEMIK Section --}}
        <div x-data="{ open: {{ Request::is('akademik*') ? 'true' : 'false' }} }" class="space-y-1">
            <h3 @click="open = !open" class="flex items-center justify-between cursor-pointer px-3 py-2 text-xs uppercase text-gray-500 font-semibold hover:bg-gray-50 rounded-lg transition">
                <div class="flex items-center gap-2">
                    <i class="fas fa-book-open text-xs"></i>
                    <span>Akademik</span>
                </div>
                <i class="fas fa-chevron-right text-xs transform transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </h3>
            
            <div x-show="open" x-transition class="pl-4 space-y-1">
                
                @if($isWakaOrKepsek || $guardName === 'web')
                <a href="{{ route('akademik.kelas.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('akademik/kelas*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-door-open w-5 mr-3 text-sm {{ Request::is('akademik/kelas*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Manajemen Kelas
                </a>
                <a href="/akademik/mapel" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('akademik/mapel') && !Request::is('akademik/mapel/*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-book w-5 mr-3 text-sm {{ Request::is('akademik/mapel') && !Request::is('akademik/mapel/*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Mata Pelajaran
                </a>
                @endif

                @if($guardName === 'guru')
                <a href="{{ route('akademik.kelas-saya.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('akademik/kelas-saya*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-chalkboard-teacher w-5 mr-3 text-sm {{ Request::is('akademik/kelas-saya*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Kelas Saya
                </a>
                @endif

                <a href="{{ route('akademik.guru-mapel.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('akademik/guru-mapel*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-chalkboard w-5 mr-3 text-sm {{ Request::is('akademik/guru-mapel*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Mapel Saya
                </a>
                <a href="{{ route('akademik.rencana-pembelajaran.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('akademik/rencana-pembelajaran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-calendar-alt w-5 mr-3 text-sm {{ Request::is('akademik/rencana-pembelajaran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Kalender Akademik
                </a>
                <a href="{{ route('akademik.penilaian.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('akademik/penilaian') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-file-alt w-5 mr-3 text-sm {{ Request::is('akademik/penilaian') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Input Penilaian
                </a>
                
                <a href="{{ route('akademik.rekap-penilaian.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('akademik/rekap-penilaian*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-chart-line w-5 mr-3 text-sm {{ Request::is('akademik/rekap-penilaian*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Rekap Penilaian
                </a>
            </div>
        </div>

        <div class="border-t border-gray-200 my-3"></div>

        {{-- KEHADIRAN Section --}}
        <div x-data="{ open: {{ Request::is('akademik/absensi*') || Request::is('akademik/rekap-kehadiran*') ? 'true' : 'false' }} }" class="space-y-1">
            <h3 @click="open = !open" class="flex items-center justify-between cursor-pointer px-3 py-2 text-xs uppercase text-gray-500 font-semibold hover:bg-gray-50 rounded-lg transition">
                <div class="flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-xs"></i>
                    <span>Kehadiran</span>
                </div>
                <i class="fas fa-chevron-right text-xs transform transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </h3>
            
            <div x-show="open" x-transition class="pl-4 space-y-1">
                <a href="{{ route('akademik.absensi.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('akademik/absensi') && !Request::is('akademik/absensi-harian*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-user-check w-5 mr-3 text-sm {{ Request::is('akademik/absensi') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Input Kehadiran (Mapel)
                </a>
                
                <a href="{{ route('akademik.rekap-kehadiran.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('akademik/rekap-kehadiran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-chart-bar w-5 mr-3 text-sm {{ Request::is('akademik/rekap-kehadiran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Rekap Kehadiran Siswa
                </a>
            </div>
        </div>

        @endif

        {{-- SANTRI Section --}}
        @if($guardName === 'santri')
        <a href="{{ route('santri.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('santri/dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition">
            <i class="fas fa-home w-5 mr-3 {{ Request::is('santri/dashboard') ? 'text-blue-600' : 'text-gray-400' }}"></i>
            Dashboard
        </a>

        <div class="border-t border-gray-200 my-3"></div>

        {{-- Akademik Saya --}}
        <div x-data="{ open: {{ Request::is('santri/kehadiran*') || Request::is('santri/mapel*') || Request::is('santri/nilai*') ? 'true' : 'false' }} }" class="space-y-1">
            <h3 @click="open = !open" class="flex items-center justify-between cursor-pointer px-3 py-2 text-xs uppercase text-gray-500 font-semibold hover:bg-gray-50 rounded-lg transition">
                <div class="flex items-center gap-2">
                    <i class="fas fa-book-reader text-xs"></i>
                    <span>Akademik Saya</span>
                </div>
                <i class="fas fa-chevron-right text-xs transform transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </h3>
            
            <div x-show="open" x-transition class="pl-4 space-y-1">
                <a href="{{ route('santri.kehadiran') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('santri/kehadiran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-calendar-check w-5 mr-3 text-sm {{ Request::is('santri/kehadiran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Rekap Kehadiran
                </a>
                <a href="{{ route('santri.mapel') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('santri/mapel*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-book w-5 mr-3 text-sm {{ Request::is('santri/mapel*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Mata Pelajaran
                </a>
                <a href="{{ route('santri.nilai') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('santri/nilai*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-star w-5 mr-3 text-sm {{ Request::is('santri/nilai*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Nilai Saya
                </a>
            </div>
        </div>
        @endif

        {{-- WALI Section --}}
        @if($guardName === 'wali')
        <a href="{{ route('wali.dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('wali/dashboard') ? 'bg-blue-50 text-blue-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition">
            <i class="fas fa-home w-5 mr-3 {{ Request::is('wali/dashboard') ? 'text-blue-600' : 'text-gray-400' }}"></i>
            Dashboard
        </a>

        <div class="border-t border-gray-200 my-3"></div>

        {{-- Monitoring Anak --}}
        <div x-data="{ open: {{ Request::is('wali/kehadiran*') || Request::is('wali/mapel*') || Request::is('wali/nilai*') ? 'true' : 'false' }} }" class="space-y-1">
            <h3 @click="open = !open" class="flex items-center justify-between cursor-pointer px-3 py-2 text-xs uppercase text-gray-500 font-semibold hover:bg-gray-50 rounded-lg transition">
                <div class="flex items-center gap-2">
                    <i class="fas fa-user-friends text-xs"></i>
                    <span>Monitoring Anak</span>
                </div>
                <i class="fas fa-chevron-right text-xs transform transition-transform duration-200" :class="{ 'rotate-90': open }"></i>
            </h3>
            
            <div x-show="open" x-transition class="pl-4 space-y-1">
                <a href="{{ route('wali.kehadiran') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('wali/kehadiran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-calendar-check w-5 mr-3 text-sm {{ Request::is('wali/kehadiran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Rekap Kehadiran
                </a>
                <a href="{{ route('wali.mapel') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('wali/mapel*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-book w-5 mr-3 text-sm {{ Request::is('wali/mapel*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Mata Pelajaran
                </a>
                <a href="{{ route('wali.nilai') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ Request::is('wali/nilai*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }} transition">
                    <i class="fas fa-star w-5 mr-3 text-sm {{ Request::is('wali/nilai*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    Nilai Anak
                </a>
            </div>
        </div>
        @endif
    </nav>
    
    {{-- User/Account info at the bottom with Dropdown --}}
    <div class="mt-auto p-4 border-t border-gray-200 relative" x-data="{ open: false }" @click.outside="open = false">
        
        <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 p-2 rounded-lg transition" @click="open = !open">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                    {{ $userInitial }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800 leading-tight">{{ $userName }}</p>
                    <p class="text-xs text-gray-500">{{ ucfirst($guardName) }}</p>
                </div>
            </div>
            <i class="fas fa-ellipsis-v text-gray-400 hover:text-gray-600"></i>
        </div>

        {{-- Dropdown Menu --}}
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             class="absolute bottom-full left-0 mb-2 w-full z-50 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden"
             x-cloak>
            
            <div class="p-3 bg-gradient-to-br from-blue-500 to-purple-600">
                <p class="text-sm font-semibold text-white">{{ $userName }}</p>
                <p class="text-xs text-white/80">{{ ucfirst($guardName) }}</p>
            </div>

            <div class="border-t border-gray-200 py-1">
                <form method="POST" action="{{ route('logout', ['guard' => $guardName]) }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                        <i class="fas fa-sign-out-alt text-red-500 w-4"></i>
                        <span>Keluar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>