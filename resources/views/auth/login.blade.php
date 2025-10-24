@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-[#FDFDFD] flex items-center justify-center p-4">

    <div class="w-full max-w-5xl bg-white shadow-2xl rounded-xl overflow-hidden grid lg:grid-cols-[40%_60%]">

        <div class="relative w-full h-96 lg:h-auto hidden lg:block"
             style="background-image: url('{{ asset('gambar/bg.png') }}'); background-size: cover; background-position: center;">

            <div class="absolute inset-0 bg-black bg-opacity-40"></div>

            <div class="absolute bottom-10 left-10 text-white text-left">
                <div class="p-2">
                    <h1 class="text-3xl font-serif font-bold tracking-wider mb-1">Pondok Pesantren Al-Madinan</h1>
                    <p class="text-lg font-light">Pusat Ilmu & Taqwa</p>
                </div>
            </div>

        </div>

        <div class="p-8 md:p-14 flex flex-col justify-center relative">

            <div class="absolute inset-0 opacity-10"
                 style="background-image: url('{{ asset('gambar/bg.png') }}'); background-repeat: repeat; background-size: 300px;">
            </div>

            <div class="z-10">
                <div class="text-center mb-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 mx-auto text-gray-700 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20V5H6.5A2.5 2.5 0 0 0 4 7.5v12z"/>
                        <path d="M20 5v14"/>
                    </svg>
                    <h2 class="text-3xl font-serif font-semibold text-gray-800">Masuk ke Akun Anda</h2>
                </div>

                {{-- Bagian Error Handling --}}
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md mb-6 text-sm" role="alert">
                        <p>Login Gagal. Silakan cek Username dan Kata Sandi Anda.</p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.process') }}">
                    @csrf

                    <div class="mb-5">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus
                                   class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-full focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-150 text-base placeholder-gray-500"
                                   placeholder="Nama Pengguna">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                            </div>
                            <input id="password" type="password" name="password" required
                                   class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-full focus:ring-1 focus:ring-blue-500 focus:border-blue-500 transition duration-150 text-base placeholder-gray-500"
                                   placeholder="Kata Sandi">
                        </div>
                    </div>

                    <div class="flex justify-start items-center mb-8 text-sm pl-1">
                        <input type="checkbox" name="remember" id="remember" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="remember" class="ml-2 text-gray-600 select-none">Ingat Saya - Lupa Sandi?</label>
                    </div>

                    <button type="submit"
                            class="w-full py-3 bg-blue-800 text-white font-semibold rounded-full shadow-lg hover:bg-blue-900 transition duration-200 uppercase tracking-wider text-lg">
                        MASUK
                    </button>
                </form>

                <div class="mt-8 text-center text-gray-600 text-sm">
                    Belum punya akun?
                    <a href="#" class="text-blue-800 font-medium hover:text-blue-900 transition duration-200">
                        Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
