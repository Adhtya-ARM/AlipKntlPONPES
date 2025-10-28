@extends('layouts.app')

@section('title', 'Input Penilaian Santri')

@section('content')
{{-- div utama untuk Alpine.js --}}
<div x-data="penilaianApp()" x-cloak>

    {{-- Data Bab Dinamis diambil dari controller --}}
    {{-- Pastikan mapelSaatIni ada sebelum mengakses propertinya. Default ke 0 jika null. --}}
    <input type="hidden" id="mapel-jumlah-bab" value="{{ $mapelSaatIni->jumlah_bab ?? 0 }}">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-semibold text-gray-700">Input Penilaian Santri</h2>

        {{-- BLOK TOMBOL AKSI --}}
        <div class="flex space-x-3">
            {{-- Tombol Utama: Membuka Modal Daftar Santri untuk input/edit --}}
            <button @click="isSantriListModalOpen = true"
                class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-md transition duration-150">
                + Input Nilai Bab/UAS/UTS
            </button>

            {{-- Tombol Import Nilai (PDF) --}}
            <button @click="isUploadModalOpen = true"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition duration-150">
                Import Nilai (PDF)
            </button>
        </div>
    </div>

    {{-- Notifikasi Session --}}
    @if (session('success'))
    <div class="bg-green-100 text-green-800 p-4 rounded-md mb-4 border border-green-400">
        {{ session('success') }}
    </div>
    @endif
    @if (session('error'))
    <div class="bg-red-100 text-red-800 p-4 rounded-md mb-4 border border-red-400">
        {{ session('error') }}
    </div>
    @endif

    {{-- Informasi Mapel dan Kelas --}}
    {{-- Tampilkan hanya jika ada mapel yang terikat --}}
    @if ($mapelIdsTampil && $mapelSaatIni)
    <div class="bg-indigo-50 text-indigo-800 p-3 rounded-md mb-4 text-sm font-medium border-l-4 border-indigo-500">
        Anda menginput nilai untuk mata pelajaran:
        <span class="font-bold">{{ $mapelSaatIni->nama_mapel ?? 'N/A' }}</span>
        (Total Bab: <span class="font-bold" x-text="currentChapterCount"></span>)
        di kelas:
        <span class="font-bold">{{ $currentKelas }}</span>
    </div>
    @else
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md mb-4 border border-yellow-400">
        Tidak ada Mata Pelajaran yang dipilih atau terikat dengan akun Anda.
        Silakan hubungi administrator untuk penugasan mata pelajaran.
    </div>
    @endif


    {{-- Tabel Data Santri dan Nilai (Tabel Utama) --}}
    {{-- Tampilkan tabel hanya jika ada santri DAN ada mapel yang terikat --}}
    @if (!$santriProfiles->isEmpty() && $mapelIdsTampil)
    <div class="bg-white shadow overflow-x-auto rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Santri</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                    
                    {{-- Kolom Dinamis Nilai Bab (Header) --}}
                    <template x-for="i in currentChapterCount" :key="'header-bab-' + i">
                        <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" x-text="'Bab ' + i"></th>
                    </template>
                    
                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Rata-rata Bab</th>
                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">UTS</th>
                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">UAS</th>
                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider font-bold">Rata-rata Akhir</th>
                    <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider font-bold">Grade</th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($santriProfiles as $santri)
                @php
                $nilaiMapel = $penilaians[$santri->id] ?? null;

                $santriAlpineData = $santri->toArray();
                $santriAlpineData['nis'] = $santri->santri->nis ?? '-';
                $santriAlpineData['kelas_name'] = $santri->kelas ?? '-';

                if ($nilaiMapel) {
                    $santriAlpineData['penilaian_id'] = $nilaiMapel->id;
                    $santriAlpineData['nilai_bab'] = json_decode($nilaiMapel->nilai_harian_json ?? '[]', true); 
                    $santriAlpineData['nilai_uts'] = $nilaiMapel->uts ?? '';
                    $santriAlpineData['nilai_uas'] = $nilaiMapel->uas ?? '';
                } else {
                    $santriAlpineData['penilaian_id'] = null;
                    $santriAlpineData['nilai_bab'] = [];
                    $santriAlpineData['nilai_uts'] = '';
                    $santriAlpineData['nilai_uas'] = '';
                }
                @endphp

                {{-- Baris Santri (x-data di sini untuk menghitung rata-rata di client side) --}}
                <tr x-data='{ 
                    santriData: @json($santriAlpineData),
                    babScores: @json($santriAlpineData['nilai_bab']), 
                    // Fungsi Alpine.js (karena fungsi utama ada di scope induk, kita bisa langsung pakai)
                    getBabAvg() {
                        return $parent.calculateAverage(this.babScores);
                    },
                    getFinalAvg() {
                        return $parent.calculateOverallAverage(this.getBabAvg(), this.santriData.nilai_uts, this.santriData.nilai_uas);
                    },
                    getGrade() {
                        return $parent.getGrade(this.getFinalAvg());
                    }
                }'>
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        {{ $loop->iteration + ($santriProfiles->currentPage() - 1) * $santriProfiles->perPage() }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $santri->nama ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $santri->santri->nis ?? '-' }}</td>

                    {{-- Kolom Dinamis Nilai Bab (Nilai) --}}
                    <template x-for="i in $parent.currentChapterCount" :key="'bab-val-' + i">
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700 text-center">
                            {{-- Nilai Bab diakses per index (i-1) --}}
                            <span x-text="babScores[i-1] ?? '-'"></span>
                        </td>
                    </template>
                    
                    {{-- Rata-rata Bab --}}
                    <td class="px-3 py-4 whitespace-nowrap text-sm font-medium text-gray-800 text-center" x-text="getBabAvg()"></td>

                    {{-- UTS --}}
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700 text-center" x-text="santriData.nilai_uts || '-'"></td>

                    {{-- UAS --}}
                    <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-700 text-center" x-text="santriData.nilai_uas || '-'"></td>

                    {{-- Rata-rata Akhir --}}
                    <td class="px-3 py-4 whitespace-nowrap text-sm font-bold text-indigo-700 text-center" x-text="getFinalAvg()"></td>

                    {{-- Grade --}}
                    <td class="px-3 py-4 whitespace-nowrap text-sm font-bold text-center" :class="{'text-green-600': getGrade() === 'A', 'text-yellow-600': getGrade() === 'B', 'text-red-600': getGrade() === 'E'}" x-text="getGrade()"></td>


                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <div class="flex items-center space-x-2 justify-center">

                            <button @click="$parent.openDetailModal(santriData)"
                                class="text-xs font-semibold px-2 py-1 rounded-full text-blue-600 hover:bg-blue-100 transition duration-150">
                                Rincian
                            </button>

                            @if ($nilaiMapel)
                            {{-- Membuka modal yang sama untuk Edit --}}
                            <button @click="$parent.openEditTugasHarianModal(santriData)"
                                class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-600 text-white hover:bg-indigo-700 transition duration-150 shadow-md">
                                Edit Nilai
                            </button>

                            <form action="{{ route('akademik.penilaian.destroy', $nilaiMapel->id) }}" method="POST"
                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus nilai ini?')"
                                class="inline">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="kelas" value="{{ $currentKelas }}">
                                <button type="submit"
                                    class="text-xs font-semibold px-2 py-1 rounded-full text-red-600 hover:bg-red-100">
                                    Hapus
                                </button>
                            </form>
                            @else
                            {{-- Membuka modal yang sama untuk Tambah --}}
                            <button @click="$parent.openCreateTugasHarianModal(santriData)"
                                class="text-xs font-semibold px-3 py-1 rounded-full bg-green-600 text-white hover:bg-green-700 transition duration-150 shadow-md">
                                Tambah Nilai
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $santriProfiles->appends(request()->except('page'))->links() }}
    </div>
    @elseif ($mapelIdsTampil) {{-- Jika tidak ada santri tapi ada mapel yang terikat --}}
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md border border-yellow-400">
        Belum ada data santri yang tercatat untuk dinilai pada kelas **{{ $currentKelas }}** ini.
    </div>
    @endif


    {{-----------------------------------------------------------}}
    {{-- MODAL UTAMA: INPUT/EDIT/DETAIL NILAI BAB/UTS/UAS --}}
    {{-----------------------------------------------------------}}
    <div x-show="isTugasHarianModalOpen" x-cloak x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isTugasHarianModalOpen = false">
        </div>

        <div class="flex items-center justify-center min-h-screen">
            <div x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-xl shadow-2xl transform transition-all max-w-2xl w-full mx-4 my-8" @click.stop>

                <div class="p-6 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                    <h3 class="text-xl font-bold text-purple-700" x-text="modalTitle"></h3>
                    <p class="text-sm text-gray-500 mt-1" x-show="!isDetailMode">Masukkan atau perbarui nilai (0-100)
                        untuk semua komponen.</p>
                    <p class="text-sm text-gray-500 mt-1" x-show="isDetailMode">Mode Rincian: Data ini bersifat
                        *read-only*.</p>
                </div>

                {{-- FORM INPUT/EDIT --}}
                <form :action="modalActionUrl" method="POST" class="p-6" x-show="!isDetailMode">
                    @csrf
                    <input type="hidden" name="_method" :value="modalMethod" x-show="modalMethod !== 'POST'">

                    {{-- Hidden Fields Penting --}}
                    <input type="hidden" name="santri_profile_id" :value="modalData ? modalData.id : ''">
                    <input type="hidden" name="mapel_id" value="{{ $mapelIdsTampil }}">
                    <input type="hidden" name="kelas" value="{{ $currentKelas }}">
                    <input type="hidden" name="penilaian_id" :value="modalData ? modalData.penilaian_id : ''"
                        x-show="modalMethod === 'PUT'">

                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-200">
                            <div>
                                <label class="block text-xs font-medium text-gray-500">Santri</label>
                                <p class="mt-1 block w-full border-0 p-0 text-sm font-semibold text-gray-900 bg-white"
                                    x-text="modalData ? modalData.nama + ' (' + modalData.nis + ')' : ''"></p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500">Kelas</label>
                                <p class="mt-1 block w-full border-0 p-0 text-sm font-semibold text-gray-900 bg-white"
                                    x-text="modalData ? modalData.kelas_name : 'N/A'"></p>
                            </div>
                        </div>

                        {{-- Input Nilai Bab DINAMIS --}}
                        <h4 class="text-base font-semibold text-gray-700 pt-2 border-b-2 pb-1">Nilai Harian (Bab)</h4>
                        
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                                <thead class="bg-purple-50">
                                    <tr>
                                        <template x-for="i in currentChapterCount" :key="'modal-header-bab-' + i">
                                            <th class="px-4 py-2 text-center text-xs font-medium text-purple-700 uppercase" x-text="'Bab ' + i"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        {{-- Loop untuk Input Nilai Bab Dinamis --}}
                                        <template x-for="i in currentChapterCount" :key="'modal-input-bab-' + i">
                                            <td class="px-2 py-2">
                                                <input type="number" :name="'bab_' + i" min="0" max="100" placeholder="0"
                                                    :value="currentBabScores[i-1] ?? ''" 
                                                    class="w-full text-center rounded-md border-gray-300 shadow-sm focus:border-purple-500 focus:ring-purple-500 text-sm p-1.5">
                                            </td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>
                        </div>


                        {{-- Input Nilai UTS & UAS --}}
                        <h4 class="text-base font-semibold text-gray-700 pt-2 border-b-2 pb-1">Nilai Ujian</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="nilai_uts" class="block text-sm font-medium text-gray-700">Nilai UTS</label>
                                <input type="number" x-ref="nilai_uts_input" id="nilai_uts" name="nilai_uts" min="0"
                                    max="100" placeholder="Contoh: 80"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2"
                                    required>
                            </div>
                            <div>
                                <label for="nilai_uas" class="block text-sm font-medium text-gray-700">Nilai UAS</label>
                                <input type="number" x-ref="nilai_uas_input" id="nilai_uas" name="nilai_uas" min="0"
                                    max="100" placeholder="Contoh: 90"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2"
                                    required>
                            </div>
                        </div>

                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button type="button" @click="isTugasHarianModalOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition duration-150">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 shadow-md transition duration-150">
                            <span x-text="modalMethod === 'POST' ? 'Simpan Nilai' : 'Perbarui Nilai'"></span>
                        </button>
                    </div>
                </form>

                {{-- Detail View (Read-Only) --}}
                <div class="p-6" x-show="isDetailMode">
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-200">
                            <div>
                                <label class="block text-xs font-medium text-gray-500">NIS</label>
                                <p class="text-sm font-semibold text-gray-900" x-text="modalData ? modalData.nis : '-'">
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-500">Nama</label>
                                <p class="text-sm font-semibold text-gray-900"
                                    x-text="modalData ? modalData.nama : 'N/A'"></p>
                            </div>
                        </div>

                        <h4 class="text-base font-semibold text-gray-700 pt-2 border-b-2 pb-1">Rincian Nilai Bab</h4>

                        <div class="overflow-x-auto border rounded-md">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <template x-for="i in currentChapterCount" :key="'detail-header-bab-' + i">
                                            <th class="px-3 py-2 text-center text-xs font-medium text-gray-500 uppercase" x-text="'Bab ' + i"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr>
                                        <template x-for="i in currentChapterCount" :key="'detail-val-bab-' + i">
                                            <td class="px-3 py-2 text-sm font-bold text-gray-800 text-center" x-text="modalData.nilai_bab[i-1] ?? '-'"></td>
                                        </template>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <p class="text-sm text-gray-700">Rata-rata Bab: <span class="font-bold" x-text="calculateAverage(modalData.nilai_bab)"></span></p>

                        <h4 class="text-base font-semibold text-gray-700 pt-2 border-b-2 pb-1">Nilai Ujian</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nilai UTS</label>
                                <p class="text-lg font-bold text-gray-800"
                                    x-text="modalData && modalData.nilai_uts !== null ? modalData.nilai_uts : '-'"></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nilai UAS</label>
                                <p class="text-lg font-bold text-gray-800"
                                    x-text="modalData && modalData.nilai_uas !== null ? modalData.nilai_uas : '-'"></p>
                            </div>
                        </div>

                    </div>

                    <div class="mt-6 flex justify-end">
                        <button @click="isTugasHarianModalOpen = false"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition duration-150">
                            Tutup
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>


    {{------------------------------------------}}
    {{-- MODAL UPLOAD NILAI DARI PDF --}}
    {{------------------------------------------}}
    <div x-show="isUploadModalOpen" x-cloak x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isUploadModalOpen = false">
        </div>

        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 my-8" @click.stop>

                <div class="p-6 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                    <h3 class="text-xl font-bold text-blue-700">Import Nilai dari PDF</h3>
                    <p class="text-sm text-gray-500 mt-1">Unggah file PDF nilai yang sudah terstandarisasi untuk
                        pengolahan otomatis.</p>
                </div>

                <form action="{{ route('akademik.penilaian.upload') }}" method="POST" enctype="multipart/form-data" class="p-6">
                    @csrf
                    <input type="hidden" name="kelas" value="{{ $currentKelas }}">
                    <input type="hidden" name="mapel_id" value="{{ $mapelIdsTampil }}">

                    <div class="mb-4">
                        <label for="file_pdf" class="block text-sm font-medium text-gray-700">Pilih File PDF Nilai</label>
                        <input type="file" id="file_pdf" name="file_pdf" accept=".pdf" required
                            class="mt-1 block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
                            aria-describedby="file_pdf_help">
                        <p id="file_pdf_help" class="mt-1 text-xs text-gray-500">Maksimum 5MB. Pastikan format PDF sesuai
                            standar.</p>
                    </div>

                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" @click="isUploadModalOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition duration-150">
                            Unggah dan Proses
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    {{--------------------------------------------------}}
    {{-- MODAL DAFTAR SANTRI (untuk Tambah/Edit Cepat) --}}
    {{--------------------------------------------------}}
    <div x-show="isSantriListModalOpen" x-cloak x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isSantriListModalOpen = false">
        </div>

        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 my-8" @click.stop>

                <div class="p-6 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                    <h3 class="text-xl font-bold text-green-700">Pilih Santri untuk Dinilai</h3>
                    <p class="text-sm text-gray-500 mt-1">Pilih santri dari daftar ini untuk memulai input/edit nilai.</p>
                </div>

                <div class="p-4 max-h-96 overflow-y-auto">
                    {{-- KONDISI UNTUK MENAMPILKAN PESAN JIKA SANTRI KOSONG --}}
                    @if ($santriProfiles->isEmpty())
                        <div class="py-3 text-center text-gray-600">
                            Tidak ada santri yang tersedia untuk dinilai di kelas ini atau mata pelajaran belum terikat.
                            Pastikan Anda sudah terikat mata pelajaran dan santri terdaftar di kelas yang relevan.
                        </div>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach ($santriProfiles as $santri)
                            @php
                            // Siapkan data santri lengkap termasuk nilai penilaian yang sudah ada
                            $santriList = $santri->toArray();
                            $santriList['nis'] = $santri->santri->nis ?? '-';
                            $santriList['kelas_name'] = $santri->kelas ?? '-';
                            $nilaiMapel = $penilaians[$santri->id] ?? null;

                            $santriList['penilaian_id'] = $nilaiMapel->id ?? null;
                            $santriList['nilai_bab'] = json_decode($nilaiMapel->nilai_harian_json ?? '[]', true); 
                            $santriList['nilai_uts'] = $nilaiMapel->uts ?? '';
                            $santriList['nilai_uas'] = $nilaiMapel->uas ?? '';
                            
                            $isDinilai = $nilaiMapel !== null;
                            @endphp

                            <li class="py-3 flex justify-between items-center"
                                x-data='{ santriListData: @json($santriList) }'>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ $santri->nama ?? 'N/A' }}</p>
                                    <p class="text-xs text-gray-500">{{ $santri->kelas ?? '-' }} | NIS:
                                        {{ $santri->santri->nis ?? '-' }}</p>
                                </div>
                                <button
                                    @click="$parent.openCreateOrEditModalFromList(santriListData, {{ $isDinilai ? 'true' : 'false' }}); isSantriListModalOpen = false"
                                    class="px-3 py-1 text-xs font-medium rounded-full transition duration-150
                                                    {{ $isDinilai ? 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                    {{ $isDinilai ? 'Edit Nilai' : 'Input Nilai' }}
                                </button>
                            </li>
                            @endforeach
                        </ul>
                    @endif {{-- ENDIF untuk $santriProfiles->isEmpty() --}}
                </div>

                <div class="p-4 border-t border-gray-100 flex justify-end">
                    <button @click="isSantriListModalOpen = false"
                        class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">
                        Tutup
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

{{------------------------------------------}}
{{-- SCRIPT ALPINE.JS (LOGIKA KRUSIAL) --}}
{{------------------------------------------}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('penilaianApp', () => ({
        // Variabel yang umum digunakan
        // Pastikan currentChapterCount diambil setelah elemen ada di DOM
        currentChapterCount: parseInt(document.getElementById('mapel-jumlah-bab')?.value || '0'),

        // Variabel untuk modal (Semua di-handle di sini)
        isTugasHarianModalOpen: false, 
        isUploadModalOpen: false,
        isSantriListModalOpen: false, 
        
        // Data Modal
        modalTitle: '',
        modalActionUrl: '',
        modalMethod: 'POST',
        isDetailMode: false,
        modalData: null,
        currentBabScores: [], // Nilai Bab saat ini (untuk di-bind ke form)

        // --- FUNGSI UTILITY ---
        calculateAverage(scores) {
            if (!Array.isArray(scores) || scores.length === 0) return 0;
            // Filter hanya nilai numerik valid (0-100)
            const validScores = scores.filter(s => typeof s === 'number' && s >= 0 && s <= 100);
            if (validScores.length === 0) return 0;
            const sum = validScores.reduce((acc, current) => acc + current, 0);
            return Math.round(sum / validScores.length);
        },

        calculateOverallAverage(avgBab, uts, uas) {
            const bab = parseInt(avgBab) || 0;
            const utsVal = parseInt(uts) || 0;
            const uasVal = parseInt(uas) || 0;
            let total = 0;
            let divisor = 0;
            
            if (bab > 0) { total += bab; divisor += 1; }
            if (utsVal > 0) { total += utsVal; divisor += 1; }
            if (uasVal > 0) { total += uasVal; divisor += 1; }
            
            if (divisor === 0) return 0;
            return Math.round(total / divisor);
        },

        getGrade(average) {
            const avg = parseInt(average);
            if (avg >= 90) return 'A';
            if (avg >= 80) return 'B';
            if (avg >= 70) return 'C';
            if (avg >= 60) return 'D';
            return 'E';
        },
        
        // --- FUNGSI RESET & CLOSE ---
        resetModalData() {
            this.modalData = null;
            this.currentBabScores = Array(this.currentChapterCount).fill(null);
            this.modalTitle = '';
            this.modalActionUrl = '';
            this.modalMethod = 'POST';
            this.isDetailMode = false;
        },

        closeAllModals() {
            this.isTugasHarianModalOpen = false;
            this.isUploadModalOpen = false;
            this.isSantriListModalOpen = false;
            this.resetModalData();
        },
        
        // --- FUNGSI CRUD LOGIC ---

        // Fungsi yang dipanggil saat tombol Tambah Nilai di Tabel ditekan
        openCreateTugasHarianModal(santri) {
            this.resetModalData();
            this.modalTitle = 'Input Nilai Baru: ' + santri.nama;
            this.modalActionUrl = '{{ route("akademik.penilaian.store") }}';
            this.modalMethod = 'POST';
            this.modalData = santri;
            this.currentBabScores = Array(this.currentChapterCount).fill(null); 
            this.isTugasHarianModalOpen = true;
            
            this.$nextTick(() => this.resetAssessmentForm());
        },

        // Fungsi yang dipanggil saat tombol Edit Nilai di Tabel ditekan
        openEditTugasHarianModal(santri) {
            this.resetModalData();
            this.modalTitle = 'Edit Nilai: ' + santri.nama;
            const gradeId = santri.penilaian_id ?? null;

            this.modalActionUrl = '{{ url("akademik/penilaian") }}/' + gradeId;
            this.modalMethod = 'PUT';
            this.modalData = santri;
            this.currentBabScores = santri.nilai_bab || Array(this.currentChapterCount).fill(null);
            this.isTugasHarianModalOpen = true;

            this.$nextTick(() => this.fillAssessmentForm(santri));
        },
        
        // Fungsi yang dipanggil saat tombol Rincian di Tabel ditekan
        openDetailModal(santri) {
            this.resetModalData();
            this.modalTitle = 'Rincian Nilai: ' + santri.nama;
            this.modalData = santri;
            this.currentBabScores = santri.nilai_bab || Array(this.currentChapterCount).fill(null);
            this.isDetailMode = true;
            this.isTugasHarianModalOpen = true;
        },

        // Dipanggil dari Modal Daftar Santri (Modal 3)
        openCreateOrEditModalFromList(santri, isDinilai) {
            if (isDinilai) {
                this.openEditTugasHarianModal(santri);
            } else {
                this.openCreateTugasHarianModal(santri);
            }
        },
        
        // --- FUNGSI FORM MANIPULASI ---
        resetAssessmentForm() {
            // Mengosongkan field nilai (UTS, UAS) untuk mode CREATE
            const refs = ['nilai_uts_input', 'nilai_uas_input'];
            refs.forEach(ref => {
                if (this.$refs[ref]) this.$refs[ref].value = '';
            });
        },

        fillAssessmentForm(santri) {
            // Mengisi input fields (mode EDIT)
            if (this.$refs.nilai_uts_input) this.$refs.nilai_uts_input.value = santri.nilai_uts ?? '';
            if (this.$refs.nilai_uas_input) this.$refs.nilai_uas_input.value = santri.nilai_uas ?? '';
        },
    }));
});
</script>
@endsection