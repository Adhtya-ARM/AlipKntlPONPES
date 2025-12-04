{{-- REFACTORED SIDEBAR --}}
<div class="w-64 bg-white shadow-xl flex-shrink-0 flex flex-col border-r border-gray-200 overflow-y-auto print:hidden transition-all duration-300">
    
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
        
        $userName = $user ? ($user->display_name ?? $user->username ?? 'N/A') : 'Pengguna';
        $userInitial = strtoupper(substr(trim($userName), 0, 2));
        
        // Get profile and photo
        $userProfile = null;
        $userPhoto = null;
        if ($guardName === 'guru' && $user) {
            $userProfile = \App\Models\User\GuruProfile::where('guru_id', $user->id)->first();
            $userPhoto = $userProfile?->foto;
        } elseif ($guardName === 'santri' && $user) {
            $userProfile = \App\Models\User\SantriProfile::where('santri_id', $user->id)->first();
            $userPhoto = $userProfile?->foto;
        } elseif ($guardName === 'wali' && $user) {
            $userProfile = \App\Models\User\WaliProfile::where('wali_id', $user->id)->first();
            $userPhoto = $userProfile?->foto;
        }
        
        $isWakaOrKepsek = false;
        if ($guardName === 'guru' && $user && $user->guruProfile) {
            $jabatan = strtolower($user->guruProfile->jabatan ?? '');
            $isWakaOrKepsek = in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka']);
        }

        // Fetch School Profile
        $sekolah = \App\Models\Akademik\SekolahProfile::first();
        $appName = $sekolah->nama_sekolah ?? config('app.name', 'Al-Madinah');
        $appLogo = ($sekolah && $sekolah->logo) ? asset('storage/' . $sekolah->logo) : null;
        
        $roleLabel = strtoupper($guardName);
        if ($guardName === 'guru' && $user && $user->guruProfile) {
            $roleLabel = strtoupper($user->guruProfile->jabatan ?? 'GURU');
        }

        // Determine Dashboard Route
        $dashboardRoute = '#';
        if ($guardName === 'guru') $dashboardRoute = route('guru.dashboard');
        elseif ($guardName === 'santri') $dashboardRoute = route('santri.dashboard');
        elseif ($guardName === 'wali') $dashboardRoute = route('wali.dashboard');
        else $dashboardRoute = url('/dashboard');
    @endphp

    {{-- Logo --}}
    <div class="p-4 flex items-center gap-3 h-16 border-b border-gray-100 sticky top-0 bg-white z-10">
        @if($appLogo)
            <img src="{{ $appLogo }}" alt="Logo" class="w-10 h-10 object-contain rounded-lg bg-white border">
        @else
            <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-graduation-cap text-white text-lg"></i>
            </div>
        @endif
        <div class="min-w-0">
            <h2 class="font-bold text-gray-800 text-sm leading-tight truncate">{{ $appName }}</h2>
            <p class="text-xs text-gray-500 truncate">{{ $roleLabel }}</p>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 p-4 space-y-6" x-data="{ activeMenu: null }">
        
        {{-- 1. DASHBOARD --}}
        <div>
            <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Utama</p>
            <a href="{{ $dashboardRoute }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('*dashboard') ? 'bg-blue-50 text-blue-700 font-medium shadow-sm' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                <i class="fas fa-home w-5 text-center {{ Request::is('*dashboard') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                <span class="text-sm">Dashboard</span>
            </a>
        </div>

        {{-- 2. GURU NAVIGATION --}}
        @if($guardName === 'guru')
            
            {{-- AKADEMIK (Daily Tasks) --}}
            <div>
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Akademik</p>
                <div class="space-y-1">
                    <a href="{{ route('akademik.guru-mapel.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/guru-mapel*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-book-reader w-5 text-center {{ Request::is('akademik/guru-mapel*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Mapel Saya</span>
                    </a>
                    <a href="{{ route('akademik.kelas-saya.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/kelas-saya*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-chalkboard w-5 text-center {{ Request::is('akademik/kelas-saya*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Kelas Saya</span>
                    </a>
                    <a href="{{ route('akademik.absensi.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/absensi') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-user-check w-5 text-center {{ Request::is('akademik/absensi') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Input Kehadiran</span>
                    </a>
                    <a href="{{ route('akademik.penilaian.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/penilaian') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-star w-5 text-center {{ Request::is('akademik/penilaian') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Input Penilaian</span>
                    </a>
                    <a href="{{ route('akademik.eraport.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/eraport*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-file-invoice w-5 text-center {{ Request::is('akademik/eraport*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">E-Raport</span>
                    </a>
                    <a href="{{ route('akademik.rencana-pembelajaran.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/rencana-pembelajaran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-calendar-alt w-5 text-center {{ Request::is('akademik/rencana-pembelajaran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Kalender Akademik</span>
                    </a>
                </div>
            </div>

            {{-- LAPORAN & ARSIP --}}
            <div>
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Laporan & Arsip</p>
                <div class="space-y-1">
                    <a href="{{ route('akademik.rekap-kehadiran.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/rekap-kehadiran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-chart-bar w-5 text-center {{ Request::is('akademik/rekap-kehadiran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Rekap Kehadiran</span>
                    </a>
                    <a href="{{ route('akademik.rekap-penilaian.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/rekap-penilaian*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-chart-line w-5 text-center {{ Request::is('akademik/rekap-penilaian*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Rekap Penilaian</span>
                    </a>
                    <a href="{{ route('akademik.guru.arsip.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/guru/arsip*') ? 'bg-amber-50 text-amber-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-amber-600' }} transition-all">
                        <i class="fas fa-history w-5 text-center {{ Request::is('akademik/guru/arsip*') ? 'text-amber-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Riwayat Pembelajaran</span>
                    </a>
                </div>
            </div>

            {{-- INFORMASI --}}
            <div>
                 <a href="{{ route('informasi.struktur-organisasi.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('informasi/struktur-organisasi*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                    <i class="fas fa-sitemap w-5 text-center {{ Request::is('informasi/struktur-organisasi*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                    <span class="text-sm">Struktur Organisasi</span>
                </a>
            </div>

            {{-- WAKA/KEPSEK MENU --}}
            @if($isWakaOrKepsek)
                <div class="pt-2 border-t border-gray-100"></div>
                
                {{-- DATA MASTER --}}
                <div>
                    <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Data Master</p>
                    <div class="space-y-1">
                        <a href="{{ route('santri.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('santri*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                            <i class="fas fa-user-graduate w-5 text-center {{ Request::is('santri*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            <span class="text-sm">Data Santri</span>
                        </a>
                        <a href="{{ route('guru.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('guru*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                            <i class="fas fa-chalkboard-teacher w-5 text-center {{ Request::is('guru*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            <span class="text-sm">Data Guru</span>
                        </a>
                        <a href="{{ route('wali.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('wali*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                            <i class="fas fa-users w-5 text-center {{ Request::is('wali*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                            <span class="text-sm">Data Wali Murid</span>
                        </a>
                    </div>
                </div>

                {{-- PENGATURAN --}}
                <div x-data="{ open: {{ Request::is('akademik/kelas*') || Request::is('akademik/mapel*') || Request::is('akademik/jadwal-pelajaran*') || Request::is('manajemen-sekolah*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 text-gray-700 hover:bg-gray-50 hover:text-blue-600 rounded-lg transition-all group">
                        <div class="flex items-center gap-3">
                            <i class="fas fa-cogs w-5 text-center text-gray-400 group-hover:text-blue-600"></i>
                            <span class="text-sm font-medium">Pengaturan Sekolah</span>
                        </div>
                        <i class="fas fa-chevron-down text-xs text-gray-400 transition-transform duration-200" :class="{'rotate-180': open}"></i>
                    </button>
                    <div x-show="open" x-collapse class="mt-1 pl-3 space-y-1">
                        <a href="{{ route('akademik.kelas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/kelas') && !Request::is('akademik/kelas-saya*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                            <span class="text-sm">Manajemen Kelas</span>
                        </a>
                        <a href="{{ route('akademik.mapel.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/mapel') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                            <span class="text-sm">Mata Pelajaran</span>
                        </a>
                        <a href="{{ route('akademik.jadwal-pelajaran.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/jadwal-pelajaran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                            <span class="text-sm">Jadwal Pelajaran</span>
                        </a>
                        <a href="{{ route('manajemen-sekolah.tahun-ajaran.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('manajemen-sekolah/tahun-ajaran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                            <span class="text-sm">Tahun Ajaran</span>
                        </a>
                        <a href="{{ route('manajemen-sekolah.kenaikan-kelas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('manajemen-sekolah/kenaikan-kelas*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                            <span class="text-sm">Kenaikan Kelas</span>
                        </a>
                        <a href="{{ route('manajemen-sekolah.sekolah.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('manajemen-sekolah/sekolah*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                            <span class="text-sm">Profil Sekolah</span>
                        </a>
                    </div>
                </div>
            @endif

        @endif

        {{-- 3. ADMIN / WEB NAVIGATION --}}
        @if($guardName === 'web')
            
            {{-- DATA MASTER --}}
            <div>
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Data Master</p>
                <div class="space-y-1">
                    <a href="{{ route('santri.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('santri*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-user-graduate w-5 text-center {{ Request::is('santri*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Data Santri</span>
                    </a>
                    <a href="{{ route('guru.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('guru*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-chalkboard-teacher w-5 text-center {{ Request::is('guru*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Data Guru</span>
                    </a>
                    <a href="{{ route('wali.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('wali*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-users w-5 text-center {{ Request::is('wali*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Data Wali Murid</span>
                    </a>
                </div>
            </div>

            {{-- AKADEMIK --}}
            <div>
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Akademik</p>
                <div class="space-y-1">
                    <a href="{{ route('akademik.kelas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/kelas') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-school w-5 text-center {{ Request::is('akademik/kelas') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Manajemen Kelas</span>
                    </a>
                    <a href="{{ route('akademik.mapel.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/mapel') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-book w-5 text-center {{ Request::is('akademik/mapel') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Mata Pelajaran</span>
                    </a>
                    <a href="{{ route('akademik.jadwal-pelajaran.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('akademik/jadwal-pelajaran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-calendar w-5 text-center {{ Request::is('akademik/jadwal-pelajaran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Jadwal Pelajaran</span>
                    </a>
                </div>
            </div>

            {{-- PENGATURAN --}}
            <div>
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Pengaturan</p>
                <div class="space-y-1">
                    <a href="{{ route('manajemen-sekolah.tahun-ajaran.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('manajemen-sekolah/tahun-ajaran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-calendar-check w-5 text-center {{ Request::is('manajemen-sekolah/tahun-ajaran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Tahun Ajaran</span>
                    </a>
                    <a href="{{ route('manajemen-sekolah.kenaikan-kelas.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('manajemen-sekolah/kenaikan-kelas*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-level-up-alt w-5 text-center {{ Request::is('manajemen-sekolah/kenaikan-kelas*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Kenaikan Kelas</span>
                    </a>
                    <a href="{{ route('manajemen-sekolah.sekolah.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('manajemen-sekolah/sekolah*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-university w-5 text-center {{ Request::is('manajemen-sekolah/sekolah*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Profil Sekolah</span>
                    </a>
                    <a href="{{ route('informasi.struktur-organisasi.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('informasi/struktur-organisasi*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-sitemap w-5 text-center {{ Request::is('informasi/struktur-organisasi*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Struktur Organisasi</span>
                    </a>
                </div>
            </div>

        @endif

        {{-- 4. SANTRI NAVIGATION --}}
        @if($guardName === 'santri')
            <div>
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Akademik</p>
                <div class="space-y-1">
                    <a href="{{ route('santri.kehadiran') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('santri/kehadiran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-user-check w-5 text-center {{ Request::is('santri/kehadiran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Kehadiran Saya</span>
                    </a>
                    <a href="{{ route('santri.mapel') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('santri/mapel*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-book w-5 text-center {{ Request::is('santri/mapel*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Mata Pelajaran</span>
                    </a>
                    <a href="{{ route('santri.nilai') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('santri/nilai*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-star w-5 text-center {{ Request::is('santri/nilai*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Nilai Saya</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- 5. WALI NAVIGATION --}}
        @if($guardName === 'wali')
            <div>
                <p class="px-3 text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Monitoring Anak</p>
                <div class="space-y-1">
                    <a href="{{ route('wali.kehadiran') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('wali/kehadiran*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-user-check w-5 text-center {{ Request::is('wali/kehadiran*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Kehadiran</span>
                    </a>
                    <a href="{{ route('wali.mapel') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('wali/mapel*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-book w-5 text-center {{ Request::is('wali/mapel*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Mata Pelajaran</span>
                    </a>
                    <a href="{{ route('wali.nilai') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg {{ Request::is('wali/nilai*') ? 'bg-blue-50 text-blue-700 font-medium' : 'text-gray-700 hover:bg-gray-50 hover:text-blue-600' }} transition-all">
                        <i class="fas fa-star w-5 text-center {{ Request::is('wali/nilai*') ? 'text-blue-600' : 'text-gray-400' }}"></i>
                        <span class="text-sm">Nilai</span>
                    </a>
                </div>
            </div>
        @endif

    </nav>

    {{-- User Profile & Logout --}}
    <div class="border-t border-gray-100 bg-gray-50">
        
        {{-- Profile Info --}}
        <div class="p-4 pb-2">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white font-bold text-xs overflow-hidden shadow-md flex-shrink-0">
                    @if($userPhoto)
                        <img src="{{ asset('storage/' . $userPhoto) }}" alt="Photo" class="w-full h-full object-cover">
                    @else
                        {{ $userInitial }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    @php
                        $displayName = $userName;
                        if(isset($userProfile) && isset($userProfile->nama) && $userProfile->nama) {
                            $displayName = $userProfile->nama;
                        }
                    @endphp
                    <p class="font-semibold text-gray-900 text-sm truncate" title="{{ $displayName }}">{{ $displayName }}</p>
                    <p class="text-xs text-gray-600 truncate">{{ $roleLabel }}</p>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="px-4 pb-4 space-y-2">
            <a href="{{ route('profile.index') }}" class="block w-full text-center py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 hover:text-blue-600 transition">
                <i class="fas fa-user-cog mr-1"></i> Edit Profil
            </a>

            <form method="POST" action="{{ route('logout', ['guard' => $guardName]) }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 text-xs bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-all font-medium border border-red-100">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Keluar</span>
                </button>
            </form>
        </div>
    </div>

</div>