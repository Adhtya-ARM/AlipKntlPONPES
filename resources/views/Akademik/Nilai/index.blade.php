@extends('layouts.app')

@section('title', 'Input Penilaian Santri')

@section('content')
    <!-- Pastikan Alpine.js dan Tailwind CSS tersedia di layout utama -->
    <div x-data="{ 
        isModalOpen: false, 
        modalTitle: '', 
        modalActionUrl: '', 
        modalMethod: 'POST', // Default untuk Create
        isDetailMode: false, // Status untuk mode Detail/Read-only
        modalData: null,
        
        // FUNGSI 1: CREATE (Tambah Nilai Baru)
        openCreateModal(santri) {
            const santriName = santri.santriprofile ? santri.santriprofile.nama : 'N/A';
            this.modalTitle = 'Tambah Nilai Baru untuk: ' + santriName;
            
            // Asumsi: Jika belum ada nilai, kita akan POST ke route umum (misal: /penilaian)
            // Ini akan memerlukan penanganan di backend untuk CREATE.
            this.modalActionUrl = '{{ url('penilaian/store') }}'; // Asumsi route store
            this.modalMethod = 'POST';
            this.isDetailMode = false;
            this.modalData = santri; 
            this.isModalOpen = true;
            
            this.$nextTick(() => this.resetFormAndFillSantriInfo(santri));
        },

        // FUNGSI 2: EDIT (Update Nilai yang Sudah Ada)
        openEditModal(santri) {
            const santriName = santri.santriprofile ? santri.santriprofile.nama : 'N/A';
            this.modalTitle = 'Edit Nilai untuk: ' + santriName;
            
            // Asumsi: Menggunakan ID Penilaian (grades.id) untuk update
            // Jika grades kosong, ini tidak boleh dipanggil, atau harus ditangani.
            const gradeId = santri.grades ? santri.grades.id : 'N/A'; 
            this.modalActionUrl = gradeId !== 'N/A' ? '{{ url('penilaian') }}/' + gradeId : '{{ url('penilaian/update-fallback') }}'; // Fallback
            this.modalMethod = 'PUT'; // Menggunakan PUT untuk UPDATE
            this.isDetailMode = false;
            this.modalData = santri; 
            this.isModalOpen = true;
            
            this.$nextTick(() => this.fillAssessmentForm(santri));
        },

        // FUNGSI 3: DETAIL (Lihat Nilai Saja)
        openDetailModal(santri) {
            const santriName = santri.santriprofile ? santri.santriprofile.nama : 'N/A';
            this.modalTitle = 'Rincian Nilai: ' + santriName;
            
            // Pada mode Detail, Action URL dan Method tidak penting
            this.modalActionUrl = '#'; 
            this.modalMethod = 'GET';
            this.isDetailMode = true; // Set mode detail
            this.modalData = santri; 
            this.isModalOpen = true;
            
            this.$nextTick(() => this.fillAssessmentForm(santri));
        },

        // Fungsi utilitas untuk mereset form dan mengisi info santri (digunakan di CREATE)
        resetFormAndFillSantriInfo(santri) {
            document.getElementById('nis_form').value = santri.nis ?? '';
            document.getElementById('nama_form').value = santri.santriprofile.nama ?? 'N/A';
            document.getElementById('nilai_harian').value = '';
            document.getElementById('nilai_uts').value = '';
            document.getElementById('nilai_uas').value = '';
            document.getElementById('catatan').value = '';
        },

        // FUNGSI KRITIS: Mengisi field dengan data penilaian yang sudah ada (digunakan di EDIT & DETAIL)
        fillAssessmentForm(santri) {
            const gradeData = santri.grades || {}; 
            
            document.getElementById('nis_form').value = santri.nis ?? '';
            document.getElementById('nama_form').value = santri.santriprofile.nama ?? 'N/A';

            document.getElementById('nilai_harian').value = gradeData.nilai_harian ?? '';
            document.getElementById('nilai_uts').value = gradeData.nilai_uts ?? '';
            document.getElementById('nilai_uas').value = gradeData.nilai_uas ?? '';
            document.getElementById('catatan').value = gradeData.catatan ?? '';
        },

        // Fungsi bantuan Alpine untuk mendapatkan nilai atau '-'
        getGrade(santri, key) {
            return santri.grades && santri.grades[key] !== undefined ? santri.grades[key] : '-';
        },
    }">

        <!-- Header & Tombol -->
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-semibold text-gray-700">Input Penilaian Santri</h2>
        </div>

        <!-- Tabel Data Santri untuk Penilaian -->
        @if ($santriProfiles->isEmpty())
            <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md border border-yellow-400">
                Belum ada data santri yang tercatat untuk dinilai.
            </div>
        @else
            <div class="bg-white shadow overflow-hidden rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Harian</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai UTS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai UAS</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach ($santriProfiles as $santri)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                    {{ $loop->iteration + ($santriProfiles->currentPage() - 1) * $santriProfiles->perPage() }}
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $santri->santriprofile->nama ?? '-' }}</td> 
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $santri->nis }}</td>
                                
                                <!-- Kolom Nilai. Catatan: Asumsi relasi 'grades' sudah dimuat di controller! -->
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span x-data='{ santriData: @php echo json_encode($santri->load(['santriprofile', 'grades'])->toArray()) @endphp }' x-text="getGrade(santriData, 'nilai_harian')">
                                        {{ $santri->grades['nilai_harian'] ?? '-' }} 
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span x-data='{ santriData: @php echo json_encode($santri->load(['santriprofile', 'grades'])->toArray()) @endphp }' x-text="getGrade(santriData, 'nilai_uts')">
                                        {{ $santri->grades['nilai_uts'] ?? '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <span x-data='{ santriData: @php echo json_encode($santri->load(['santriprofile', 'grades'])->toArray()) @endphp }' x-text="getGrade(santriData, 'nilai_uas')">
                                        {{ $santri->grades['nilai_uas'] ?? '-' }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                    
                                    <!-- JSON BINDING (Diperlukan untuk passing data lengkap ke Alpine) -->
                                    <div x-data='{ santriData: @php echo json_encode($santri->load(['santriprofile', 'grades'])->toArray()) @endphp }' class="flex items-center space-x-2 justify-center">
                                        
                                        <!-- Tombol Detail -->
                                        <button @click="$parent.openDetailModal(santriData)" 
                                                class="text-xs font-semibold px-2 py-1 rounded-full text-blue-600 hover:bg-blue-100 transition duration-150">
                                            Rincian
                                        </button>
                                        
                                        <!-- Tombol Input/Edit (Conditional) -->
                                        @if ($santri->grades)
                                            <!-- Jika sudah ada nilai, tombol EDIT -->
                                            <button @click="$parent.openEditModal(santriData)" 
                                                    class="text-xs font-semibold px-3 py-1 rounded-full bg-indigo-600 text-white hover:bg-indigo-700 transition duration-150 shadow-md">
                                                Edit Nilai
                                            </button>
                                        @else
                                            <!-- Jika belum ada nilai, tombol TAMBAH -->
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
                @if (method_exists($santris, 'links'))
                    {{ $santris->links('vendor.pagination.tailwind') }}
                @else
                    <p class="text-sm text-gray-600">Menampilkan {{ count($santris) }} data. Pagination dinonaktifkan.</p>
                @endif
            </div>
        @endif

        <!-- MODAL CONTAINER (Diadaptasi untuk Penilaian: Create, Update, Detail) -->
        <div x-show="isModalOpen" x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

            <!-- Background Overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isModalOpen = false"></div>

            <!-- Modal Content Container -->
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

                    <!-- Form: Digunakan untuk Input/Edit Nilai. Disembunyikan saat mode Detail. -->
                    <form :action="modalActionUrl" :method="modalMethod === 'POST' ? 'POST' : 'POST'" class="p-6" x-show="!isDetailMode">
                        @csrf
                        <template x-if="modalMethod === 'PUT'">
                            @method('PUT') <!-- Method PUT/PATCH untuk update -->
                        </template>
                        
                        <!-- Hidden Field untuk Santri ID -->
                        <input type="hidden" name="santri_id" :value="modalData ? modalData.id : ''">
                        <template x-if="modalMethod === 'PUT'">
                            <input type="hidden" name="grade_id" :value="modalData && modalData.grades ? modalData.grades.id : ''">
                        </template>

                        <div class="space-y-4">
                            
                            <!-- Display Santri Info (Read-only) -->
                            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-200">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">NIS</label>
                                    <input type="text" id="nis_form" class="mt-1 block w-full border-0 p-0 text-sm font-semibold text-gray-900 bg-white" readonly>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">Nama</label>
                                    <input type="text" id="nama_form" class="mt-1 block w-full border-0 p-0 text-sm font-semibold text-gray-900 bg-white" readonly>
                                </div>
                            </div>

                            <h4 class="text-base font-semibold text-gray-700 pt-2 border-b-2 pb-1">Input Nilai</h4>
                            
                            <!-- Nilai Harian -->
                            <div>
                                <label for="nilai_harian" class="block text-sm font-medium text-gray-700">Nilai Harian</label>
                                <input type="number" id="nilai_harian" name="nilai_harian" min="0" max="100" placeholder="Contoh: 85" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" required>
                            </div>
                            
                            <!-- Nilai UTS -->
                            <div>
                                <label for="nilai_uts" class="block text-sm font-medium text-gray-700">Nilai UTS</label>
                                <input type="number" id="nilai_uts" name="nilai_uts" min="0" max="100" placeholder="Contoh: 78" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" required>
                            </div>

                            <!-- Nilai UAS -->
                            <div>
                                <label for="nilai_uas" class="block text-sm font-medium text-gray-700">Nilai UAS</label>
                                <input type="number" id="nilai_uas" name="nilai_uas" min="0" max="100" placeholder="Contoh: 92" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2" required>
                            </div>
                            
                            <!-- Catatan Tambahan (Opsional) -->
                            <div class="pt-2">
                                <label for="catatan" class="block text-sm font-medium text-gray-700">Catatan Pengajar (Opsional)</label>
                                <textarea id="catatan" name="catatan" rows="2" placeholder="Tuliskan catatan khusus terkait performa santri..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2"></textarea>
                            </div>

                        </div>

                        <!-- Footer Tombol untuk CREATE/UPDATE -->
                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-100 transition duration-150">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 shadow-md transition duration-150">
                                Simpan Nilai
                            </button>
                        </div>
                    </form>
                    
                    <!-- View Mode/Read-only Content (Ditampilkan saat isDetailMode = true) -->
                    <div class="p-6" x-show="isDetailMode">
                        <div class="space-y-4">
                            <!-- Display Santri Info (Read-only) -->
                            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-gray-200">
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">NIS</label>
                                    <p class="text-sm font-semibold text-gray-900" x-text="modalData ? modalData.nis : '-'"></p>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-500">Nama</label>
                                    <p class="text-sm font-semibold text-gray-900" x-text="modalData && modalData.santriprofile ? modalData.santriprofile.nama : 'N/A'"></p>
                                </div>
                            </div>
                            
                            <h4 class="text-base font-semibold text-gray-700 pt-2 border-b-2 pb-1">Rincian Nilai</h4>
                            
                            <!-- Nilai Harian -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nilai Harian</label>
                                <p class="text-lg font-bold text-gray-800" x-text="getGrade(modalData, 'nilai_harian')"></p>
                            </div>
                            
                            <!-- Nilai UTS -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nilai UTS</label>
                                <p class="text-lg font-bold text-gray-800" x-text="getGrade(modalData, 'nilai_uts')"></p>
                            </div>

                            <!-- Nilai UAS -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nilai UAS</label>
                                <p class="text-lg font-bold text-gray-800" x-text="getGrade(modalData, 'nilai_uas')"></p>
                            </div>
                            
                            <!-- Catatan Tambahan (Opsional) -->
                            <div class="pt-2">
                                <label class="block text-sm font-medium text-gray-700">Catatan Pengajar</label>
                                <p class="text-sm text-gray-700 italic border-l-4 border-indigo-400 pl-3 py-1" x-text="modalData && modalData.grades && modalData.grades.catatan ? modalData.grades.catatan : 'Tidak ada catatan.'"></p>
                            </div>
                        </div>
                        
                        <!-- Footer Tombol untuk DETAIL -->
                        <div class="mt-6 flex justify-end">
                            <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-md transition duration-150">
                                Tutup
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
@endsection
