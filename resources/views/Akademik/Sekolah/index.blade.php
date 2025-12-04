@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-2xl font-bold mb-6 text-gray-800 border-b pb-2">Pengaturan Profil Sekolah</h2>

        <form action="{{ route('manajemen-sekolah.sekolah.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kolom Kiri -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Sekolah</label>
                        <input type="text" name="nama_sekolah" value="{{ old('nama_sekolah', $sekolah->nama_sekolah) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Logo Sekolah</label>
                        @if($sekolah->logo)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $sekolah->logo) }}" alt="Logo" class="h-24 w-24 object-contain border rounded p-1">
                            </div>
                        @endif
                        <input type="file" name="logo" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                        <textarea name="alamat" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition">{{ old('alamat', $sekolah->alamat) }}</textarea>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $sekolah->email) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                        <input type="text" name="telepon" value="{{ old('telepon', $sekolah->telepon) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Website</label>
                        <input type="url" name="website" value="{{ old('website', $sekolah->website) }}" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Google Maps Embed URL</label>
                        <input type="text" name="maps_embed_url" value="{{ old('maps_embed_url', $sekolah->maps_embed_url) }}" placeholder="<iframe src=...>" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                        <p class="text-xs text-gray-500 mt-1">Paste kode embed dari Google Maps di sini.</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Visi</label>
                    <textarea name="visi" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition">{{ old('visi', $sekolah->visi) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Misi</label>
                    <textarea name="misi" rows="4" class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-200 transition">{{ old('misi', $sekolah->misi) }}</textarea>
                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition transform active:scale-95">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
