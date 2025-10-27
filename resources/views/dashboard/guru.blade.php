@extends('layouts.app') 

@section('title', 'Dashboard Guru')

@section('content')
    <div class="grid grid-cols-12 gap-6">

        <div class="col-span-12">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">

                {{-- Card 1: Total Santri --}}
                <div class="bg-white p-4 rounded-lg card-shadow flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3M6 18c-1.105 0-2-.895-2-2s.895-2 2-2 2 .895 2 2-.895 2-2 2zM6 14a2 2 0 100-4 2 2 0 000 4zm7 0h3"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Santri</p>
                        <p class="text-xl font-semibold text-gray-900">1</p>
                    </div>
                </div>

                {{-- Card 2: Mata Pelajaran --}}
                <div class="bg-white p-4 rounded-lg card-shadow flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.5v11M17.5 12h-11M3 7v10a2 2 0 002 2h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2z"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Mata Pelajaran</p>
                        <p class="text-xl font-semibold text-gray-900">1</p>
                    </div>
                </div>

                {{-- Card 3: Total Penilaian --}}
                <div class="bg-white p-4 rounded-lg card-shadow flex items-center space-x-3">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Total Penilaian</p>
                        <p class="text-xl font-semibold text-gray-900">1</p>
                    </div>
                </div>

                {{-- Card 4: Aktivitas Hari Ini --}}
                <div class="bg-white p-4 rounded-lg card-shadow flex items-center space-x-3">
                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-gray-500 text-sm">Aktivitas Hari Ini</p>
                        <p class="text-xl font-semibold text-gray-900">0</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-span-12 lg:col-span-8 space-y-6">
            
            <div class="bg-white p-5 rounded-lg card-shadow">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Aktivitas Terbaru</h2>
                
                {{-- Activity Item --}}
                <div class="flex justify-between items-center p-3 border border-gray-200 rounded-lg">
                    <div class="flex items-start space-x-3">
                        <div class="w-8 h-8 mt-1 bg-blue-50 rounded-full flex items-center justify-center text-blue-500 flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2h14a2 2 0 002-2v-3"></path></svg>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">Penilaian untuk **aaaaaaa**</p>
                            <p class="text-xs text-gray-500">Mata Pelajaran | Tanggal: 26 Oct 2025</p>
                        </div>
                    </div>
                    <div class="flex-shrink-0">
                        <span class="inline-block py-1 px-3 text-sm font-semibold text-green-700 bg-green-100 rounded-full">
                            Nilai: N/A
                        </span>
                    </div>
                </div>
                {{-- End Activity Item --}}
            </div>
        </div>

        <div class="col-span-12 lg:col-span-4 space-y-6">

            <div class="bg-white p-5 rounded-lg card-shadow">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Aksi Cepat</h2>
                
                <div class="space-y-3">
                    <a href="#" class="w-full flex items-center justify-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Input Penilaian
                    </a>
                    
                    <a href="#" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 8h-4"></path></svg>
                        Kelola Santri
                    </a>

                    <a href="#" class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9.247a1 1 0 011.002-.591l3.5.7a1 1 0 01.77.77l.7 3.5a1 1 0 01-.591 1.002c-.328.093-.67.14-.984.14-.313 0-.61-.036-.888-.104L8.228 9.247zM16 12a4 4 0 10-8 0 4 4 0 008 0z"></path></svg>
                        Bantuan
                    </a>
                </div>
            </div>

            <div class="bg-white p-5 rounded-lg card-shadow">
                <h2 class="text-lg font-semibold mb-4 text-gray-800">Mata Pelajaran</h2>
                
                {{-- Subject Item --}}
                <div class="flex justify-between items-center p-3 border border-gray-200 rounded-lg">
                    <p class="text-sm font-medium text-gray-800">TIK</p>
                    <span class="inline-block py-1 px-3 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">
                        Aktif
                    </span>
                </div>
                {{-- End Subject Item --}}
            </div>

        </div>

    </div>

@endsection