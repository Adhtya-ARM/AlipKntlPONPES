<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <title>@yield('title', 'Aplikasi Santri')</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style type="text/tailwindcss">
        /* Tambahkan style kustom Tailwind di sini jika diperlukan */
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.375rem; /* rounded-md */
        }
    </style>
</head>


    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">

            {{-- Area untuk pesan flash (success/error) --}}
            @if (session('success'))
                <div class="alert bg-green-100 text-green-800 border border-green-400">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert bg-red-100 text-red-800 border border-red-400">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Konten spesifik dari setiap halaman --}}
            @yield('content')
        </div>
    </main>
</body>
</html>
