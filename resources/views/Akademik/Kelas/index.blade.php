@extends('layouts.app')

@section('title', 'Manajemen Kelas')

@section('content')
<div x-data="kelasCrud()" class="container mx-auto p-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Kelas</h1>
        <button @click="openCreate()" 
            class="bg-blue-600 text-white p-3 rounded-lg shadow hover:bg-blue-700 transition"
            title="Tambah Kelas">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    {{-- TABLE --}}
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-indigo-700 text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Nama Kelas</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Wali Kelas</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Jumlah Santri</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="(item, index) in kelasList" :key="item.id">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm text-gray-600" x-text="index + 1"></td>
                        <td class="px-6 py-3 font-semibold text-gray-900" x-text="item.nama_kelas"></td>
                        <td class="px-6 py-3 text-center text-gray-700" 
                            x-text="item.wali_kelas?.nama || '-'"></td>
                        <td class="px-6 py-3 text-center font-semibold text-gray-800" 
                            x-text="item.santri_profiles_count ?? 0"></td>
                        <td class="px-6 py-3 text-center space-x-2">

                            {{-- 🔹 Tombol Edit --}}
                            <button 
                                @click="openEdit(item)" 
                                class="text-blue-600 hover:text-blue-800"
                                title="Edit Kelas">
                                <i class="fas fa-edit"></i>
                            </button>

                            {{-- 🔹 Tombol Detail --}}
                            <button 
                                @click="openDetail(item)" 
                                class="text-green-600 hover:text-green-800"
                                title="Lihat Santri">
                                <i class="fas fa-eye"></i>
                            </button>

                            {{-- 🔹 Tombol Kelola Santri --}}
                            <button @click="openSantri(item)" 
                                class="text-indigo-600 hover:text-indigo-800" title="Kelola Santri">
                                <i class="fas fa-users"></i>
                            </button>

                            {{-- 🔹 Tombol Hapus --}}
                            <button 
                                x-show="!item.is_locked"
                                @click="deleteKelas(item.id)" 
                                class="text-red-600 hover:text-red-800"
                                title="Hapus Kelas">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>

                <tr x-show="kelasList.length === 0">
                    <td colspan="5" class="text-center text-gray-400 py-6">Tidak ada data ditemukan.</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- ========== MODAL TAMBAH / EDIT ========== --}}
    <template x-if="showModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" @click.self="closeModal()">
            <div class="bg-white rounded-xl w-full max-w-md shadow-xl p-6 space-y-4">
                <h2 class="text-lg font-bold text-gray-800" x-text="modalTitle"></h2>

                <p x-show="form.is_locked" class="text-sm text-red-500">
                    ⚠️ Kelas ini sudah terhubung ke data Mapel. Hanya wali kelas yang dapat diubah.
                </p>

                <div>
                    <label class="text-sm text-gray-600">Nama Kelas</label>
                    <input type="text" 
                           x-model="form.nama_kelas" 
                           class="w-full border-gray-300 rounded-lg mt-1 p-2 bg-gray-50 disabled:cursor-not-allowed disabled:opacity-70" 
                           :disabled="form.is_locked"
                           required>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Wali Kelas</label>
                    <select x-model="form.wali_kelas_id" class="w-full border rounded p-2">
                        <option value="">-- Pilih Wali Kelas --</option>
                        @foreach ($guruProfiles as $g)
                            <option value="{{ $g->id }}">{{ $g->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-2 pt-3">
                    <button @click="closeModal()" 
                        class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">Batal</button>
                    <button @click="saveKelas()" 
                        class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
                </div>
            </div>
        </div>
    </template>

    {{-- ========== MODAL DAFTAR SANTRI (EDIT / INPUT) ========== --}}
    <template x-if="showSantriModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" @click.self="closeSantriModal()">
            <div class="bg-white rounded-xl w-full max-w-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h2 class="font-bold text-xl text-indigo-700" 
                        x-text="'Daftar Santri - ' + (santriModalTitle || '')"></h2>
                    <button @click="closeSantriModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                {{-- 🔍 Filter & Centang Semua --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="relative w-1/2">
                        <input type="text" x-model="searchSantri" placeholder="Cari nama santri..."
                            class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
                    </div>
                    <div class="text-gray-600 text-sm font-medium">
                        <input type="checkbox" @change="toggleAll($event)" class="mr-2">
                        Centang Semua
                    </div>
                </div>

                {{-- 🧑‍🎓 Tabel Santri --}}
                <div class="overflow-x-auto max-h-[60vh]">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-2 text-left text-sm font-semibold text-gray-600">No</th>
                                <th class="px-6 py-2 text-left text-sm font-semibold text-gray-600">Nama</th>
                                <th class="px-6 py-2 text-center text-sm font-semibold text-gray-600">Ikut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(s, i) in filteredSantri()" :key="s.id">
                                <tr>
                                    <td class="px-6 py-2 text-sm text-gray-500" x-text="i + 1"></td>
                                    <td class="px-6 py-2 text-gray-800" x-text="s.nama"></td>
                                    <td class="px-6 py-2 text-center">
                                        <input type="checkbox" x-model="selectedSantri" :value="s.id"
                                            class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    </td>
                                </tr>
                            </template>
                            <tr x-show="filteredSantri().length === 0">
                                <td colspan="3" class="text-center text-gray-400 py-6">Tidak ada santri ditemukan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Tombol --}}
                <div class="flex justify-end space-x-3 mt-4 border-t pt-3">
                    <button @click="closeSantriModal()" 
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300">Batal</button>
                    <button @click="saveSantri()" 
                        class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Simpan</button>
                </div>
            </div>
        </div>
    </template>

    {{-- ========== MODAL DETAIL SANTRI (READ ONLY) ========== --}}
    <template x-if="showDetailModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" @click.self="closeDetailModal()">
            <div class="bg-white rounded-xl w-full max-w-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h2 class="font-bold text-xl text-green-700" 
                        x-text="'Santri di ' + (detailModalTitle || '')"></h2>
                    <button @click="closeDetailModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="overflow-x-auto max-h-[60vh]">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-2 text-left text-sm font-semibold text-gray-600">No</th>
                                <th class="px-6 py-2 text-left text-sm font-semibold text-gray-600">Nama Santri</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <template x-for="(s, i) in detailSantri" :key="s.id">
                                <tr>
                                    <td class="px-6 py-2 text-sm text-gray-500" x-text="i + 1"></td>
                                    <td class="px-6 py-2 text-gray-800" x-text="s.nama"></td>
                                </tr>
                            </template>
                            <tr x-show="detailSantri.length === 0">
                                <td colspan="2" class="text-center text-gray-400 py-6">Belum ada santri di kelas ini.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-4 border-t pt-3">
                    <button @click="closeDetailModal()" 
                        class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300">Tutup</button>
                </div>
            </div>
        </div>
    </template>

</div>

@include('Akademik.Kelas.kelas-script')
@endsection
