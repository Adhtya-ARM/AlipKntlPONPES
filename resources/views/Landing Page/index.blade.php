<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sekolah->nama_sekolah ?? 'Pondok Pesantren Al-Madinah' }}</title>
    <link rel="icon" href="{{ $sekolah->logo ? asset('storage/' . $sekolah->logo) : asset('gambar/logo.png') }}">
    
    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    
    {{-- Icons --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- Tailwind & Alpine --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Playfair Display', 'serif'],
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        gold: {
                            400: '#fbbf24',
                            500: '#f59e0b',
                            600: '#d97706',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .hero-pattern {
            background-color: #1e3a8a;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%233b82f6' fill-opacity='0.08'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="font-sans antialiased text-gray-800 bg-gray-50" x-data="{ scrolled: false, mobileMenu: false }" @scroll.window="scrolled = (window.pageYOffset > 20)">

    {{-- NAVBAR --}}
    <nav class="fixed w-full z-50 transition-all duration-300" 
         :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-lg py-3' : 'bg-transparent py-6'">
        <div class="container mx-auto px-6 flex justify-between items-center">
            {{-- Logo --}}
            <a href="#" class="flex items-center gap-3 group">
                <img src="{{ $sekolah->logo ? asset('storage/' . $sekolah->logo) : asset('gambar/logo.png') }}" 
                     alt="Logo" class="h-12 w-auto transition-transform group-hover:scale-105">
                <div class="flex flex-col">
                    <span class="font-bold text-lg leading-tight" :class="scrolled ? 'text-gray-900' : 'text-white'">
                        {{ $sekolah->nama_sekolah ?? 'Al-Madinah' }}
                    </span>
                    <span class="text-xs tracking-wider uppercase" :class="scrolled ? 'text-gray-500' : 'text-blue-200'">
                        Islamic Boarding School
                    </span>
                </div>
            </a>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="#beranda" class="font-medium hover:text-gold-500 transition" :class="scrolled ? 'text-gray-700' : 'text-white'">Beranda</a>
                <a href="#tentang" class="font-medium hover:text-gold-500 transition" :class="scrolled ? 'text-gray-700' : 'text-white'">Tentang</a>
                <a href="#program" class="font-medium hover:text-gold-500 transition" :class="scrolled ? 'text-gray-700' : 'text-white'">Program</a>
                <a href="#kontak" class="font-medium hover:text-gold-500 transition" :class="scrolled ? 'text-gray-700' : 'text-white'">Kontak</a>
                
                @auth
                    @php
                        $dashboardRoute = '#';
                        if(Auth::guard('guru')->check()) $dashboardRoute = route('guru.dashboard');
                        elseif(Auth::guard('santri')->check()) $dashboardRoute = route('santri.dashboard');
                        elseif(Auth::guard('wali')->check()) $dashboardRoute = route('wali.dashboard');
                        else $dashboardRoute = url('/dashboard');
                    @endphp
                    <a href="{{ $dashboardRoute }}" class="px-6 py-2.5 rounded-full font-semibold shadow-lg transform hover:-translate-y-0.5 transition duration-200"
                       :class="scrolled ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-white text-primary-700 hover:bg-gray-100'">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-6 py-2.5 rounded-full font-semibold shadow-lg transform hover:-translate-y-0.5 transition duration-200"
                       :class="scrolled ? 'bg-primary-600 text-white hover:bg-primary-700' : 'bg-white text-primary-700 hover:bg-gray-100'">
                        Masuk
                    </a>
                @endauth
            </div>

            {{-- Mobile Menu Button --}}
            <button @click="mobileMenu = !mobileMenu" class="md:hidden text-2xl focus:outline-none" :class="scrolled ? 'text-gray-800' : 'text-white'">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        {{-- Mobile Menu Dropdown --}}
        <div x-show="mobileMenu" x-collapse class="md:hidden bg-white border-t mt-3 shadow-xl">
            <div class="flex flex-col p-4 space-y-3">
                <a href="#beranda" class="text-gray-700 font-medium hover:text-primary-600">Beranda</a>
                <a href="#tentang" class="text-gray-700 font-medium hover:text-primary-600">Tentang</a>
                <a href="#program" class="text-gray-700 font-medium hover:text-primary-600">Program</a>
                <a href="#kontak" class="text-gray-700 font-medium hover:text-primary-600">Kontak</a>
                <a href="{{ route('login') }}" class="text-center bg-primary-600 text-white py-2 rounded-lg font-semibold">Masuk Area Siswa</a>
            </div>
        </div>
    </nav>

    {{-- HERO SECTION --}}
    <header id="beranda" class="relative min-h-screen flex items-center justify-center overflow-hidden hero-pattern">
        {{-- Background Elements --}}
        <div class="absolute inset-0 bg-gradient-to-b from-black/70 via-black/50 to-primary-900/90 z-0"></div>
        
        {{-- Content --}}
        <div class="relative z-10 container mx-auto px-6 text-center pt-20">
            <div class="inline-block mb-4 px-4 py-1.5 rounded-full bg-white/10 backdrop-blur border border-white/20 text-white text-sm font-medium tracking-wide animate-fade-in-down">
                Ahlan Wa Sahlan di Pondok Pesantren Al-Madinah
            </div>
            
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-serif font-bold text-white mb-6 leading-tight drop-shadow-lg">
                Membentuk Generasi <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-gold-400 to-yellow-200">Qur'ani & Berprestasi</span>
            </h1>
            
            <p class="text-lg md:text-xl text-gray-200 mb-10 max-w-2xl mx-auto leading-relaxed">
                Menyala dengan ilmu, beradab dengan akhlak. Kami berkomitmen mencetak pemimpin masa depan yang hafal Al-Qur'an dan unggul dalam sains.
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                <a href="#program" class="px-8 py-4 bg-gold-500 hover:bg-gold-600 text-white rounded-full font-bold shadow-lg shadow-gold-500/30 transform hover:-translate-y-1 transition duration-300 flex items-center gap-2">
                    Lihat Program <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#tentang" class="px-8 py-4 bg-white/10 hover:bg-white/20 backdrop-blur border border-white/30 text-white rounded-full font-bold transform hover:-translate-y-1 transition duration-300">
                    Tentang Kami
                </a>
            </div>

            {{-- Stats --}}
            <div class="mt-20 grid grid-cols-2 md:grid-cols-4 gap-8 border-t border-white/10 pt-10">
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white mb-1">500+</div>
                    <div class="text-sm text-gray-400">Santri Aktif</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white mb-1">45+</div>
                    <div class="text-sm text-gray-400">Guru Berdedikasi</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white mb-1">100%</div>
                    <div class="text-sm text-gray-400">Lulusan Tahfidz</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white mb-1">A</div>
                    <div class="text-sm text-gray-400">Akreditasi</div>
                </div>
            </div>
        </div>
    </header>

    {{-- TENTANG KAMI --}}
    <section id="tentang" class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-16">
                <div class="lg:w-1/2 relative">
                    <div class="absolute -top-4 -left-4 w-24 h-24 bg-gold-100 rounded-full opacity-50 blur-xl"></div>
                    <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-primary-100 rounded-full opacity-50 blur-xl"></div>
                    <img src="https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" 
                         alt="Tentang Kami" class="relative rounded-2xl shadow-2xl z-10 w-full object-cover h-[500px]">
                    <div class="absolute -bottom-10 -left-10 bg-white p-6 rounded-xl shadow-xl z-20 hidden md:block max-w-xs border-l-4 border-gold-500">
                        <p class="text-gray-600 italic">"Pendidikan adalah senjata paling mematikan di dunia, karena dengan pendidikan, Anda dapat mengubah dunia."</p>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <span class="text-gold-600 font-bold tracking-wider uppercase text-sm">Tentang Al-Madinah</span>
                    <h2 class="text-4xl font-serif font-bold text-gray-900 mt-2 mb-6">Mewujudkan Generasi Emas Berkarakter Islami</h2>
                    <p class="text-gray-600 leading-relaxed mb-6 text-lg">
                        {{ $sekolah->visi ?? 'Pondok Pesantren Al-Madinah berdedikasi untuk menyelenggarakan pendidikan Islam yang holistik, memadukan kurikulum nasional dengan nilai-nilai kepesantrenan.' }}
                    </p>
                    
                    <div class="space-y-4 mb-8">
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center flex-shrink-0 text-primary-600">
                                <i class="fas fa-quran"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Tahfidz Al-Qur'an</h4>
                                <p class="text-sm text-gray-500">Program unggulan hafalan Al-Qur'an bersanad.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center flex-shrink-0 text-primary-600">
                                <i class="fas fa-laptop-code"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Teknologi & Sains</h4>
                                <p class="text-sm text-gray-500">Pembelajaran berbasis IT dan sains modern.</p>
                            </div>
                        </div>
                        <div class="flex items-start gap-4">
                            <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center flex-shrink-0 text-primary-600">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Bahasa Asing</h4>
                                <p class="text-sm text-gray-500">Penguasaan Bahasa Arab dan Inggris aktif.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PROGRAM PENDIDIKAN --}}
    <section id="program" class="py-24 bg-gray-50 relative overflow-hidden">
        {{-- Background Decoration --}}
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden opacity-5 pointer-events-none">
            <i class="fas fa-star absolute top-10 left-10 text-9xl"></i>
            <i class="fas fa-moon absolute bottom-20 right-20 text-9xl"></i>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16">
                <span class="text-primary-600 font-bold tracking-wider uppercase text-sm">Jenjang Pendidikan</span>
                <h2 class="text-4xl font-serif font-bold text-gray-900 mt-2">Program Unggulan Kami</h2>
                <p class="text-gray-500 mt-4 max-w-2xl mx-auto">Kami menyediakan jenjang pendidikan berkelanjutan dari SMP hingga SMA dengan kurikulum terintegrasi.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-10 max-w-5xl mx-auto">
                {{-- SMP CARD --}}
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden group hover:shadow-2xl transition duration-300 border border-gray-100">
                    <div class="h-64 bg-cover bg-center relative" style="background-image: url('https://images.unsplash.com/photo-1509062522246-3755977927d7?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                        <div class="absolute bottom-6 left-6 text-white">
                            <span class="bg-primary-600 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-2 inline-block">Kelas 7-9</span>
                            <h3 class="text-3xl font-bold">SMP Islam</h3>
                            <p class="text-gray-300 text-sm mt-1">Sekolah Menengah Pertama</p>
                        </div>
                    </div>
                    <div class="p-8">
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            Membangun pondasi karakter Islami yang kuat dengan fokus pada pembiasaan ibadah, tahsin Al-Qur'an, dan dasar-dasar ilmu pengetahuan umum.
                        </p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-check-circle text-green-500"></i> Target Hafalan 5 Juz
                            </li>
                            <li class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-check-circle text-green-500"></i> Pembinaan Akhlak Mulia
                            </li>
                            <li class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-check-circle text-green-500"></i> Ekstrakurikuler Robotik & Pramuka
                            </li>
                        </ul>
                        <a href="#" class="block w-full text-center py-3 rounded-xl border-2 border-primary-600 text-primary-600 font-bold hover:bg-primary-600 hover:text-white transition">
                            Selengkapnya
                        </a>
                    </div>
                </div>

                {{-- SMA CARD --}}
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden group hover:shadow-2xl transition duration-300 border border-gray-100">
                    <div class="h-64 bg-cover bg-center relative" style="background-image: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent"></div>
                        <div class="absolute bottom-6 left-6 text-white">
                            <span class="bg-gold-500 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide mb-2 inline-block">Kelas 10-12</span>
                            <h3 class="text-3xl font-bold">SMA Islam</h3>
                            <p class="text-gray-300 text-sm mt-1">Sekolah Menengah Atas</p>
                        </div>
                    </div>
                    <div class="p-8">
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            Menyiapkan pemimpin masa depan yang intelektual dan spiritual. Fokus pada pendalaman ilmu syar'i, persiapan kuliah, dan leadership.
                        </p>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-check-circle text-green-500"></i> Target Hafalan 15-30 Juz
                            </li>
                            <li class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-check-circle text-green-500"></i> Bimbingan Masuk PTN/Timur Tengah
                            </li>
                            <li class="flex items-center gap-3 text-gray-700">
                                <i class="fas fa-check-circle text-green-500"></i> Entrepreneurship & Public Speaking
                            </li>
                        </ul>
                        <a href="#" class="block w-full text-center py-3 rounded-xl border-2 border-gold-500 text-gold-600 font-bold hover:bg-gold-500 hover:text-white transition">
                            Selengkapnya
                        </a>
                    </div>
                </div>
            </div>

            {{-- Additional Programs --}}
            <div class="mt-16 flex flex-wrap justify-center gap-4">
                <span class="px-6 py-2 bg-white rounded-full shadow-sm text-gray-600 font-medium border border-gray-200">
                    <i class="fas fa-book-open text-primary-500 mr-2"></i> Madrasah Diniyah
                </span>
                <span class="px-6 py-2 bg-white rounded-full shadow-sm text-gray-600 font-medium border border-gray-200">
                    <i class="fas fa-microphone-alt text-primary-500 mr-2"></i> Muhadhoroh
                </span>
                <span class="px-6 py-2 bg-white rounded-full shadow-sm text-gray-600 font-medium border border-gray-200">
                    <i class="fas fa-swimmer text-primary-500 mr-2"></i> Olahraga Sunnah
                </span>
                <span class="px-6 py-2 bg-white rounded-full shadow-sm text-gray-600 font-medium border border-gray-200">
                    <i class="fas fa-hands-helping text-primary-500 mr-2"></i> Bakti Sosial
                </span>
            </div>
        </div>
    </section>

    {{-- STRUKTUR ORGANISASI / GURU --}}
    <section class="py-24 bg-white relative overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16">
                <span class="text-primary-600 font-bold tracking-wider uppercase text-sm">Tim Pengajar</span>
                <h2 class="text-4xl font-serif font-bold text-gray-900 mt-2">Guru & Staff Kami</h2>
                <p class="text-gray-500 mt-4 max-w-2xl mx-auto">Dibimbing oleh asatidz yang kompeten dan berdedikasi tinggi dalam mendidik santri.</p>
            </div>

            @if($struktur->count() > 0)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($struktur as $guru)
                        <div class="group relative">
                            <div class="aspect-[3/4] rounded-2xl overflow-hidden bg-gray-100 shadow-lg">
                                @if($guru->foto)
                                    <img src="{{ asset('storage/' . $guru->foto) }}" alt="{{ $guru->nama }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                                @else
                                    <div class="w-full h-full flex items-center justify-center bg-gray-200 text-gray-400">
                                        <i class="fas fa-user text-6xl"></i>
                                    </div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition duration-300 flex flex-col justify-end p-6">
                                    <h3 class="text-white font-bold text-lg">{{ $guru->nama }}</h3>
                                    <p class="text-gold-400 text-sm font-medium">{{ $guru->jabatan }}</p>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center text-gray-400 py-10">
                    <p>Belum ada data guru yang ditampilkan.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- LOKASI / MAPS --}}
    <section class="py-24 bg-white">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-serif font-bold text-gray-900">Lokasi Kami</h2>
                <p class="text-gray-500 mt-2">Kunjungi pondok kami di alamat berikut</p>
            </div>
            
            <div class="rounded-3xl overflow-hidden shadow-2xl h-[400px] relative bg-gray-100">
                @if($sekolah->maps_embed_url)
                    <iframe src="{{ $sekolah->maps_embed_url }}" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                @else
                    <div class="flex flex-col items-center justify-center h-full text-gray-400">
                        <i class="fas fa-map-marked-alt text-6xl mb-4"></i>
                        <p>Peta lokasi belum diatur.</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- FOOTER --}}
    <footer id="kontak" class="bg-gray-900 text-white pt-20 pb-10">
        <div class="container mx-auto px-6">
            <div class="grid md:grid-cols-4 gap-12 mb-16">
                <div class="col-span-1 md:col-span-2">
                    <div class="flex items-center gap-3 mb-6">
                        <img src="{{ $sekolah->logo ? asset('storage/' . $sekolah->logo) : asset('gambar/logo.png') }}" alt="Logo" class="h-12 w-auto brightness-0 invert">
                        <span class="font-bold text-xl">{{ $sekolah->nama_sekolah ?? 'Al-Madinah' }}</span>
                    </div>
                    <p class="text-gray-400 leading-relaxed max-w-md mb-6">
                        Mewujudkan pendidikan Islam yang berkualitas, melahirkan generasi penghafal Al-Qur'an yang cerdas, berakhlak mulia, dan siap memimpin masa depan.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-gold-500 transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-gold-500 transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center hover:bg-gold-500 transition"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-6 text-gold-500">Tautan Cepat</h4>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#beranda" class="hover:text-white transition">Beranda</a></li>
                        <li><a href="#tentang" class="hover:text-white transition">Tentang Kami</a></li>
                        <li><a href="#program" class="hover:text-white transition">Program Pendidikan</a></li>
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Portal Akademik</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 class="font-bold text-lg mb-6 text-gold-500">Hubungi Kami</h4>
                    <ul class="space-y-4 text-gray-400">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt mt-1 text-primary-500"></i>
                            <span>{{ $sekolah->alamat ?? 'Jl. Pendidikan No. 123, Kota Santri' }}</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-phone text-primary-500"></i>
                            <span>{{ $sekolah->no_telepon ?? '+62 812 3456 7890' }}</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-primary-500"></i>
                            <span>{{ $sekolah->email ?? 'info@almadinah.sch.id' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-white/10 pt-8 text-center text-gray-500 text-sm">
                &copy; {{ date('Y') }} {{ $sekolah->nama_sekolah ?? 'Pondok Pesantren Al-Madinah' }}. All Rights Reserved.
            </div>
        </div>
    </footer>

</body>
</html>
