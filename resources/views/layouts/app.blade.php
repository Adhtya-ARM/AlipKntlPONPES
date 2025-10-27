<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Acme Inc. Dashboard')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Memuat Tailwind CSS dan JavaScript --}}
  
    
    <style>
        /* Custom scrollbar untuk sidebar jika diperlukan */
        .custom-scroll-y::-webkit-scrollbar {
            width: 8px;
        }
        .custom-scroll-y::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .custom-scroll-y::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .custom-scroll-y::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;  /* IE and Edge */
            scrollbar-width: none;  /* Firefox */
        }
    </style>
</head>
<body class="bg-gray-100 font-sans antialiased">
    @php
        // Daftar nama rute yang TIDAK boleh menampilkan sidebar
        // Halaman login, register, landing page, dll.
        $noSidebarRoutes = [
            'login', 
            'register', 
            'landing', 
            'password.request', 
            'password.reset'
        ];
        
        // PENTING: Cek apakah ada guard yang sedang login
        $isLoggedIn = Auth::check() || 
                      Auth::guard('santri')->check() || 
                      Auth::guard('wali')->check() || 
                      Auth::guard('guru')->check(); // <-- PASTIKAN INI ADA!
        
        // Tampilkan sidebar jika sedang login DAN rute saat ini bukan rute yang dikecualikan
        $showSidebar = $isLoggedIn && !in_array(Route::currentRouteName(), $noSidebarRoutes);
    
    @endphp
    
    <div class="flex h-screen">
        
        @if ($showSidebar)
            <x-sidebar /> 
        @endif

        {{-- Main Content Area --}}
        <div class="flex-1 flex flex-col overflow-hidden">
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                @yield('content')
            </main>
        </div>
    </div>

</body>
</html>