<div class="w-64 bg-white shadow-xl flex-shrink-0 flex flex-col border-r border-gray-200 overflow-y-auto no-scrollbar custom-scroll-y">

    {{-- Logika PHP untuk mengambil data user dan guard --}}
    @php
        $guardName = 'web'; 
        $user = null; // Inisialisasi
    
        if (Auth::guard('guru')->check()) {
            $guardName = 'guru';
            $user = Auth::guard('guru')->user();
        } elseif (Auth::guard('santri')->check()) {
            $guardName = 'santri';
            $user = Auth::guard('santri')->user();
        } elseif (Auth::guard('wali')->check()) {
            $guardName = 'wali';
            $user = Auth::guard('wali')->user();
        }
            
        // Mengambil data nama dan email
        if ($user) {
            // Menggunakan accessor baru (display_name)
            // Jika model memiliki relasi profile, ini akan memuat nama dari profile
            $userName = $user->display_name ?? $user->username ?? 'N/A'; 
        } else {
            $userName = 'Pengguna';
        }
    
        $userInitial = strtoupper(substr(trim($userName), 0, 2));
    
        // Untuk navigasi: contoh path aktif
        $currentPath = Request::path();
    @endphp

    {{-- Logo/Branding --}}
    <div class="p-4 flex items-center h-16 border-b border-gray-200">
        <h1 class="text-lg font-bold text-gray-800">Acme Inc.</h1>
    </div>

    <nav class="flex-1 p-4 space-y-1 text-sm">
        
        {{-- Main Navigation --}}
        <a href="#" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100 font-medium">
            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
            Dashboard
        </a>

        <div class="border-t border-gray-200 mt-4 pt-4"></div>

        {{-- MASTER DATA Dropdown --}}
        <div x-data="{ open_master: false }" class="space-y-1">
            
            {{-- Header/Trigger Dropdown --}}
            <h3 @click="open_master = !open_master" class="flex items-center justify-between cursor-pointer px-3 py-2 text-xs uppercase text-gray-500 font-semibold mt-4 hover:bg-gray-50 rounded-md transition duration-150">
                <span>Master Data</span>
                {{-- PERBAIKAN CLASS: Mengganti 'transitiaton-transform' menjadi 'transition-transform' --}}
                <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-90': open_master, 'rotate-0': !open_master }" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </h3>
            
            {{-- Dropdown Content (Auto-hide) --}}
            <div x-show="open_master" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0 -translate-y-2" 
                 x-transition:enter-end="opacity-100 translate-y-0" 
                 x-transition:leave="transition ease-in duration-150" 
                 x-transition:leave-start="opacity-100 translate-y-0" 
                 x-transition:leave-end="opacity-0 -translate-y-2" 
                 class="pl-4 space-y-1"
                 x-cloak>
                 
                <a href="/santri" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.653-.162-1.285-.474-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.653.162-1.285.474-1.857m0 0a5.002 5.002 0 019.052 0M10 12h.01M10 16h.01"></path></svg>
                    Data Santri
                </a>
                <a href="/guru" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Data Guru
                </a>
                <a href="/wali" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Data Wali
                </a>
            </div>
        </div>
        {{-- END MASTER DATA Dropdown --}}

        <div class="border-t border-gray-200 mt-4 pt-4"></div>

        {{-- AKADEMIK Dropdown --}}
        {{-- PERBAIKAN ERROR: open_akademik sekarang digunakan dalam x-data --}}
        <div x-data="{ open_akademik: false }" class="space-y-1">
            
            {{-- Header/Trigger Dropdown --}}
            <h3 @click="open_akademik = !open_akademik" class="flex items-center justify-between cursor-pointer px-3 py-2 text-xs uppercase text-gray-500 font-semibold mt-4 hover:bg-gray-50 rounded-md transition duration-150">
                <span>Akademik</span>
                {{-- PERBAIKAN ERROR: Menggunakan open_akademik sebagai variabel kelas --}}
                <svg class="w-4 h-4 transform transition-transform duration-200" :class="{ 'rotate-90': open_akademik, 'rotate-0': !open_akademik }" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </h3>
            
            {{-- Dropdown Content (Auto-hide) --}}
            <div x-show="open_akademik" 
                 x-transition:enter="transition ease-out duration-300" 
                 x-transition:enter-start="opacity-0 -translate-y-2" 
                 x-transition:enter-end="opacity-100 translate-y-0" 
                 x-transition:leave="transition ease-in duration-150" 
                 x-transition:leave-start="opacity-100 translate-y-0" 
                 x-transition:leave-end="opacity-0 -translate-y-2" 
                 class="pl-4 space-y-1"
                 x-cloak>
                 
                <a href="/akademik/mapel" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                    <i class="fas fa-book-open w-5 h-5 mr-3 text-gray-400"></i>
                    Mata Pelajaran
                </a>
                <a href="{{ route('akademik.penilaian.index') }}" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 2H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Penilaian
                </a>
                <a href="{{ route('akademik.absensi.index') }}" class="flex items-center px-3 py-2 rounded-md text-gray-700 hover:bg-gray-100">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h.01M7 15h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Absensi
                </a>
            </div>
        </div>
        {{-- END AKADEMIK Dropdown --}}
    </nav>
    
    {{-- User/Account info at the bottom with Dropdown --}}
    <div class="mt-auto p-4 border-t border-gray-200 relative" x-data="{ open: false }" @click.outside="open = false">
        
        {{-- Tombol/Area yang Dapat Diklik --}}
        <div class="flex items-center justify-between cursor-pointer hover:bg-gray-100 p-2 rounded-md" @click="open = !open">
            <div class="flex items-center space-x-3">
                {{-- Initial dinamis --}}
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-semibold text-gray-700 flex-shrink-0">
                    {{ $userInitial }}
                </div>
                <div>
                    {{-- Nama dinamis dari profile/username --}}
                    <p class="text-sm font-semibold text-gray-800 leading-none">{{ $userName }}</p>
                    {{-- Email dinamis dari user auth (diasumsikan tidak ditampilkan) --}}
                </div>
            </div>
            {{-- Icon Tiga Titik --}}
            <svg class="w-5 h-5 text-gray-500 hover:text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01"></path></svg>
        </div>

        {{-- Dropdown Menu (Pop-up) --}}
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 transform scale-100"
             x-transition:leave-end="opacity-0 transform scale-95"
             
             class="absolute bottom-full left-0 mb-2 w-full z-50 
                    bg-white rounded-lg shadow-xl border border-gray-200"
             x-cloak> 
            
            <div class="p-3 border-b border-gray-200">
                <p class="text-sm font-semibold text-gray-800">{{ $userName }}</p>
            </div>
            
            <div class="py-1">
                {{-- Link Pengaturan Akun --}}
                <a href="#" class="flex items-center space-x-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    <span>Akun Saya</span>
                </a>
                <a href="#" class="flex items-center space-x-3 px-3 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.585.358 1.25.59 1.95.692z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Settings</span>
                </a>
            </div>

            <div class="border-t border-gray-200 py-1">
                {{-- Log out Form --}}
                <form method="POST" action="{{ route('logout', ['guard' => $guardName]) }}">
                    @csrf
                    <button type="submit" class="flex items-center space-x-3 w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        <span>Log out</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>