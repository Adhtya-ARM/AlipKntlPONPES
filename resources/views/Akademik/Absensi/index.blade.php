@extends('layouts.app')

@section('content')
<div x-data="absensiCrud" class="p-6">
    <h1 class="text-2xl font-bold mb-5">📋 Manajemen Absensi</h1>

    <!-- Daftar Mapel -->
    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm text-left border-collapse">
            <thead class="bg-indigo-600 text-white">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Mata Pelajaran</th>
                    <th class="p-3">Kelas</th>
                    <th class="p-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="(item, index) in mapels" :key="item.id">
                    <tr class="border-b hover:bg-gray-50">
                        <td class="p-3 text-gray-700" x-text="index + 1"></td>
                        <td class="p-3" x-text="item.mapel.nama_mapel"></td>
                        <td class="p-3" x-text="item.kelas.nama_kelas"></td>
                        <td class="p-3 text-center space-x-1">
                            <button 
                                @click="openAbsensi(item)"
                                class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded-lg">
                                📋 Absensi
                            </button>
                            <button 
                                @click="openDetail(item)"
                                class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg">
                                📊 Detail
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Modal Absensi -->
    <div 
        x-show="showModal" 
        x-transition 
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
        style="display: none;"
    >
        <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl p-5 relative">
            <h2 class="text-lg font-semibold mb-3">
                Absensi <span x-text="modalTitle"></span>
            </h2>

            <!-- Info mode -->
            <template x-if="hasExistingData && !isEditing">
                <p class="text-sm text-gray-600 mb-2">
                    🔒 Data absensi sudah disimpan. Klik <b>Update</b> jika ingin mengubah.
                </p>
            </template>

            <!-- Dropdown Pertemuan -->
            <div class="flex items-center gap-3 mb-3">
                <label class="text-sm font-medium">Pertemuan ke:</label>
                <select x-model="selectedPertemuan" 
                        @change="loadPertemuan" 
                        class="border rounded-md px-3 py-1 text-sm">
                    <template x-for="n in maxPertemuan" :key="n">
                        <option :value="n" x-text="n"></option>
                    </template>
                </select>

                <label class="text-sm ml-5 font-medium">Tanggal:</label>
                <input type="date" 
                       x-model="tanggalAbsensi" 
                       class="border rounded-md px-3 py-1 text-sm">
            </div>

            <!-- Daftar Santri -->
            <div class="overflow-y-auto max-h-80 border rounded-md">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 text-left">No</th>
                            <th class="p-2 text-center">Nama Santri</th>
                            <th class="p-2 text-center">Hadir</th>
                            <th class="p-2 text-center">Izin</th>
                            <th class="p-2 text-center">Sakit</th>
                            <th class="p-2 text-center">Alpha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(s, i) in daftarSantri" :key="s.id">
                            <tr class="border-b hover:bg-gray-50">
                                <td class="p-2" x-text="i + 1"></td>
                                <td class="p-2" x-text="s.nama"></td>

                                <template x-for="type in ['hadir', 'izin', 'sakit', 'alpha']" :key="type">
                                    <td class="text-center p-2">
                                        <input type="checkbox"
                                            :disabled="readOnly && !isEditing"
                                            :checked="absensi[s.id]?.[type]"
                                            @change="absensi[s.id] = { hadir:false, izin:false, sakit:false, alpha:false }; absensi[s.id][type] = true;">
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Tombol -->
            <div class="mt-4 text-right space-x-2">
                <template x-if="!hasExistingData || isEditing">
                    <button @click="saveAbsensi"
                        class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm">
                        <span x-text="hasExistingData ? 'Update' : 'Simpan'"></span>
                    </button>
                </template>
                <template x-if="hasExistingData && !isEditing">
                    <button @click="enableEdit"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md text-sm">
                        Update
                    </button>
                </template>
                <button @click="showModal = false"
                    class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-md text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Detail Rekap -->
    <!-- MODAL DETAIL REKAP -->
    <div 
        x-show="showDetailModal" 
        x-transition 
        class="fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4"
        style="display: none;"
    >
        <div class="bg-white rounded-xl shadow-lg w-full max-w-5xl p-5 relative">
            <h2 class="text-lg font-semibold mb-3">
                Rekap Absensi <span x-text="detailTitle"></span>
            </h2>
    
            <div class="overflow-y-auto max-h-[70vh] border rounded-md">
                <table class="w-full text-sm text-center border-collapse">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-2 border text-left">No</th>
                            <th class="p-2 border text-left">Nama</th>
                            <template x-for="n in detailMaxPertemuan" :key="n">
                                <th class="p-2 border" x-text="n"></th>
                            </template>
                            <th class="p-2 border">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(s, i) in detailSantri" :key="s.id">
                            <tr>
                                <td class="p-2 border" x-text="i + 1"></td>
                                <td class="p-2 border text-left" x-text="s.nama"></td>
                                <template x-for="n in detailMaxPertemuan" :key="n">
                                    <td class="p-2 border" x-text="getStatusLetter(s.id, n)"></td>
                                </template>
                                <td class="p-2 border font-semibold" x-text="getPersen(s.id) + '%'"></td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
    
            <div class="mt-4 flex justify-between items-center">
                <p class="text-sm text-gray-600">Keterangan: H = Hadir, I = Izin, S = Sakit, A = Alpha</p>
                <div class="space-x-2">
                    <a :href="`/akademik/absensi/${currentMapelId}/export-excel`" 
                       target="_blank"
                       class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md text-sm">
                       📗 Excel
                    </a>
                    <a :href="`/akademik/absensi/${currentMapelId}/export-pdf`" 
                       target="_blank"
                       class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md text-sm">
                       📄 PDF
                    </a>
                    <button @click="showDetailModal = false"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

@include('Akademik.Absensi.absensi-script')
@endsection
