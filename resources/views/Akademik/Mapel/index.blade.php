@extends('layouts.app')

@section('title', 'Manajemen Mata Pelajaran Ajar Saya')

@section('content')
<div x-data="mapelCrud()" class="container mx-auto p-6">

    {{-- HEADER --}}
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Manajemen Mata Pelajaran Ajar Saya</h1>
        <button @click="openCreate()" 
            class="bg-blue-600 text-white p-3 rounded-lg shadow hover:bg-blue-700 transition"
            title="Tambah Mapel">
            <i class="fas fa-plus"></i>
        </button>
    </div>

    {{-- SEARCH + FILTER --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
        <div class="relative w-full sm:w-1/2">
            <input type="text" x-model="search" placeholder="Cari nama mapel..." 
                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
        </div>

        <select x-model="filterKelas" 
            class="border border-gray-300 py-2 px-3 rounded-lg focus:ring-indigo-500 focus:border-indigo-500">
            <option value="">Semua Kelas</option>
            @foreach ($kelas as $k)
                <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
            @endforeach
        </select>
    </div>

    {{-- TABLE --}}
    <div class="bg-white shadow rounded-xl overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-indigo-700 text-white">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Mapel</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Kelas</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Jumlah Siswa</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Pertemuan</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Bab</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <template x-for="(item, index) in filteredData()" :key="item.id">
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-3 text-sm text-gray-600" x-text="index + 1"></td>
                        <td class="px-6 py-3 font-semibold text-gray-900" x-text="item.mapel.nama_mapel"></td>
                        <td class="px-6 py-3 text-indigo-600 text-sm" x-text="item.kelas.nama_kelas"></td>
                        <td class="px-6 py-3 text-center font-semibold text-gray-800" x-text="item.santri_mapel_count ?? 0"></td>
                        <td class="px-6 py-3 text-center text-gray-700" x-text="item.rencana_pembelajaran?.jumlah_pertemuan ?? 0"></td>
                        <td class="px-6 py-3 text-center font-bold text-gray-800" x-text="item.rencana_pembelajaran?.jumlah_bab ?? 0"></td>
                        <td class="px-6 py-3 text-center space-x-2">

                            <button @click="openRencana(item)" 
                                class="text-green-600 hover:text-green-800" title="Rencana Pembelajaran">
                                <i class="fas fa-cog"></i>
                            </button>

                            <button @click="openEdit(item)" 
                                class="text-blue-600 hover:text-blue-800" title="Edit Mapel">
                                <i class="fas fa-edit"></i>
                            </button>

                            <button @click="deleteMapel(item.mapel.id)" 
                                class="text-red-600 hover:text-red-800" title="Hapus Mapel">
                                <i class="fas fa-trash"></i>
                            </button>

                            <button @click="openSiswa(item)" 
                                class="text-indigo-600 hover:text-indigo-800" title="Daftar Siswa">
                                <i class="fas fa-users"></i>
                            </button>
                        </td>
                    </tr>
                </template>

                <tr x-show="filteredData().length === 0">
                    <td colspan="7" class="text-center text-gray-400 py-6">Tidak ada data ditemukan.</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- MODAL TAMBAH/EDIT --}}
    <template x-if="showModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" @click.self="closeModal()">
            <div class="bg-white rounded-xl w-full max-w-md shadow-xl p-6 space-y-4">
                <h2 class="text-lg font-bold text-gray-800" x-text="modalTitle"></h2>

                <div>
                    <label class="text-sm text-gray-600">Nama Mapel</label>
                    <input type="text" x-model="form.nama_mapel" class="w-full border-gray-300 rounded-lg mt-1 p-2" required>
                </div>
                <div>
                    <label class="text-sm text-gray-600">Kelas</label>
                    <select x-model="form.kelas_id" class="w-full border-gray-300 rounded-lg mt-1 p-2" required>
                        <option value="">Pilih Kelas</option>
                        @foreach ($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm text-gray-600">Tahun Ajaran</label>
                        <input type="text" x-model="form.tahun_ajaran" class="w-full border-gray-300 rounded-lg mt-1 p-2">
                    </div>
                    <div>
                        <label class="text-sm text-gray-600">Semester</label>
                        <select x-model="form.semester" class="w-full border-gray-300 rounded-lg mt-1 p-2">
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 pt-3">
                    <button @click="closeModal()" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">Batal</button>
                    <button @click="saveMapel()" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">Simpan</button>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL RENCANA --}}
    <template x-if="showRencanaModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" @click.self="closeRencanaModal()">
            <div class="bg-white rounded-xl w-full max-w-md shadow-xl p-6 space-y-4">
                <div class="flex justify-between items-center mb-3 border-b pb-2">
                    <h2 class="text-lg font-bold text-green-700" x-text="'Rencana - ' + rencanaTitle"></h2>
                    <button @click="closeRencanaModal()" class="text-gray-500 hover:text-gray-700"><i class="fas fa-times"></i></button>
                </div>

                <div>
                    <label class="text-sm text-gray-600">Jumlah Pertemuan</label>
                    <input type="number" x-model="rencana.jumlah_pertemuan" min="0" class="w-full border-gray-300 rounded-lg mt-1 p-2">
                </div>

                <div>
                    <label class="text-sm text-gray-600">Jumlah Bab</label>
                    <input type="number" x-model="rencana.jumlah_bab" min="0" class="w-full border-gray-300 rounded-lg mt-1 p-2">
                </div>

                <div class="flex justify-end space-x-3 pt-3">
                    <button @click="closeRencanaModal()" class="px-4 py-2 rounded-lg bg-gray-200 text-gray-700 hover:bg-gray-300">Batal</button>
                    <button @click="saveRencana()" class="px-4 py-2 rounded-lg bg-green-600 text-white hover:bg-green-700">Simpan</button>
                </div>
            </div>
        </div>
    </template>

    {{-- MODAL DAFTAR SISWA --}}
    <template x-if="showSiswaModal">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40" @click.self="closeSiswaModal()">
            <div class="bg-white rounded-xl w-full max-w-2xl shadow-xl p-6">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h2 class="font-bold text-xl text-indigo-700" x-text="'Daftar Siswa - ' + (siswaModalTitle || '')"></h2>
                    <button @click="closeSiswaModal()" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <div class="flex items-center justify-between mb-3">
                    <div class="text-gray-600 text-sm font-medium">
                        <input type="checkbox" @change="toggleAll($event)" class="mr-2"> Centang Semua
                    </div>
                    <div class="text-xs text-gray-500" x-text="'Total: ' + daftarSiswa.length + ' siswa'"></div>
                </div>

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
                            <template x-for="(s, i) in daftarSiswa" :key="s.id">
                                <tr>
                                    <td class="px-6 py-2 text-sm text-gray-500" x-text="i + 1"></td>
                                    <td class="px-6 py-2 text-gray-800" x-text="s.nama"></td>
                                    <td class="px-6 py-2 text-center">
                                        <input type="checkbox" x-model="selectedSantri" :value="s.id" class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end space-x-3 mt-4 border-t pt-3">
                    <button @click="closeSiswaModal()" class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300">Batal</button>
                    <button @click="saveSiswa()" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700">Simpan</button>
                </div>
            </div>
        </div>
    </template>

</div>

@include('Akademik.Mapel.mapel-script')
@endsection
