@extends('layouts.app')

@section('title', 'Kelola Data Kelas')

@section('content')
<div x-data="kelasHandler()" x-init="init()" class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Kelola Data Kelas</h2>
        <p class="text-sm text-gray-500 mt-1">Manajemen kelas dan wali kelas</p>
    </div>

    {{-- Summary Cards --}}
    {{-- Pastikan variabel $summary tersedia dari controller --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach($summary as $jurusan => $count)
        <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md transition">
            <div class="text-xs text-gray-500 mb-1">{{ $jurusan }}</div>
            <div class="text-2xl font-bold text-blue-600">{{ $count }} <span class="text-sm text-gray-500">Kelas</span></div>
        </div>
        @endforeach
    </div>

    {{-- Action Buttons --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <button @click="openModal('create')" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700 transition flex items-center gap-2">
                <i class="fas fa-plus"></i> Tambah
            </button>
            <button @click="filterTingkat = ''" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition" :class="filterTingkat === '' ? 'bg-blue-50 text-blue-700 font-semibold' : ''">
                Semua Tingkat
            </button>
            {{-- Filter Buttons for Tingkat 7 to 12 --}}
            @for ($i = 7; $i <= 12; $i++)
            <button @click="filterTingkat = '{{ $i }}'" class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-200 transition" :class="filterTingkat === '{{ $i }}' ? 'bg-blue-50 text-blue-700 font-semibold' : ''">
                Tingkat {{ $i }}
            </button>
            @endfor
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-16">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tingkat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wali Kelas</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-80">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="(kelas, index) in filteredKelas" :key="kelas.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="index + 1"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900" x-text="kelas.level"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                {{-- Readonly mode: Tampilkan nama guru jika ada dan tidak dalam mode edit Wali Kelas --}}
                                <template x-if="kelas.wali_kelas_id && editingWaliId !== kelas.id">
                                    <div class="text-sm text-gray-900 px-3 py-2 border border-transparent rounded-md bg-white" x-text="getGuruName(kelas.wali_kelas_id)"></div>
                                </template>
                                
                                {{-- Editable mode: Tampilkan dropdown jika belum ada wali kelas atau mode edit diaktifkan --}}
                                <template x-if="!kelas.wali_kelas_id || editingWaliId === kelas.id">
                                    <select x-model="kelas.wali_kelas_id" 
                                            :disabled="editingWaliId !== kelas.id"
                                            class="border-gray-300 rounded-md text-sm w-full transition duration-150 ease-in-out p-2"
                                            :class="editingWaliId === kelas.id ? 'bg-white shadow-inner focus:ring-blue-500 focus:border-blue-500' : 'bg-gray-50 cursor-not-allowed'">
                                        <option value="">-- Pilih Wali Kelas --</option>
                                        <template x-for="guru in guruList" :key="guru.id">
                                            <option :value="guru.id" x-text="guru.nama"></option>
                                        </template>
                                    </select>
                                </template>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Tombol Atur Siswa --}}
                                    <button @click="openStudentModal(kelas)" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-blue-50 text-blue-700 rounded-md hover:bg-blue-100 transition text-xs font-medium"
                                            title="Atur Siswa">
                                        <i class="fas fa-users text-sm"></i>
                                        <span>Siswa</span>
                                    </button>

                                    {{-- Tombol Edit Data Kelas --}}
                                    <button @click="editKelas(kelas)" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-50 text-gray-700 rounded-md hover:bg-gray-100 transition text-xs font-medium"
                                            title="Edit Tingkat/Jurusan">
                                        <i class="fas fa-pen text-sm"></i>
                                        <span>Edit</span>
                                    </button>

                                    {{-- Tombol Ubah/Simpan Wali Kelas --}}
                                    <button @click="toggleWaliKelasEdit(kelas)" 
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-md transition text-xs font-medium"
                                            :class="editingWaliId === kelas.id ? 'bg-green-50 text-green-700 hover:bg-green-100' : 'bg-purple-50 text-purple-700 hover:bg-purple-100'"
                                            :title="editingWaliId === kelas.id ? 'Simpan Wali Kelas' : 'Ubah Wali Kelas'">
                                        <i class="fas text-sm" :class="editingWaliId === kelas.id ? 'fa-check' : 'fa-user-cog'"></i>
                                        <span x-text="editingWaliId === kelas.id ? 'Simpan' : 'Wali'"></span>
                                    </button>
                                    
                                    {{-- Tombol Hapus atau Icon Lock --}}
                                    <button @click="deleteKelas(kelas.id)" 
                                            x-show="!kelas.is_locked"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 bg-red-50 text-red-700 rounded-md hover:bg-red-100 transition text-xs font-medium"
                                            title="Hapus Kelas">
                                        <i class="fas fa-trash text-sm"></i>
                                        <span>Hapus</span>
                                    </button>
                                    
                                    <span x-show="kelas.is_locked" 
                                          class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 text-gray-500 rounded-md text-xs font-medium cursor-not-allowed" 
                                          title="Kelas terkunci karena sudah digunakan">
                                        <i class="fas fa-lock text-sm"></i>
                                        <span>Terkunci</span>
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="filteredKelas.length === 0">
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <div>Tidak ada data kelas</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Create/Edit --}}
    <div x-show="showModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4" x-text="modalMode === 'create' ? 'Tambah Kelas Baru' : 'Edit Kelas'"></h3>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat</label>
                    <select x-model="form.level" class="w-full border-gray-300 rounded-md text-sm p-2">
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                    </select>
                </div>

                <div x-show="modalMode === 'edit'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Wali Kelas (Untuk tampilan)</label>
                    {{-- Menampilkan nama wali kelas saat ini dari form data --}}
                    <input type="text" :value="form.wali_kelas_id ? getGuruName(form.wali_kelas_id) : 'Belum Ditentukan'" disabled class="w-full border-gray-300 rounded-md text-sm p-2 bg-gray-100 cursor-not-allowed">
                    <p class="text-xs text-gray-500 mt-1">Perubahan Wali Kelas dilakukan langsung pada tabel dengan tombol "Wali/Simpan".</p>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button @click="showModal = false" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300">
                    Batal
                </button>
                <button @click="saveKelas()" :disabled="isSaving" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!isSaving">Simpan</span>
                    <span x-show="isSaving">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal Atur Siswa --}}
    <div x-show="showStudentModal" x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm" style="display: none;">
        <div @click.away="showStudentModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 p-6 flex flex-col max-h-[90vh]">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Atur Siswa - <span x-text="studentModalTitle"></span></h3>
                <button @click="showStudentModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            {{-- Search Input --}}
            <div class="mb-4">
                <div class="relative">
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari nama atau NISN santri..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <div class="mb-4 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Total Siswa: <span class="font-bold" x-text="filteredStudents.length"></span> | 
                    Terpilih: <span class="font-bold text-blue-600" x-text="selectedStudents.length"></span>
                </div>
                <button @click="toggleSelectAll()" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                    <span x-text="isAllFilteredSelected() ? 'Hapus Semua' : 'Tandai Semua'"></span>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto border border-gray-200 rounded-lg p-2 bg-gray-50">
                <div x-show="isLoadingStudents" class="flex justify-center items-center h-32">
                    <i class="fas fa-spinner fa-spin text-blue-600 text-2xl"></i>
                </div>
                
                <div x-show="!isLoadingStudents && students.length === 0" class="text-center py-8 text-gray-500">
                    Tidak ada data siswa tersedia.
                </div>
                
                <div x-show="!isLoadingStudents && filteredStudents.length === 0 && students.length > 0" class="text-center py-8 text-gray-500">
                    <i class="fas fa-search text-3xl mb-2"></i>
                    <div>Tidak ada santri yang sesuai dengan pencarian</div>
                </div>

                <div x-show="!isLoadingStudents && filteredStudents.length > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <template x-for="student in filteredStudents" :key="student.id">
                        <label class="flex items-center space-x-3 p-3 bg-white rounded-lg border border-gray-200 transition"
                               :class="student.has_relations ? 'bg-gray-50 cursor-not-allowed' : 'hover:bg-blue-50 cursor-pointer'">
                            <input type="checkbox" 
                                   :value="student.id" 
                                   x-model="selectedStudents" 
                                   :disabled="student.has_relations"
                                   class="form-checkbox h-5 w-5 text-blue-600 rounded focus:ring-blue-500"
                                   :class="student.has_relations ? 'opacity-50 cursor-not-allowed' : ''">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-semibold text-gray-800" x-text="student.nama"></span>
                                    <i x-show="student.has_relations" 
                                       class="fas fa-lock text-xs text-red-500" 
                                       title="Santri memiliki data absensi/penilaian dan tidak dapat dihapus dari kelas"></i>
                                </div>
                                <div class="text-xs text-gray-500" x-text="student.nisn"></div>
                                <div x-show="student.has_relations" class="text-xs text-red-600 mt-1">
                                    Terdaftar dengan data absensi/penilaian
                                </div>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-100">
                <button @click="showStudentModal = false" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300">
                    Batal
                </button>
                <button @click="saveStudents()" :disabled="isSavingStudents" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!isSavingStudents">Simpan Perubahan</span>
                    <span x-show="isSavingStudents">Menyimpan...</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Pastikan library SweetAlert2 (Swal) dan Axios sudah di-include sebelum script ini.

    function kelasHandler() {
        return {
            // Data yang di-fetch dari controller
            allKelas: @json($kelas),
            guruList: @json($guruList),
            filteredKelas: [],
            
            // State
            filterTingkat: '',
            showModal: false,
            modalMode: 'create',
            isSaving: false,
            editingWaliId: null, // ID kelas yang sedang diedit wali kelasnya
            
            // Student Management State
            showStudentModal: false,
            studentModalTitle: '',
            currentKelasId: null,
            students: [],
            selectedStudents: [],
            isLoadingStudents: false,
            isSavingStudents: false,
            searchQuery: '', // Search query for filtering students

            // Form data untuk modal (Tambah/Edit Kelas)
            form: {
                id: null,
                level: '',
                wali_kelas_id: ''
            },

            init() {
                this.filteredKelas = this.allKelas;
                this.$watch('filterTingkat', () => this.applyFilter());
            },

            applyFilter() {
                if (this.filterTingkat === '') {
                    this.filteredKelas = this.allKelas;
                } else {
                    this.filteredKelas = this.allKelas.filter(k => k.level == this.filterTingkat);
                }
            },
            
            getGuruName(id) {
                const guru = this.guruList.find(g => g.id == id);
                return guru ? guru.nama : 'N/A';
            },
            
            // Computed property for filtered students based on search
            get filteredStudents() {
                if (!this.searchQuery) {
                    return this.students;
                }
                const query = this.searchQuery.toLowerCase();
                return this.students.filter(student => {
                    const nama = student.nama ? student.nama.toLowerCase() : '';
                    const nisn = student.nisn ? String(student.nisn).toLowerCase() : '';
                    return nama.includes(query) || nisn.includes(query);
                });
            },
            
            // Check if all filtered students are selected
            isAllFilteredSelected() {
                const availableStudents = this.filteredStudents.filter(s => !s.has_relations);
                if (availableStudents.length === 0) return false;
                return availableStudents.every(s => this.selectedStudents.includes(s.id.toString()));
            },

            // === Manajemen Modal (Tambah/Edit Kelas: Tingkat & Nama Unik) ===
            openModal(mode) {
                this.modalMode = mode;
                this.showModal = true;
                if (mode === 'create') {
                    this.form = { id: null, level: '', nama_unik: '', wali_kelas_id: '' };
                }
            },

            editKelas(kelas) {
                // Dipanggil saat ikon "fa-pen" diklik
                this.modalMode = 'edit';
                this.form = {
                    id: kelas.id,
                    level: kelas.level.toString(),
                    wali_kelas_id: kelas.wali_kelas_id || ''
                };
                this.showModal = true;
            },

            async saveKelas() {
                // Validasi hanya Level
                if (!this.form.level) {
                    Swal.fire('Error', 'Tingkat Kelas harus diisi', 'error');
                    return;
                }

                this.isSaving = true;
                const url = this.modalMode === 'create' ? '/akademik/kelas' : `/akademik/kelas/${this.form.id}`;
                const method = this.modalMode === 'create' ? 'post' : 'put';
                
                try {
                    // Hanya kirim level
                    const payload = {
                        level: this.form.level
                    };

                    await axios[method](url, payload);
                    
                    Swal.fire({ 
                        icon: 'success', 
                        title: this.modalMode === 'create' ? 'Kelas berhasil ditambahkan' : 'Kelas berhasil diupdate', 
                        timer: 1500, 
                        showConfirmButton: false 
                    }).then(() => {
                        location.reload(); // Reload untuk mendapatkan data kelas terbaru
                    });
                } catch (error) {
                    Swal.fire('Error', error.response?.data?.message || `Gagal ${this.modalMode === 'create' ? 'menambah' : 'mengupdate'} kelas`, 'error');
                } finally {
                    this.isSaving = false;
                    this.showModal = false;
                }
            },
            
            // === Manajemen Wali Kelas (Langsung di Tabel) ===

            // Tombol di kolom Aksi (Gear/Check) untuk edit Wali Kelas
            toggleWaliKelasEdit(kelas) {
                if (this.editingWaliId === kelas.id) {
                    // Mode edit aktif, klik lagi berarti menyimpan
                    this.updateWaliKelas(kelas.id, kelas.wali_kelas_id);
                } else {
                    // Mode edit tidak aktif, klik untuk mengaktifkan
                    // Jika ada baris lain yang sedang diedit, matikan mode edit baris lain
                    this.editingWaliId = kelas.id;
                }
            },

            async updateWaliKelas(kelasId, waliKelasId) {
                if (!kelasId || this.isSaving) return;

                console.log('UPDATE WALI KELAS:', {
                    kelasId,
                    waliKelasId
                });

                this.isSaving = true;
                
                try {
                    const payload = { guru_profile_id: waliKelasId === "" ? null : waliKelasId };
                    console.log('Sending payload:', payload);
                    
                    // Melakukan PUT request untuk update guru_profile_id (wali kelas)
                    const response = await axios.put(`/akademik/kelas/${kelasId}`, payload);
                    console.log('Response:', response.data);
                    
                    // Update data lokal dari response server (SUMBER KEBENARAN)
                    const updatedKelas = response.data.kelas;
                    const updateIndex = this.allKelas.findIndex(k => k.id === kelasId);
                    
                    if (updateIndex !== -1 && updatedKelas) {
                        // Update dengan data dari server
                        this.allKelas[updateIndex].guru_profile_id = updatedKelas.guru_profile_id;
                        this.allKelas[updateIndex].wali_kelas_id = updatedKelas.guru_profile_id;
                        
                        // Re-render
                        this.applyFilter();
                    }
                    
                    Swal.fire({ 
                        icon: 'success', 
                        title: 'Berhasil!',
                        text: 'Wali kelas berhasil diupdate',
                        timer: 2000, 
                        showConfirmButton: false 
                    });
                } catch (error) {
                    console.error('Error updating wali kelas:', error);
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: error.response?.data?.message || 'Gagal update wali kelas',
                        confirmButtonText: 'OK'
                    });
                } finally {
                    this.isSaving = false;
                    this.editingWaliId = null;
                }
            },

            // === Manajemen Siswa ===
            async openStudentModal(kelas) {
                this.currentKelasId = kelas.id;
                this.studentModalTitle = `Tingkat ${kelas.level}`;
                this.showStudentModal = true;
                this.isLoadingStudents = true;
                this.students = [];
                this.selectedStudents = [];
                this.searchQuery = ''; // Reset search when opening modal

                try {
                    const response = await axios.get(`/akademik/kelas/${kelas.id}/siswa`);
                    // Response diharapkan: { students: [...], enrolled_ids: [...] }
                    this.students = response.data.students;
                    this.selectedStudents = response.data.enrolled_ids.map(id => id.toString()); // Pastikan string untuk checkbox
                } catch (error) {
                    Swal.fire('Error', 'Gagal memuat data siswa', 'error');
                    this.showStudentModal = false;
                } finally {
                    this.isLoadingStudents = false;
                }
            },

            toggleSelectAll() {
                // Only toggle non-locked students from filtered list
                const availableStudents = this.filteredStudents.filter(s => !s.has_relations);
                const availableIds = availableStudents.map(s => s.id.toString());
                const allSelected = availableIds.every(id => this.selectedStudents.includes(id));
                
                if (allSelected) {
                    // Remove all available students from selection
                    this.selectedStudents = this.selectedStudents.filter(id => !availableIds.includes(id));
                } else {
                    // Add all available students to selection (keep locked ones)
                    const lockedIds = this.students.filter(s => s.has_relations && this.selectedStudents.includes(s.id.toString())).map(s => s.id.toString());
                    const newSelections = [...new Set([...this.selectedStudents, ...availableIds])];
                    this.selectedStudents = newSelections;
                }
            },

            async saveStudents() {
                this.isSavingStudents = true;
                try {
                    await axios.post(`/akademik/kelas/${this.currentKelasId}/siswa`, {
                        santri_ids: this.selectedStudents
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Data siswa berhasil disimpan',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    this.showStudentModal = false;
                } catch (error) {
                    Swal.fire('Error', error.response?.data?.message || 'Gagal menyimpan data siswa', 'error');
                } finally {
                    this.isSavingStudents = false;
                }
            },

            // === Manajemen Hapus ===
            async deleteKelas(kelasId) {
                const result = await Swal.fire({
                    title: 'Hapus Kelas?',
                    text: 'Data kelas akan dihapus permanen',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                });

                if (result.isConfirmed) {
                    try {
                        await axios.delete(`/akademik/kelas/${kelasId}`);
                        Swal.fire('Terhapus!', 'Kelas berhasil dihapus', 'success').then(() => {
                            location.reload();
                        });
                    } catch (error) {
                        Swal.fire('Error', error.response?.data?.message || 'Gagal menghapus kelas', 'error');
                    }
                }
            }
        }
    }
</script>
@endsection