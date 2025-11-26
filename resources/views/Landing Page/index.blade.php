<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pondok Pesantren Al-Madinah - Membangun Generasi Rabbani</title>
    <link rel="icon" href="{{ asset('gambar/logo.png') }}" type="image/png">
    <meta name="description" content="Pondok Pesantren Modern dengan kurikulum terpadu, mencetak generasi hafidz quran yang berwawasan global.">
    
    {{-- Tailwind CSS & Alpine.js --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, h4 { font-family: 'Playfair Display', serif; }
        .hero-pattern {
            background-color: #111827;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231f2937' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
    </style>
</head>
<body class="antialiased text-gray-800 bg-white">

    {{-- Navbar --}}
    <nav x-data="{ open: false, scrolled: false }" 
         @scroll.window="scrolled = (window.pageYOffset > 20)"
         :class="scrolled ? 'bg-white/90 backdrop-blur-md shadow-sm py-2' : 'bg-transparent py-4'"
         class="fixed w-full z-50 transition-all duration-300">
        <div class="container mx-auto px-6 flex justify-between items-center">
            <a href="#" class="flex items-center gap-2 group">
                <div class="w-10 h-10 bg-green-600 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-lg group-hover:bg-green-700 transition">
                    <i class="fas fa-quran"></i>
                </div>
                <span :class="scrolled ? 'text-gray-800' : 'text-white'" class="text-xl font-bold tracking-wide transition-colors">Al-Madinah</span>
            </a>

            {{-- Desktop Menu --}}
            <div class="hidden md:flex items-center gap-8">
                <a href="#home" :class="scrolled ? 'text-gray-600 hover:text-green-600' : 'text-gray-200 hover:text-white'" class="text-sm font-medium transition">Beranda</a>
                <a href="#about" :class="scrolled ? 'text-gray-600 hover:text-green-600' : 'text-gray-200 hover:text-white'" class="text-sm font-medium transition">Tentang Kami</a>
                <a href="#program" :class="scrolled ? 'text-gray-600 hover:text-green-600' : 'text-gray-200 hover:text-white'" class="text-sm font-medium transition">Program</a>
                <a href="#fasilitas" :class="scrolled ? 'text-gray-600 hover:text-green-600' : 'text-gray-200 hover:text-white'" class="text-sm font-medium transition">Fasilitas</a>
                <a href="{{ route('login') }}" class="px-5 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-semibold rounded-full transition shadow-lg hover:shadow-green-500/30 transform hover:-translate-y-0.5">
                    Login Portal
                </a>
            </div>

            {{-- Mobile Menu Button --}}
            <button @click="open = !open" class="md:hidden focus:outline-none">
                <i :class="scrolled ? 'text-gray-800' : 'text-white'" class="fas fa-bars text-2xl"></i>
            </button>
        </div>

        {{-- Mobile Menu --}}
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden absolute top-full left-0 w-full bg-white shadow-lg border-t border-gray-100 py-4 px-6 flex flex-col gap-4">
            <a href="#home" class="text-gray-600 hover:text-green-600 font-medium">Beranda</a>
            <a href="#about" class="text-gray-600 hover:text-green-600 font-medium">Tentang Kami</a>
            <a href="#program" class="text-gray-600 hover:text-green-600 font-medium">Program</a>
            <a href="#fasilitas" class="text-gray-600 hover:text-green-600 font-medium">Fasilitas</a>
            <a href="{{ route('login') }}" class="text-center px-5 py-3 bg-green-600 text-white font-semibold rounded-lg">Login Portal</a>
        </div>
    </nav>

    {{-- Hero Section --}}
    <section id="home" class="relative pt-32 pb-20 lg:pt-48 lg:pb-32 overflow-hidden hero-pattern">
        <div class="absolute inset-0 bg-gradient-to-b from-black/60 via-black/40 to-gray-900/90"></div>
        
        {{-- Background Image (Optional, using pattern for now but can be replaced) --}}
        {{-- <img src="https://images.unsplash.com/photo-1564121211835-e88c852648ab?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80" alt="Background" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-50"> --}}

        <div class="container mx-auto px-6 relative z-10 text-center">
            <span class="inline-block py-1 px-3 rounded-full bg-green-500/20 text-green-300 text-sm font-semibold mb-6 border border-green-500/30 backdrop-blur-sm">
                Penerimaan Santri Baru Tahun Ajaran 2025/2026 Dibuka
            </span>
            <h1 class="text-4xl md:text-6xl lg:text-7xl font-bold text-white mb-6 leading-tight">
                Membangun Generasi <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-600">Hafidz & Berakhlak</span>
            </h1>
            <p class="text-gray-300 text-lg md:text-xl max-w-2xl mx-auto mb-10 leading-relaxed">
                Pondok Pesantren Al-Madinah memadukan kurikulum salaf dan modern untuk mencetak kader ulama yang intelek dan pemimpin masa depan.
            </p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="#program" class="px-8 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-full transition shadow-lg hover:shadow-green-500/40 transform hover:-translate-y-1 flex items-center justify-center gap-2">
                    Lihat Program <i class="fas fa-arrow-right"></i>
                </a>
                <a href="#about" class="px-8 py-4 bg-white/10 hover:bg-white/20 text-white font-semibold rounded-full backdrop-blur-sm border border-white/20 transition flex items-center justify-center gap-2">
                    <i class="fas fa-play-circle"></i> Profil Pondok
                </a>
            </div>
        </div>

        {{-- Stats --}}
        <div class="container mx-auto px-6 mt-20">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 border-t border-white/10 pt-12">
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white mb-2">1500+</div>
                    <div class="text-gray-400 text-sm">Santri Aktif</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white mb-2">120+</div>
                    <div class="text-gray-400 text-sm">Guru & Staff</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white mb-2">50+</div>
                    <div class="text-gray-400 text-sm">Ekstrakurikuler</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl md:text-4xl font-bold text-white mb-2">25th</div>
                    <div class="text-gray-400 text-sm">Pengabdian</div>
                </div>
            </div>
        </div>
    </section>

    {{-- About Section --}}
    <section id="about" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex flex-col lg:flex-row items-center gap-12">
                <div class="lg:w-1/2 relative">
                    <div class="absolute -top-4 -left-4 w-24 h-24 bg-green-100 rounded-full -z-10"></div>
                    <div class="absolute -bottom-4 -right-4 w-32 h-32 bg-yellow-100 rounded-full -z-10"></div>
                    <img src="https://images.unsplash.com/photo-1584551246679-0daf3d275d0f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1000&q=80" alt="Kegiatan Santri" class="rounded-2xl shadow-2xl w-full object-cover h-[500px]">
                    <div class="absolute bottom-8 left-8 bg-white p-6 rounded-xl shadow-lg max-w-xs hidden md:block">
                        <div class="flex items-center gap-4 mb-2">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600">
                                <i class="fas fa-quote-right"></i>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">KH. Abdullah</div>
                                <div class="text-xs text-gray-500">Pengasuh Pondok</div>
                            </div>
                        </div>
                        <p class="text-sm text-gray-600 italic">"Pendidikan adalah cahaya yang menerangi jalan masa depan."</p>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <h4 class="text-green-600 font-semibold mb-2 uppercase tracking-wider">Tentang Kami</h4>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-6">Mewujudkan Pendidikan Islam yang Holistik dan Berkemajuan</h2>
                    <p class="text-gray-600 mb-6 leading-relaxed">
                        Berdiri sejak tahun 1998, Pondok Pesantren Al-Madinah telah berkomitmen untuk menyediakan pendidikan berkualitas yang menyeimbangkan ilmu agama dan ilmu umum. Kami percaya bahwa setiap santri memiliki potensi unik yang harus dikembangkan.
                    </p>
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">Kurikulum Terpadu (Kemenag & Kemdikbud)</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">Program Tahfidz Al-Qur'an Intensif</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="fas fa-check-circle text-green-500 mt-1"></i>
                            <span class="text-gray-700">Pembinaan Bahasa Arab & Inggris Aktif</span>
                        </li>
                    </ul>
                    <a href="#" class="text-green-600 font-semibold hover:text-green-700 flex items-center gap-2 group">
                        Selengkapnya tentang kami <i class="fas fa-arrow-right transform group-hover:translate-x-1 transition"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Struktur Organisasi Section --}}
    <section id="struktur" class="py-20 bg-gradient-to-b from-gray-50 to-white">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h4 class="text-green-600 font-semibold mb-2 uppercase tracking-wider">Struktur Organisasi</h4>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Pimpinan & Pengajar</h2>
                <p class="text-gray-600">Mengenal lebih dekat para pemimpin dan pengajar yang berdedikasi untuk pendidikan berkualitas.</p>
            </div>

            @if($struktur->isEmpty())
                <div class="text-center text-gray-500 py-16 bg-white rounded-2xl shadow-sm">
                    <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                    <p class="text-lg">Data struktur organisasi belum tersedia.</p>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6">
                    @foreach($struktur as $guru)
                    <div class="group">
                        <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 overflow-hidden transform hover:-translate-y-2">
                            <!-- Photo Container -->
                            <div class="aspect-square overflow-hidden bg-gradient-to-br from-green-50 to-emerald-50">
                                @if($guru->foto)
                                    <img src="{{ asset('storage/' . $guru->foto) }}" 
                                         alt="{{ $guru->nama }}" 
                                         class="w-full h-full object-cover group-hover:scale-110 transition duration-500">
                                @else
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($guru->nama) }}&background=10b981&color=fff&bold=true&size=300" 
                                         alt="{{ $guru->nama }}" 
                                         class="w-full h-full object-cover">
                                @endif
                            </div>
                            
                            <!-- Info -->
                            <div class="p-4 text-center">
                                <h3 class="font-bold text-gray-900 text-sm mb-1 line-clamp-2 leading-tight">{{ $guru->nama }}</h3>
                                <p class="text-green-600 text-xs font-medium line-clamp-1">{{ $guru->jabatan }}</p>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Additional Info -->
                <div class="mt-12 text-center">
                    <p class="text-sm text-gray-500">
                        Didukung oleh <strong>{{ $struktur->count() }}</strong> tenaga pendidik dan kependidikan yang berpengalaman
                    </p>
                </div>
            @endif
        </div>
    </section>

    {{-- Programs Section --}}
    <section id="program" class="py-20 bg-gray-50">
        <div class="container mx-auto px-6">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <h4 class="text-green-600 font-semibold mb-2 uppercase tracking-wider">Program Unggulan</h4>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Jenjang Pendidikan</h2>
                <p class="text-gray-600">Kami menyediakan berbagai jenjang pendidikan formal dan non-formal untuk memenuhi kebutuhan umat.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- Card 1 --}}
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition duration-300 border border-gray-100 group">
                    <div class="w-14 h-14 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600 text-2xl mb-6 group-hover:scale-110 transition">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Madrasah Tsanawiyah (MTs)</h3>
                    <p class="text-gray-600 mb-6 text-sm leading-relaxed">Pendidikan tingkat menengah pertama dengan fokus pada pembentukan karakter dasar dan penguasaan dasar-dasar ilmu agama.</p>
                    <a href="#" class="inline-flex items-center text-blue-600 font-semibold text-sm hover:text-blue-700">
                        Lihat Kurikulum <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </a>
                </div>

                {{-- Card 2 --}}
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition duration-300 border border-gray-100 group relative overflow-hidden">
                    <div class="absolute top-0 right-0 bg-green-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg">Favorit</div>
                    <div class="w-14 h-14 bg-green-100 rounded-xl flex items-center justify-center text-green-600 text-2xl mb-6 group-hover:scale-110 transition">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Madrasah Aliyah (MA)</h3>
                    <p class="text-gray-600 mb-6 text-sm leading-relaxed">Pendidikan tingkat menengah atas dengan jurusan IPA, IPS, dan Keagamaan. Persiapan matang menuju perguruan tinggi.</p>
                    <a href="#" class="inline-flex items-center text-green-600 font-semibold text-sm hover:text-green-700">
                        Lihat Kurikulum <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </a>
                </div>

                {{-- Card 3 --}}
                <div class="bg-white rounded-2xl p-8 shadow-sm hover:shadow-xl transition duration-300 border border-gray-100 group">
                    <div class="w-14 h-14 bg-yellow-100 rounded-xl flex items-center justify-center text-yellow-600 text-2xl mb-6 group-hover:scale-110 transition">
                        <i class="fas fa-mosque"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Tahfidz Al-Qur'an</h3>
                    <p class="text-gray-600 mb-6 text-sm leading-relaxed">Program khusus menghafal Al-Qur'an 30 juz dengan sanad, didampingi oleh muhaffidz/ah yang berpengalaman.</p>
                    <a href="#" class="inline-flex items-center text-yellow-600 font-semibold text-sm hover:text-yellow-700">
                        Lihat Kurikulum <i class="fas fa-chevron-right ml-1 text-xs"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Fasilitas Section --}}
    <section id="fasilitas" class="py-20 bg-white">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row justify-between items-end mb-12">
                <div class="max-w-2xl">
                    <h4 class="text-green-600 font-semibold mb-2 uppercase tracking-wider">Fasilitas</h4>
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900">Lingkungan Belajar yang Nyaman</h2>
                </div>
                <a href="#" class="hidden md:inline-flex items-center gap-2 px-6 py-3 border border-gray-300 rounded-full text-gray-700 font-medium hover:bg-gray-50 transition">
                    Lihat Semua Galeri <i class="fas fa-arrow-right"></i>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="group relative overflow-hidden rounded-2xl h-64 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1595846519845-68e298c2edd8?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Masjid" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-90"></div>
                    <div class="absolute bottom-0 left-0 p-6">
                        <h3 class="text-white text-xl font-bold">Masjid Jami'</h3>
                        <p class="text-gray-300 text-sm mt-1">Pusat ibadah dan kajian kitab kuning</p>
                    </div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl h-64 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1588072432836-e10032774350?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Kelas" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-90"></div>
                    <div class="absolute bottom-0 left-0 p-6">
                        <h3 class="text-white text-xl font-bold">Ruang Kelas Multimedia</h3>
                        <p class="text-gray-300 text-sm mt-1">Dilengkapi proyektor dan AC</p>
                    </div>
                </div>
                <div class="group relative overflow-hidden rounded-2xl h-64 cursor-pointer">
                    <img src="https://images.unsplash.com/photo-1571260899304-425eee4c7efc?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80" alt="Lab" class="w-full h-full object-cover transition duration-500 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 to-transparent opacity-90"></div>
                    <div class="absolute bottom-0 left-0 p-6">
                        <h3 class="text-white text-xl font-bold">Laboratorium Komputer</h3>
                        <p class="text-gray-300 text-sm mt-1">Sarana pengembangan skill IT santri</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Section --}}
    <section class="py-20 bg-green-600 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10 bg-[url('https://www.transparenttextures.com/patterns/arabesque.png')]"></div>
        <div class="container mx-auto px-6 relative z-10 text-center">
            <h2 class="text-3xl md:text-5xl font-bold text-white mb-6">Siap Bergabung Bersama Kami?</h2>
            <p class="text-green-100 text-lg mb-10 max-w-2xl mx-auto">Jadilah bagian dari keluarga besar Pondok Pesantren Al-Madinah dan raih masa depan gemilang dunia dan akhirat.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="#" class="px-8 py-4 bg-white text-green-700 font-bold rounded-full shadow-lg hover:bg-gray-100 transition transform hover:-translate-y-1">
                    Daftar Sekarang
                </a>
                <a href="#" class="px-8 py-4 bg-green-700 text-white font-bold rounded-full border border-green-500 hover:bg-green-800 transition">
                    Hubungi Kami
                </a>
            </div>
        </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-300 py-16 border-t border-gray-800">
        <div class="container mx-auto px-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                <div>
                    <div class="flex items-center gap-2 mb-6">
                        <div class="w-8 h-8 bg-green-600 rounded flex items-center justify-center text-white font-bold">
                            <i class="fas fa-quran text-sm"></i>
                        </div>
                        <span class="text-xl font-bold text-white">Al-Madinah</span>
                    </div>
                    <p class="text-sm leading-relaxed mb-6 text-gray-400">
                        Mewujudkan generasi Islam yang beriman, berilmu, dan beramal sholeh untuk kemajuan umat dan bangsa.
                    </p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-green-600 hover:text-white transition"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-pink-600 hover:text-white transition"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-red-600 hover:text-white transition"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-white font-bold mb-6">Tautan Cepat</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-green-500 transition">Profil Pondok</a></li>
                        <li><a href="#" class="hover:text-green-500 transition">Visi & Misi</a></li>
                        <li><a href="#" class="hover:text-green-500 transition">Struktur Organisasi</a></li>
                        <li><a href="#" class="hover:text-green-500 transition">Berita & Artikel</a></li>
                        <li><a href="#" class="hover:text-green-500 transition">Karir</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-white font-bold mb-6">Program Pendidikan</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="#" class="hover:text-green-500 transition">Madrasah Tsanawiyah</a></li>
                        <li><a href="#" class="hover:text-green-500 transition">Madrasah Aliyah</a></li>
                        <li><a href="#" class="hover:text-green-500 transition">Tahfidz Al-Qur'an</a></li>
                        <li><a href="#" class="hover:text-green-500 transition">Madrasah Diniyah</a></li>
                        <li><a href="#" class="hover:text-green-500 transition">Ekstrakurikuler</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-white font-bold mb-6">Kontak Kami</h3>
                    <ul class="space-y-4 text-sm">
                        <li class="flex items-start gap-3">
                            <i class="fas fa-map-marker-alt text-green-500 mt-1"></i>
                            <span>Jl. Pesantren No. 123, Desa Sukamaju, Kec. Cibeureum, Kota Tasikmalaya, Jawa Barat</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-phone text-green-500"></i>
                            <span>(0265) 1234567</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <i class="fas fa-envelope text-green-500"></i>
                            <span>info@almadinah.sch.id</span>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-12 pt-8 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} Pondok Pesantren Al-Madinah. All rights reserved.
            </div>
        </div>
    </footer>

</body>
</html>
