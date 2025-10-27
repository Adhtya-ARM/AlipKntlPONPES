@extends('layouts.app')

@section('title', 'Input Penilaian Santri')

@section('content')
    {{-- div utama untuk Alpine.js --}}
    <div x-data="penilaianApp()">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-semibold text-gray-700">Input Penilaian Santri</h2>

            {{-- BLOK TOMBOL AKSI --}}
            <div class="flex space-x-3">

                {{-- TOMBOL: Tambah Data Manual (Membuka Modal Daftar Santri) --}}
                <button @click="isSantriListModalOpen = true" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 shadow-md transition duration-150">
                    + Tambah Data Manual
                </button>

                {{-- Tombol Import Nilai (PDF) --}}
                <button @click="isUploadModalOpen = true" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition duration-150">
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
        {{-- ASUMSI: $mapelSaatIni adalah objek Mapel yang dikirim dari Controller, dan $mapelIdsTampil adalah ID Mapel --}}
        @if ($mapelIdsTampil)
            {{-- Menggunakan variabel yang dikirim dari Controller --}}
            @php
                // Di Controller, Anda harus mengirim:
                // $mapelSaatIni = $currentMapel; // Objek Mapel
            @endphp
            <div class="bg-indigo-50 text-indigo-800 p-3 rounded-md mb-4 text-sm font-medium border-l-4 border-indigo-500">
                Anda menginput nilai untuk mata pelajaran:
                <span class="font-bold">{{ $mapelSaatIni->nama_mapel ?? 'N/A' }}</span>
                di kelas:
                <span class="font-bold">{{ $currentKelas }}</span>
            </div>
        @else
            <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md mb-4 border border-yellow-400">
                Tidak ada Mata Pelajaran yang dipilih atau terikat dengan akun Anda.
            </div>
        @endif


        {{-- Tabel Data Santri dan Nilai --}}
        @if ($santriProfiles->isEmpty() && $mapelIdsTampil)
            <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md border border-yellow-400">
                Belum ada data santri yang tercatat untuk dinilai pada kelas **{{ $currentKelas }}** ini.
            </div>
        @elseif (!$santriProfiles->isEmpty())
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($santriProfiles as $santri)
                            @php
                                $nilaiMapel = $penilaians[$santri->id] ?? null;

                                $santriAlpineData = $santri->toArray();
                                $santriAlpineData['nis'] = $santri->santri->nis ?? '-';

                                if ($nilaiMapel) {
                                    $santriAlpineData['penilaian_id'] = $nilaiMapel->id;
                                    $santriAlpineData['nilai_harian'] = $nilaiMapel->nilai ?? '';
                                    $santriAlpineData['nilai_uts'] = $nilaiMapel->uts ?? '';
                                    $santriAlpineData['nilai_uas'] = $nilaiMapel->uas ?? '';
                                    $santriAlpineData['catatan'] = $nilaiMapel->catatan ?? '';
                                } else {
                                    $santriAlpineData['penilaian_id'] = null;
                                    $santriAlpineData['nilai_harian'] = '';
                                    $santriAlpineData['nilai_uts'] = '';
                                    $santriAlpineData['nilai_uas'] = '';
                                    $santriAlpineData['catatan'] = '';
                                }
                            @endphp

                            <tr x-data='{ santriData: @json($santriAlpineData) }'>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ $loop->iteration + ($santriProfiles->currentPage() - 1) * $santriProfiles->perPage() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $santri->nama ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $santri->kelas ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $santri->santri->nis ?? '-' }}</td>

                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    <div class="flex items-center space-x-2 justify-center">

                                        <button @click="$parent.openDetailModal(santriData)"
                                                class="text-xs font-semibold px-2 py-1 rounded-full text-blue-600 hover:bg-blue-100 transition duration-150">
                                            Rincian
                                        </button>

                                        @if ($nilaiMapel)
                                            <button @click="$parent.openEditModal(santriData)"
                                                    class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-600 text-white hover:bg-indigo-700 transition duration-150 shadow-md">
                                                Edit Nilai
                                            </button>

                                            <form action="{{ route('penilaian.destroy', $nilaiMapel->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus nilai ini?')" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <input type="hidden" name="kelas" value="{{ $currentKelas }}">
                                                <button type="submit" class="text-xs font-semibold px-2 py-1 rounded-full text-red-600 hover:bg-red-100">
                                                    Hapus
                                                </button>
                                            </form>
                                        @else
                                            <button @click="$parent.openCreateModal(santriData)"
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
        @endif


        {{-----------------------------------------------------------}}
        {{-- MODAL 1: INPUT/EDIT/DETAIL NILAI (CREATE, UPDATE, READ) --}}
        {{-----------------------------------------------------------}}
        <div x-show="isModalOpen" x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isModalOpen = false"></div>

            <div class="flex items-center justify-center min-h-screen">
                <div x-transition:enter="ease-out duration-300"
                      x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                      x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                      x-transition:leave="ease-in duration-200"
                      x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                      x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                      class="bg-white rounded-xl shadow-2xl transform transition-all max-w-md w-full mx-4 my-8"
                      @click.away="isModalOpen = false">

                    <div class="p-6 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h3 class="text-xl font-bold text-indigo-700" x-text="modalTitle"></h3>
                        <p class="text-sm text-gray-500 mt-1" x-show="!isDetailMode">Masukkan atau perbarui nilai (0-100) untuk mata pelajaran yang relevan.</p>
                        <p class="text-sm text-gray-500 mt-1" x-show="isDetailMode">Mode Rincian: Data ini bersifat *read-only*.</p>
                    </div>

                    <form :action="modalActionUrl" method="POST" class="p-6" x-show="!isDetailMode">
                        @csrf
                        {{-- Field tersembunyi untuk metode HTTP PUT/PATCH --}}
                        <input type="hidden" name="_method" :value="modalMethod" x-show="modalMethod !== 'POST'">

                        {{-- Hidden Fields Penting (Sesuai dengan kolom DB yang relevan untuk Penilaian) --}}
                        <input type="hidden" name="santri_profile_id" :value="modalData ? modalData.id : ''">
                        <input type="hidden" name="mapel_id" value="{{ $mapelIdsTampil }}">
                        <input type="hidden" name="kelas" value="{{ $currentKelas }}">
                        {{-- penilaian_id hanya dibutuhkan untuk rute Edit/Update --}}
                        <input type="hidden" name="penilaian_id" :value="modalData ? modalData.penilaian_id : ''" x-show="modalMethod === 'PUT'">

                        <div class="space-y-4">

                            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-200">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">NIS</label>
                                    {{-- Menggunakan x-text atau innerText (ref) untuk menampilkan data Alpine --}}
                                    <p x-ref="nis_form" class="mt-1 block w-full border-0 p-0 text-sm font-semibold text-gray-900 bg-white" x-text="modalData ? modalData.nis : ''"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">Nama</label>
                                    <p x-ref="nama_form" class="mt-1 block w-full border-0 p-0 text-sm font-semibold text-gray-900 bg-white" x-text="modalData ? modalData.nama : 'N/A'"></p>
                                </div>
                            </div>

                            <h4 class="text-base font-semibold text-gray-700 pt-2 border-b-2 pb-1">Input Nilai</h4>

                            <div>
                                <label for="nilai_harian" class="block text-sm font-medium text-gray-700">Nilai Harian (DB: nilai)</label>
                                <input type="number" x-ref="nilai_harian_input" id="nilai_harian" name="nilai_harian" min="0" max="100" placeholder="Contoh: 85" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" required>
                            </div>

                            <div>
                                <label for="nilai_uts" class="block text-sm font-medium text-gray-700">Nilai UTS (DB: uts)</label>
                                <input type="number" x-ref="nilai_uts_input" id="nilai_uts" name="nilai_uts" min="0" max="100" placeholder="Contoh: 78" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" required>
                            </div>

                            <div>
                                <label for="nilai_uas" class="block text-sm font-medium text-gray-700">Nilai UAS (DB: uas)</label>
                                <input type="number" x-ref="nilai_uas_input" id="nilai_uas" name="nilai_uas" min="0" max="100" placeholder="Contoh: 92" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" required>
                            </div>

                            <div class="pt-2">
                                <label for="catatan" class="block text-sm font-medium text-gray-700">Catatan Pengajar (DB: catatan) (Opsional)</label>
                                <textarea x-ref="catatan_input" id="catatan" name="catatan" rows="2" placeholder="Tuliskan catatan khusus terkait performa santri..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2"></textarea>
                            </div>

                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition duration-150">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md transition duration-150">
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
                                    <p class="text-sm font-semibold text-gray-900" x-text="modalData ? modalData.nis : '-'"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">Nama</label>
                                    <p class="text-sm font-semibold text-gray-900" x-text="modalData ? modalData.nama : 'N/A'"></p>
                                </div>
                            </div>

                            <h4 class="text-base font-semibold text-gray-700 pt-2 border-b-2 pb-1">Rincian Nilai</h4>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nilai Harian</label>
                                <p class="text-lg font-bold text-gray-800" x-text="modalData && modalData.nilai_harian !== null ? modalData.nilai_harian : '-'"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nilai UTS</label>
                                <p class="text-lg font-bold text-gray-800" x-text="modalData && modalData.nilai_uts !== null ? modalData.nilai_uts : '-'"></p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nilai UAS</label>
                                <p class="text-lg font-bold text-gray-800" x-text="modalData && modalData.nilai_uas !== null ? modalData.nilai_uas : '-'"></p>
                            </div>

                            <div class="pt-2">
                                <label class="block text-sm font-medium text-gray-700">Catatan Pengajar</label>
                                <p class="text-sm text-gray-700 italic border-l-4 border-indigo-400 pl-3 py-1" x-text="modalData && modalData.catatan && modalData.catatan !== '' ? modalData.catatan : 'Tidak ada catatan.'"></p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button @click="isModalOpen = false" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition duration-150">
                                Tutup
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{------------------------------------------}}
        {{-- MODAL 2: UPLOAD NILAI DARI PDF --}}
        {{------------------------------------------}}
        <div x-show="isUploadModalOpen" x-cloak class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isUploadModalOpen = false"></div>

            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-xl shadow-2xl max-w-sm w-full mx-4 my-8" @click.away="isUploadModalOpen = false">
                    <div class="p-6 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h3 class="text-xl font-bold text-indigo-700">Upload Nilai dari PDF</h3>
                        <p class="text-sm text-gray-500 mt-1">Pastikan format PDF sesuai dengan format parsing di backend.</p>
                    </div>

                    <form action="{{ route('penilaian.upload.pdf') }}" method="POST" enctype="multipart/form-data" class="p-6">
                        @csrf
                        <div class="space-y-4">
                            <input type="hidden" name="mapel_id_upload" value="{{ $mapelIdsTampil }}">
                            <input type="hidden" name="kelas_upload" value="{{ $currentKelas }}">

                            <div>
                                <label for="pdf_file" class="block text-sm font-medium text-gray-700">Pilih File PDF</label>
                                <input type="file" id="pdf_file" name="pdf_file" accept=".pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" required>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="isUploadModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">Batal</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700">Upload & Proses</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{--------------------------------------------------}}
        {{-- MODAL 3: Daftar Santri untuk Tambah Data Manual --}}
        {{--------------------------------------------------}}
        <div x-show="isSantriListModalOpen" x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isSantriListModalOpen = false"></div>

            <div class="flex items-center justify-center min-h-screen">
                <div class="bg-white rounded-xl shadow-2xl max-w-lg w-full mx-4 my-8" @click.away="isSantriListModalOpen = false">

                    <div class="p-6 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                        <h3 class="text-xl font-bold text-green-700">Pilih Santri untuk Dinilai</h3>
                        <p class="text-sm text-gray-500 mt-1">Pilih santri dari daftar ini untuk memulai input nilai.</p>
                    </div>

                    <div class="p-4 max-h-96 overflow-y-auto">
                        <ul class="divide-y divide-gray-200">
                            @foreach ($santriProfiles as $santri)
                                @php
                                    $santriList = $santri->toArray();
                                    $santriList['nis'] = $santri->santri->nis ?? '-';
                                    $nilaiMapel = $penilaians[$santri->id] ?? null;

                                    // Siapkan data penilaian lengkap
                                    $santriList['penilaian_id'] = $nilaiMapel->id ?? null;
                                    $santriList['nilai_harian'] = $nilaiMapel->nilai ?? '';
                                    $santriList['nilai_uts'] = $nilaiMapel->uts ?? '';
                                    $santriList['nilai_uas'] = $nilaiMapel->uas ?? '';
                                    $santriList['catatan'] = $nilaiMapel->catatan ?? '';

                                    $isDinilai = $nilaiMapel !== null;
                                @endphp

                                <li class="py-3 flex justify-between items-center" x-data='{ santriListData: @json($santriList) }'>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ $santri->nama ?? 'N/A' }}</p>
                                        <p class="text-xs text-gray-500">{{ $santri->kelas ?? '-' }} | NIS: {{ $santri->santri->nis ?? '-' }}</p>
                                    </div>
                                    <button @click="$parent.openCreateOrEditModalFromList(santriListData, {{ $isDinilai ? 'true' : 'false' }}); isSantriListModalOpen = false"
                                            class="px-3 py-1 text-xs font-medium rounded-full transition duration-150
                                                 {{ $isDinilai ? 'bg-indigo-100 text-indigo-700 hover:bg-indigo-200' : 'bg-green-100 text-green-700 hover:bg-green-200' }}">
                                        {{ $isDinilai ? 'Edit Nilai' : 'Input Nilai' }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <div class="p-4 border-t border-gray-100 flex justify-end">
                        <button @click="isSantriListModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100">
                            Tutup
                        </button>
                    </div>

                </div>
            </div>
        </div>
        
    </div>

    {{------------------------------------------}}
    {{-- SCRIPT ALPINE.JS --}}
    {{------------------------------------------}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('penilaianApp', () => ({
                // Variabel yang umum digunakan di layout seperti sidebar (ditambahkan untuk menghindari error "open_master is not defined" jika elemennya berada di scope ini)
                open_master: false, 
                
                // Variabel untuk modal
                isModalOpen: false, // Modal Input/Edit/Detail
                isUploadModalOpen: false,
                isSantriListModalOpen: false, // Modal Daftar Santri untuk input manual
                modalTitle: '',
                modalActionUrl: '',
                modalMethod: 'POST',
                isDetailMode: false,
                modalData: null,

                // FUNGSI 1: CREATE (Tambah Nilai Baru)
                openCreateModal(santri) {
                    this.modalTitle = 'Tambah Nilai Baru untuk: ' + santri.nama;
                    this.modalActionUrl = '{{ route('penilaian.store') }}';
                    this.modalMethod = 'POST';
                    this.isDetailMode = false;
                    this.modalData = santri;
                    this.isModalOpen = true;

                    // Mengosongkan form dan mengisi info santri
                    this.$nextTick(() => {
                        this.resetFormAndFillSantriInfo(santri);
                        // Menggunakan x-text pada elemen p
                        if (this.$refs.nis_form) this.$refs.nis_form.innerText = santri.nis ?? '';
                        if (this.$refs.nama_form) this.$refs.nama_form.innerText = santri.nama ?? 'N/A';
                    });
                },

                // FUNGSI 2: EDIT (Update Nilai yang Sudah Ada)
                openEditModal(santri) {
                    this.modalTitle = 'Edit Nilai untuk: ' + santri.nama;
                    const gradeId = santri.penilaian_id ?? null;
                    if (!gradeId) {
                        // Fallback jika tidak ada ID Penilaian (walaupun seharusnya ada di mode edit)
                        this.openCreateModal(santri);
                        return;
                    }

                    this.modalActionUrl = '{{ url('penilaian') }}/' + gradeId;
                    this.modalMethod = 'PUT';
                    this.isDetailMode = false;
                    this.modalData = santri;
                    this.isModalOpen = true;

                    // Mengisi form dengan data penilaian yang sudah ada
                    this.$nextTick(() => {
                        this.fillAssessmentForm(santri);
                        // Menggunakan x-text pada elemen p
                        if (this.$refs.nis_form) this.$refs.nis_form.innerText = santri.nis ?? '';
                        if (this.$refs.nama_form) this.$refs.nama_form.innerText = santri.nama ?? 'N/A';
                    });
                },

                // FUNGSI 3: DETAIL (Lihat Nilai Saja)
                openDetailModal(santri) {
                    this.modalTitle = 'Rincian Nilai: ' + santri.nama;
                    this.modalActionUrl = '#';
                    this.modalMethod = 'GET';
                    this.isDetailMode = true;
                    this.modalData = santri;
                    this.isModalOpen = true;
                },

                // FUNGSI BARU: Dipanggil dari Modal Daftar Santri (Modal 3)
                openCreateOrEditModalFromList(santri, isDinilai) {
                    if (isDinilai) {
                        this.openEditModal(santri);
                    } else {
                        this.openCreateModal(santri);
                    }
                },

                // Fungsi utilitas untuk mereset form dan mengisi info santri (untuk CREATE)
                resetFormAndFillSantriInfo(santri) {
                    // Mengisi info santri (readonly) - TIDAK PERLU lagi karena sudah pakai x-text di elemen p

                    // Mengosongkan field nilai
                    if (this.$refs.nilai_harian_input) this.$refs.nilai_harian_input.value = '';
                    if (this.$refs.nilai_uts_input) this.$refs.nilai_uts_input.value = '';
                    if (this.$refs.nilai_uas_input) this.$refs.nilai_uas_input.value = '';
                    if (this.$refs.catatan_input) this.$refs.catatan_input.value = '';
                },

                // Mengisi field dengan data penilaian yang sudah ada (untuk EDIT)
                fillAssessmentForm(santri) {
                    // Mengisi info santri (readonly) - TIDAK PERLU lagi karena sudah pakai x-text di elemen p

                    // Mengisi input fields (mode EDIT)
                    if (this.$refs.nilai_harian_input) this.$refs.nilai_harian_input.value = santri.nilai_harian ?? '';
                    if (this.$refs.nilai_uts_input) this.$refs.nilai_uts_input.value = santri.nilai_uts ?? '';
                    if (this.$refs.nilai_uas_input) this.$refs.nilai_uas_input.value = santri.nilai_uas ?? '';
                    if (this.$refs.catatan_input) this.$refs.catatan_input.value = santri.catatan ?? '';
                },
            }));
        });
    </script>
@endsection