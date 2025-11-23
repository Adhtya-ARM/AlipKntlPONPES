@extends('layouts.app')

@section('title', 'Login - Al-Madinah')

@section('content')
<div class="bg-gray-50 min-h-screen flex items-start pt-16 justify-center" 
     style="background-image: url('{{ asset('gambar/bg.png') }}'); background-size: cover; background-position: center;">

    <div class="p-4 sm:p-6 md:p-8 w-full max-w-md">
        <div class="p-8 sm:p-10 rounded-xl shadow-2xl border border-white/30 backdrop-blur-xl bg-white/60">
            
            <div class="text-center mb-8">
                <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">
                    Al-Madinah
                </h1>
                <p class="mt-1 text-sm text-gray-700">
                    Sistem Informasi Pondok Pesantren
                </p>
            </div>

            {{-- === FORM LOGIN (tampil ketika tidak sedang menampilkan role picker) === --}}
            @if(empty($showRolePicker))
            <form method="POST" action="{{ route('login') }}" class="space-y-6" autocomplete="off">
                @csrf 
                
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-900">
                        Nama Pengguna / NIS
                    </label>
                    <div class="mt-1">
                        <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus
                            class="appearance-none block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('username') border-red-500 @enderror bg-white/90 transition duration-150 ease-in-out"
                            placeholder="Masukkan Nama Pengguna atau NIS" autocomplete="username">
                        
                        @error('username')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-900">
                        Kata Sandi
                    </label>
                    <div class="mt-1">
                        <input id="password" name="password" type="password" required
                            class="appearance-none block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm @error('password') border-red-500 @enderror bg-white/90 transition duration-150 ease-in-out"
                            placeholder="Masukkan Kata Sandi" autocomplete="current-password">

                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" class="h-4 w-4 text-green-600 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                            Ingat Saya
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-green-700 hover:text-green-500 transition duration-150 ease-in-out">
                            Lupa Kata Sandi?
                        </a>
                    </div>
                    @endif
                </div>

                <div>
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition duration-150 ease-in-out">
                        Masuk
                    </button>
                </div>
            </form>
            @endif

            {{-- === ROLE PICKER (submit ke POST /login untuk single-endpoint flow) === --}}
            @if(!empty($showRolePicker) && isset($subRoles) && count($subRoles))
                <div class="mt-6 p-4 bg-white/80 rounded-lg border border-gray-200 shadow-sm">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 text-center">Pilih Sub-Role Anda</h3>

                    @php
                        $userName = optional(Auth::user())->name ?? session('auth_user_name') ?? null;
                    @endphp

                    @if($userName)
                        <p class="text-center text-xs text-gray-600 mb-4">Masuk sebagai <span class="font-medium">{{ $userName }}</span></p>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-3">
                        @csrf
                        @foreach($subRoles as $role)
                            <button type="submit" name="role" value="{{ $role }}"
                                class="w-full py-2 px-4 text-sm font-medium rounded-lg shadow-sm text-white
                                @if($role === 'mts') bg-blue-600 hover:bg-blue-700 @else bg-green-600 hover:bg-green-700 @endif
                                focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                                Masuk sebagai {{ strtoupper($role) }}
                            </button>
                        @endforeach

                        @if(count($subRoles) > 1)
                            <button type="submit" name="role" value="both"
                                class="w-full py-2 px-4 text-sm font-medium rounded-lg border border-gray-300 text-gray-800 bg-white hover:bg-gray-100 transition">
                                Gabungan (MTS &amp; MA)
                            </button>
                        @endif
                    </form>

                    <div class="mt-4 text-center">
                        <form method="POST" action="{{ route('logout', ['guard' => session('auth_guard', 'guru')]) }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-700 hover:text-red-500 transition">
                                Batal / Kembali ke Login
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <p class="mt-8 text-center text-xs text-gray-700">
                &copy; {{ date('Y') }} Ponpes Al-Madinah.
            </p>
        </div>
    </div>
</div>
@endsection
