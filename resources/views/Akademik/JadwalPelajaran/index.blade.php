@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@section('content')
<div x-data="jadwalPelajaranData()" class="bg-gray-50 min-h-screen p-6">
    
    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Manajemen Jadwal Pelajaran</h1>
            <p class="text-gray-500 mt-1">Kelola jadwal mengajar per hari dan jam pelajaran</p>
        </div>
        <button @click="openModal()" 
            class="bg-blue-600 text-white px-6 py-3 rounded-xl shadow-lg hover:bg-blue-700 transition flex items-center gap-2 transform hover:scale-105 duration-200">
            <i class="fas fa-plus-circle text-lg"></i> 
            <span class="font-semibold">Tambah Jadwal</span>
        </button>
    </div>

    {{-- FILTER & TABS CONTAINER --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-6">
        
        {{-- FILTER SECTION --}}
        <div class="flex flex-col md:flex-row gap-4 mb-6 border-b border-gray-100 pb-4">
            <div class="w-full md:w-1/3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Kelas</label>
                <select x-model="filterKelas" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Kelas</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->level }}">Kelas {{ $k->level }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-1/3">
                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Filter Mapel</label>
                <select x-model="filterMapel" class="w-full border-gray-200 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">Semua Mata Pelajaran</option>
                    @foreach($mapels as $m)
                        <option value="{{ $m->nama_mapel }}">{{ $m->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- TAB HARI (No Scroll Icon) --}}
        <div class="overflow-x-auto no-scrollbar pb-2">
            <div class="flex gap-2 min-w-max">
                <template x-for="hari in hariOptions" :key="hari">
                    <button 
                        @click="selectedHari = hari"
                        :class="selectedHari === hari ? 'bg-blue-600 text-white shadow-md ring-2 ring-blue-200' : 'bg-gray-50 text-gray-600 hover:bg-gray-100 border border-gray-200'"
                        class="px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 text-sm flex items-center gap-2"
                    >
                        <i class="fas fa-calendar-day text-xs" :class="selectedHari === hari ? 'text-blue-200' : 'text-gray-400'"></i>
                        <span x-text="hari"></span>
                        {{-- Badge Count --}}
                        <span class="ml-1 px-1.5 py-0.5 rounded-md text-[10px]" 
                              :class="selectedHari === hari ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-500'"
                              x-text="getFilteredCount(hari)">
                        </span>
                    </button>
                </template>
            </div>
        </div>
    </div>

    {{-- GRID JADWAL PER HARI--}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-clock text-blue-600"></i>
                    Jadwal <span x-text="selectedHari" class="text-blue-600"></span>
                </h2>
                <div class="text-sm text-gray-500">
                    Menampilkan <span class="font-bold text-gray-800" x-text="getFilteredJadwal(selectedHari).length"></span> jadwal
                </div>
            </div>

            {{-- Table Jadwal --}}
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Jam Ke</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mata Pelajaran</th>
                            <th class="px-4 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Guru</th>
                            <th class="px-4 py-3 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <template x-for="jadwal in getFilteredJadwal(selectedHari)" :key="jadwal.id">
                            <tr class="hover:bg-blue-50 transition group" :class="{'bg-yellow-50': jadwal.jenis_kegiatan !== 'KBM'}">
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center transition" :class="jadwal.jenis_kegiatan === 'KBM' ? 'bg-blue-100 group-hover:bg-blue-200' : 'bg-yellow-100 group-hover:bg-yellow-200'">
                                            <span class="font-bold text-sm" :class="jadwal.jenis_kegiatan === 'KBM' ? 'text-blue-700' : 'text-yellow-700'" x-text="jadwal.jam_ke"></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    <div class="flex items-center gap-2 bg-gray-50 px-2 py-1 rounded border border-gray-100 w-max">
                                        <i class="fas fa-clock text-gray-400 text-xs"></i>
                                        <span x-text="jadwal.jam_mulai.substring(0,5) + ' - ' + jadwal.jam_selesai.substring(0,5)" class="font-mono text-xs"></span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <template x-if="jadwal.jenis_kegiatan === 'KBM'">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span x-text="'Kelas ' + jadwal.kelas.level"></span>
                                        </span>
                                    </template>
                                    <template x-if="jadwal.jenis_kegiatan !== 'KBM'">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <span x-text="jadwal.jenjang || 'Semua'"></span>
                                        </span>
                                    </template>
                                </td>
                                <td class="px-4 py-3">
                                    <template x-if="jadwal.jenis_kegiatan === 'KBM'">
                                        <div>
                                            <div class="font-semibold text-gray-800" x-text="jadwal.mapel.nama_mapel"></div>
                                            <div class="text-xs text-gray-500" x-text="jadwal.semester + ' ' + jadwal.tahun_ajaran"></div>
                                        </div>
                                    </template>
                                    <template x-if="jadwal.jenis_kegiatan !== 'KBM'">
                                        <div>
                                            <div class="font-bold text-gray-800" x-text="jadwal.nama_kegiatan || jadwal.jenis_kegiatan"></div>
                                            <div class="text-xs text-gray-500 italic" x-text="jadwal.jenis_kegiatan"></div>
                                        </div>
                                    </template>
                                </td>
                                <td class="px-4 py-3">
                                    <template x-if="jadwal.jenis_kegiatan === 'KBM'">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 bg-purple-100 rounded-full flex items-center justify-center text-xs">
                                                <i class="fas fa-user text-purple-600"></i>
                                            </div>
                                            <span class="text-sm text-gray-700 truncate max-w-[150px]" x-text="jadwal.guru_profile ? jadwal.guru_profile.nama : '-'" :title="jadwal.guru_profile ? jadwal.guru_profile.nama : ''"></span>
                                        </div>
                                    </template>
                                    <template x-if="jadwal.jenis_kegiatan !== 'KBM'">
                                        <span class="text-gray-400 text-sm">-</span>
                                    </template>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editJadwal(jadwal)" class="p-1.5 text-blue-600 hover:bg-blue-100 rounded-lg transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button @click="deleteJadwal(jadwal.id)" class="p-1.5 text-red-600 hover:bg-red-100 rounded-lg transition" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                        <tr x-show="getFilteredJadwal(selectedHari).length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                        <i class="fas fa-search text-gray-300 text-2xl"></i>
                                    </div>
                                    <p class="text-lg font-medium text-gray-600">Tidak ada jadwal ditemukan</p>
                                    <p class="text-sm text-gray-400 mt-1">Coba ubah filter atau pilih hari lain</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL TAMBAH/EDIT JADWAL --}}
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

            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-calendar-plus text-blue-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">
                                <span x-text="isEditMode ? 'Edit Jadwal' : 'Tambah Jadwal Baru'"></span>
                            </h3>
                            <div class="mt-4">
                                <div class="grid grid-cols-2 gap-4">
                                    {{-- Jenis Kegiatan --}}
                                    <div class="col-span-2">
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Kegiatan</label>
                                        <select x-model="form.jenis_kegiatan" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm">
                                            <option value="KBM">KBM (Kegiatan Belajar Mengajar)</option>
                                            <option value="Upacara">Upacara</option>
                                            <option value="Apel">Apel Pagi</option>
                                            <option value="Istirahat">Istirahat</option>
                                            <option value="Ekstrakurikuler">Ekstrakurikuler</option>
                                            <option value="Lainnya">Lainnya</option>
                                        </select>
                                    </div>

                                    {{-- FIELDS FOR KBM --}}
                                    <div class="col-span-2 grid grid-cols-2 gap-4" x-show="form.jenis_kegiatan === 'KBM'">
                                        {{-- Pilih Kelas --}}
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Kelas</label>
                                            <select x-model="form.kelas_id" @change="loadGuruMapels()" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm">
                                                <option value="">-- Pilih Kelas --</option>
                                                @foreach($kelas as $k)
                                                    <option value="{{ $k->id }}">Kelas {{ $k->level }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Pilih Mapel --}}
                                        <div>
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Mapel</label>
                                            <select x-model="form.mapel_id" @change="loadGuruMapels()" :disabled="!form.kelas_id" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm disabled:bg-gray-100">
                                                <option value="">-- Pilih Mapel --</option>
                                                @foreach($mapels as $m)
                                                    <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- Pilih Guru (Auto dari GuruMapel) --}}
                                        <div class="col-span-2">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Guru</label>
                                            <select x-model="form.guru_mapel_id" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm">
                                                <option value="">-- Pilih Guru --</option>
                                                <template x-for="gm in availableGuruMapels" :key="gm.id">
                                                    <option :value="gm.id" x-text="gm.guru_profile ? (gm.guru_profile.nama + ' (' + gm.semester + ' - ' + gm.tahun_ajaran + ')') : 'Guru Tidak Ditemukan'"></option>
                                                </template>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- FIELDS FOR NON-KBM --}}
                                    <div class="col-span-2 grid grid-cols-2 gap-4" x-show="form.jenis_kegiatan !== 'KBM'">
                                        <div class="col-span-2">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Nama Kegiatan</label>
                                            <input type="text" x-model="form.nama_kegiatan" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm" placeholder="Contoh: Upacara Bendera Senin">
                                        </div>
                                        <div class="col-span-2">
                                            <label class="block text-sm font-bold text-gray-700 mb-2">Jenjang</label>
                                            <select x-model="form.jenjang" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm">
                                                <option value="SMP">SMP</option>
                                                <option value="SMA">SMA</option>
                                            </select>
                                        </div>
                                    </div>

                                    {{-- Hari --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Hari</label>
                                        <select x-model="form.hari" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm">
                                            <template x-for="hari in hariOptions" :key="hari">
                                                <option :value="hari" x-text="hari"></option>
                                            </template>
                                        </select>
                                    </div>

                                    {{-- Jam Ke --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Jam Ke</label>
                                        <input type="number" x-model="form.jam_ke" min="1" max="12" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm" placeholder="1-12">
                                    </div>

                                    {{-- Jam Mulai --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Jam Mulai</label>
                                        <input type="time" x-model="form.jam_mulai" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm">
                                    </div>

                                    {{-- Jam Selesai --}}
                                    <div>
                                        <label class="block text-sm font-bold text-gray-700 mb-2">Jam Selesai</label>
                                        <input type="time" x-model="form.jam_selesai" class="w-full border-gray-300 rounded-xl shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 text-sm">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="saveJadwal()" 
                        type="button" 
                        class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-3 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition">
                        <span x-text="isEditMode ? 'Update' : 'Simpan'"></span>
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
function jadwalPelajaranData() {
    return {
        jadwals: @json($jadwalGrouped),
        hariOptions: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
        selectedHari: 'Senin',
        filterKelas: '',
        filterMapel: '',
        showModal: false,
        isEditMode: false,
        editId: null,
        availableGuruMapels: [],
        form: {
            jenis_kegiatan: 'KBM',
            nama_kegiatan: '',
            jenjang: 'SMP',
            guru_mapel_id: '',
            kelas_id: '',
            mapel_id: '',
            hari: 'Senin',
            jam_ke: 1,
            jam_mulai: '07:00',
            jam_selesai: '07:45',
        },

        init() {
            console.log('Jadwals:', this.jadwals);
        },

        getFilteredJadwal(hari) {
            let list = this.jadwals[hari] || [];
            
            if (this.filterKelas) {
                list = list.filter(j => j.kelas && j.kelas.level == this.filterKelas);
            }
            
            if (this.filterMapel) {
                list = list.filter(j => j.mapel && j.mapel.nama_mapel == this.filterMapel);
            }
            
            return list;
        },

        getFilteredCount(hari) {
            return this.getFilteredJadwal(hari).length;
        },

        openModal() {
            this.isEditMode = false;
            this.resetForm();
            this.showModal = true;
        },

        resetForm() {
            this.form = {
                jenis_kegiatan: 'KBM',
                nama_kegiatan: '',
                jenjang: 'SMP',
                guru_mapel_id: '',
                kelas_id: '',
                mapel_id: '',
                hari: this.selectedHari,
                jam_ke: 1,
                jam_mulai: '07:00',
                jam_selesai: '07:45',
            };
            this.availableGuruMapels = [];
        },

        async loadGuruMapels() {
            if (!this.form.kelas_id || !this.form.mapel_id) {
                this.availableGuruMapels = [];
                return;
            }

            try {
                const response = await axios.get('{{ route("akademik.jadwal-pelajaran.guruMapels") }}', {
                    params: {
                        kelas_id: this.form.kelas_id,
                        mapel_id: this.form.mapel_id
                    }
                });
                this.availableGuruMapels = response.data;
            } catch (error) {
                console.error('Error loading guru mapels:', error);
            }
        },

        async saveJadwal() {
            if (!this.form.guru_mapel_id) {
                Swal.fire('Error', 'Semua field harus diisi', 'error');
                return;
            }

            try {
                const url = this.isEditMode 
                    ? `{{ url('akademik/jadwal-pelajaran') }}/${this.editId}`
                    : '{{ route("akademik.jadwal-pelajaran.store") }}';
                
                const method = this.isEditMode ? 'PUT' : 'POST';

                const response = await axios({
                    method: method,
                    url: url,
                    data: this.form,
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                Swal.fire('Sukses', response.data.message, 'success').then(() => {
                    location.reload();
                });
            } catch (error) {
                console.error('Error:', error);
                const message = error.response?.data?.message || 'Terjadi kesalahan';
                Swal.fire('Error', message, 'error');
            }
        },

        editJadwal(jadwal) {
            this.isEditMode = true;
            this.editId = jadwal.id;
            this.form = {
                jenis_kegiatan: jadwal.jenis_kegiatan || 'KBM',
                nama_kegiatan: jadwal.nama_kegiatan || '',
                jenjang: jadwal.jenjang || 'SMP',
                guru_mapel_id: jadwal.guru_mapel_id,
                kelas_id: jadwal.kelas_id,
                mapel_id: jadwal.mapel_id,
                hari: jadwal.hari,
                jam_ke: jadwal.jam_ke,
                jam_mulai: jadwal.jam_mulai,
                jam_selesai: jadwal.jam_selesai,
            };
            this.showModal = true;
            if (this.form.jenis_kegiatan === 'KBM') {
                this.loadGuruMapels();
            }
        },

        async deleteJadwal(id) {
            const result = await Swal.fire({
                title: 'Hapus Jadwal?',
                text: "Jadwal akan dihapus permanent!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await axios.delete(`{{ url('akademik/jadwal-pelajaran') }}/${id}`, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                Swal.fire('Terhapus!', response.data.message, 'success').then(() => {
                    location.reload();
                });
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Gagal menghapus jadwal', 'error');
            }
        }
    };
}
</script>
@endsection
