@extends('layouts.app')

@section('title', 'Kelas Saya')

@section('content')
<div x-data="guruMapelData()" class="container mx-auto p-6">
    
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Kelas Saya</h1>
            <p class="text-gray-500 mt-1">Kelola kelas dan mata pelajaran yang Anda ampu.</p>
        </div>
        <div class="flex gap-3">
            <button @click="openModal()" 
                class="bg-blue-600 text-white px-6 py-3 rounded-xl shadow-lg hover:bg-blue-700 transition flex items-center gap-2 transform hover:scale-105 duration-200">
                <i class="fas fa-plus-circle text-lg"></i> 
                <span class="font-semibold">Tambah Mapel Ajar</span>
            </button>
        </div>
    </div>

    {{-- EMPTY STATE --}}
    <template x-if="guruMapels.length === 0">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="bg-blue-50 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-chalkboard-teacher text-3xl text-blue-500"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Kelas</h3>
            <p class="text-gray-500 mb-6 max-w-md mx-auto">Anda belum menambahkan mata pelajaran yang Anda ajar. Silakan tambahkan untuk mulai mengelola nilai dan absensi.</p>
            <button @click="openModal()" class="text-blue-600 font-semibold hover:text-blue-700 hover:underline">
                + Tambah Mapel Sekarang
            </button>
        </div>
    </template>

    {{-- GRID CARD LAYOUT - LEBIH INFORMATIF --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="(gm, index) in guruMapels" :key="gm.id">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-xl transition duration-300 overflow-hidden group">
                {{-- Card Header dengan Gradient --}}
                <div class="p-6 bg-gradient-to-br from-blue-500 to-blue-600 text-white">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-book text-xl"></i>
                            </div>
                            <div class="bg-white/20 backdrop-blur px-3 py-1 rounded-lg text-xs font-bold uppercase tracking-wide">
                                <span x-text="gm.semester == 'ganjil' ? 'Ganjil' : 'Genap'"></span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button @click="deleteGuruMapel(gm.id)" class="w-8 h-8 bg-white/10 hover:bg-red-500 rounded-lg flex items-center justify-center transition" title="Hapus Kelas">
                                <i class="fas fa-trash-alt text-sm"></i>
                            </button>
                        </div>
                    </div>
                    
                    <h3 class="text-2xl font-bold mb-2" x-text="gm.mapel?.nama_mapel"></h3>
                    <div class="flex items-center gap-2 text-blue-100">
                        <i class="fas fa-school text-sm"></i>
                        <span class="font-medium text-base" x-text="gm.kelas ? 'Kelas ' + gm.kelas.level : '-'"></span>
                    </div>
                </div>

                {{-- Card Body - INFORMASI LENGKAP --}}
                <div class="p-6">
                    {{-- Tahun Ajaran --}}
                    <div class="flex items-center justify-between text-sm mb-4 pb-4 border-b border-gray-100">
                        <span class="text-gray-500 flex items-center gap-2">
                            <i class="fas fa-calendar-alt text-gray-400"></i>
                            Tahun Ajaran
                        </span>
                        <span class="font-bold text-gray-800" x-text="gm.tahun_ajaran"></span>
                    </div>

                    {{-- Statistik Grid --}}
                    <div class="grid grid-cols-2 gap-3 mb-5">
                        {{-- Jumlah Siswa --}}
                        <div class="bg-blue-50 rounded-xl p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fas fa-users text-blue-600 text-sm"></i>
                                <span class="text-xs text-gray-600">Siswa</span>
                            </div>
                            <div class="text-2xl font-bold text-blue-700" x-text="gm.jumlah_siswa || 0"></div>
                        </div>

                        {{-- Jumlah Pertemuan --}}
                        <div class="bg-green-50 rounded-xl p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fas fa-clipboard-check text-green-600 text-sm"></i>
                                <span class="text-xs text-gray-600">Pertemuan</span>
                            </div>
                            <div class="text-2xl font-bold text-green-700" x-text="gm.jumlah_pertemuan || 0"></div>
                        </div>

                        {{-- Rata-rata Nilai --}}
                        <div class="bg-yellow-50 rounded-xl p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fas fa-star text-yellow-600 text-sm"></i>
                                <span class="text-xs text-gray-600">Rata Nilai</span>
                            </div>
                            <div class="text-2xl font-bold" :class="gm.rata_rata_nilai >= 75 ? 'text-green-600' : 'text-yellow-600'" x-text="gm.rata_rata_nilai ? gm.rata_rata_nilai : '-'"></div>
                        </div>

                        {{-- Siswa Dinilai --}}
                        <div class="bg-purple-50 rounded-xl p-3">
                            <div class="flex items-center gap-2 mb-1">
                                <i class="fas fa-check-circle text-purple-600 text-sm"></i>
                                <span class="text-xs text-gray-600">Dinilai</span>
                            </div>
                            <div class="text-2xl font-bold text-purple-700">
                                <span x-text="gm.siswa_dinilai || 0"></span><span class="text-sm text-gray-500">/<span x-text="gm.jumlah_siswa || 0"></span></span>
                            </div>
                        </div>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="grid grid-cols-1 gap-2">
                        <a :href="`/akademik/guru-mapel/${gm.id}/rekap`" 
                           class="flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white py-3 rounded-xl hover:from-blue-600 hover:to-blue-700 transition font-medium shadow-md">
                            <i class="fas fa-chart-pie"></i> Rekap Lengkap
                        </a>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="resetAbsensi(gm.id)" 
                                class="flex items-center justify-center gap-2 border border-orange-200 text-orange-600 py-2.5 rounded-xl hover:bg-orange-50 hover:border-orange-300 transition text-sm font-medium">
                                <i class="fas fa-history"></i> Reset Absen
                            </button>
                            <button @click="clearGrades(gm.id)" 
                                class="flex items-center justify-center gap-2 border border-red-200 text-red-600 py-2.5 rounded-xl hover:bg-red-50 hover:border-red-300 transition text-sm font-medium">
                                <i class="fas fa-eraser"></i> Reset Nilai
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- MODAL TAMBAH MAPEL --}}
    <div x-show="showModal" 
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" 
        style="display: none;">
        
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity" @click="showModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-book-open text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                Tambah Mapel yang Diajar
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-6">
                                    Silakan lengkapi form di bawah ini untuk menambahkan kelas baru.
                                </p>

                                <div class="space-y-5">
                                    {{-- Pilih Kelas DULU --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">1. Pilih Kelas</label>
                                        <select x-model="form.kelas_id" @change="filterMapelByKelas()"
                                            class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-3 text-base">
                                            <option value="">-- Pilih Kelas --</option>
                                            <template x-for="kls in kelas" :key="kls.id">
                                                <option :value="kls.id" x-text="'Kelas ' + kls.level"></option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Pilih Mapel - FILTERED BY KELAS --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">2. Pilih Mata Pelajaran</label>
                                        <select x-model="form.mapel_id" 
                                            :disabled="!form.kelas_id"
                                            class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-3 text-base disabled:bg-gray-100 disabled:text-gray-400">
                                            <option value="">-- Pilih Mapel --</option>
                                            <template x-for="mapel in filteredMapels" :key="mapel.id">
                                                <option :value="mapel.id" x-text="mapel.nama_mapel"></option>
                                            </template>
                                        </select>
                                        <p x-show="!form.kelas_id" class="text-xs text-orange-500 mt-1">* Pilih Kelas terlebih dahulu</p>
                                        <p x-show="form.kelas_id && filteredMapels.length === 0" class="text-xs text-red-500 mt-1">* Semua mapel untuk kelas ini sudah Anda ambil.</p>
                                    </div>

                                    {{-- Auto-filled Info from Active Year --}}
                                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                                        <div class="flex items-center gap-2 mb-2">
                                            <i class="fas fa-info-circle text-blue-600"></i>
                                            <span class="text-sm font-semibold text-gray-700">Tahun Ajaran Aktif</span>
                                        </div>
                                        <div class="space-y-1">
                                            <div class="text-base font-bold text-blue-700" x-text="activeYearDisplay"></div>
                                            <div class="flex items-center gap-3">
                                                <div class="text-sm text-blue-600" x-text="'Semester ' + (form.semester == 'ganjil' ? 'Ganjil' : 'Genap')"></div>
                                                <span class="text-blue-400">•</span>
                                                <div class="text-sm text-blue-600" x-show="activeYearJenjang" x-text="'Jenjang: ' + activeYearJenjang"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="saveGuruMapel()" 
                        type="button" 
                        class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                        Simpan Data
                    </button>
                    <button @click="showModal = false" 
                        type="button" 
                        class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-3 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function guruMapelData() {
    const activeYear = @json(\App\Models\Akademik\TahunAjaran::active()->first());
    
    return {
        guruMapels: @json($guruMapels),
        mapels: @json($mapels),
        kelas: @json($kelas),
        filteredMapels: [],
        showModal: false,
        activeYearDisplay: activeYear ? activeYear.nama : 'Tidak Ada',
        activeYearJenjang: activeYear ? activeYear.jenjang : null,
        form: {
            kelas_id: '',
            mapel_id: '',
            semester: activeYear ? activeYear.semester : 'ganjil',
            tahun_ajaran: activeYear ? activeYear.nama : ''
        },

        init() {
            console.log('Active Year:', activeYear);
            console.log('Guru Mapels with Stats:', this.guruMapels);
        },

        openModal() {
            this.form.kelas_id = '';
            this.form.mapel_id = '';
            this.filteredMapels = [];
            this.showModal = true;
        },

        // FILTER: Pilih KELAS dulu, baru tampilkan MAPEL yang belum diambil untuk kelas itu
        filterMapelByKelas() {
            if (!this.form.kelas_id) {
                this.filteredMapels = [];
                this.form.mapel_id = '';
                return;
            }

            // Ambil semua mapel yang SUDAH diambil untuk kelas ini oleh guru ini
            const takenMapelIds = this.guruMapels
                .filter(gm => gm.kelas_id == this.form.kelas_id)
                .map(gm => gm.mapel_id);

            // Find selected class level
            const selectedKelas = this.kelas.find(k => k.id == this.form.kelas_id);
            const level = selectedKelas ? selectedKelas.level : null;

            // Filter mapel: tampilkan hanya yang BELUM diambil untuk kelas ini DAN sesuai tingkat
            this.filteredMapels = this.mapels.filter(m => {
                // 1. Check if already taken
                if (takenMapelIds.includes(m.id)) return false;
                
                // 2. Check if mapel targets this level
                if (level && m.tingkat && Array.isArray(m.tingkat) && m.tingkat.length > 0) {
                    return m.tingkat.map(t => t.toString()).includes(level.toString());
                }
                
                // If mapel has no specific level (empty array or null), show it (optional, depends on business rule)
                // Assuming if no level specified, it's available for all or none. Let's assume available.
                return true;
            });
            
            // Reset mapel selection if currently selected mapel is not available
            if (this.form.mapel_id && !this.filteredMapels.find(m => m.id == this.form.mapel_id)) {
                this.form.mapel_id = '';
            }
        },

        async saveGuruMapel() {
            if (!this.form.kelas_id || !this.form.mapel_id || !this.form.semester || !this.form.tahun_ajaran) {
                Swal.fire('Error', 'Semua field harus diisi', 'error');
                return;
            }

            try {
                const response = await fetch('{{ route("akademik.guru-mapel.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(this.form)
                });

                const result = await response.json();

                if (response.ok) {
                    Swal.fire('Sukses', result.message, 'success').then(() => {
                        location.reload(); // Reload untuk refresh statistik
                    });
                } else {
                    Swal.fire('Gagal', result.message || 'Gagal menambahkan mapel', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan: ' + error.message, 'error');
            }
        },

        async deleteGuruMapel(id) {
            const result = await Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Anda akan menghapus kelas ini dari daftar ajar Anda.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`{{ url('akademik/guru-mapel') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire('Terhapus!', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal menghapus mapel', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan: ' + error.message, 'error');
            }
        },

        async clearGrades(id) {
            const result = await Swal.fire({
                title: 'Hapus Semua Nilai?',
                text: "Tindakan ini akan menghapus SEMUA data penilaian untuk kelas ini. Tidak dapat dibatalkan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Reset Nilai!',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`{{ url('akademik/guru-mapel') }}/${id}/clear-grades`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire('Berhasil', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal menghapus penilaian', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan: ' + error.message, 'error');
            }
        },

        async resetAbsensi(id) {
            const result = await Swal.fire({
                title: 'Reset Kehadiran?',
                text: "Semua data kehadiran untuk kelas ini akan dihapus. Lanjutkan?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f97316',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Reset Absen!',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`{{ url('akademik/guru-mapel') }}/${id}/reset-absensi`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const data = await response.json();

                if (response.ok) {
                    Swal.fire('Berhasil', data.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Gagal', data.message || 'Gagal mereset absensi', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Terjadi kesalahan: ' + error.message, 'error');
            }
        }
    };
}
</script>
@endsection
