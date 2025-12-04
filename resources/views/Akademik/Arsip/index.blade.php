@extends('layouts.app')

@section('title', 'Arsip Kelas')

@section('content')
<div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="fas fa-archive text-white text-3xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Arsip Kelas</h2>
                    <p class="text-gray-600 mt-1">Data arsip kelas (sistem lama)</p>
                </div>
            </div>
            <div class="bg-blue-50 border-l-4 border-blue-400 p-4 rounded-lg">
                <div class="flex">
                    <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                    <div class="ml-3">
                        <p class="text-sm text-blue-700">
                            <strong>Catatan:</strong> Ini adalah sistem arsip kelas lama. Untuk melihat riwayat pembelajaran Anda, 
                            silakan gunakan menu <strong>"Riwayat Pembelajaran"</strong> di sidebar.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Content --}}
        <div class="bg-white rounded-2xl shadow-sm p-16 text-center">
            <div class="w-24 h-24 bg-indigo-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-archive text-indigo-500 text-4xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-800 mb-2">Arsip Tidak Tersedia</h3>
            <p class="text-gray-600 max-w-md mx-auto mb-6">
                Fitur arsip kelas (snapshot) belum diimplementasikan. Saat ini sistem menggunakan arsip semester berbasis status.
            </p>
            <a href="{{ route('akademik.guru.arsip.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                <i class="fas fa-history"></i>
                <span>Lihat Riwayat Pembelajaran</span>
            </a>
        </div>
    </div>
</div>
@endsection
