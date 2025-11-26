@extends('layouts.app')

@section('title', 'Input Kehadiran')

@section('content')
<div x-data="absensiMapelHandler()" x-init="init()" class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Input Kehadiran</h2>
        <p class="text-sm text-gray-500 mt-1">Input absensi per mata pelajaran yang Anda ajar</p>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Pilih Kelas --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">1. Pilih Kelas</label>
                <select x-model="selectedKelasId" @change="filterMapel()" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">-- Pilih Kelas --</option>
                    <template x-for="k in uniqueKelas" :key="k.id">
                        <option :value="k.id" x-text="`Kelas ${k.level}`"></option>
                    </template>
                </select>
            </div>

            {{-- Pilih Mapel --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">2. Pilih Mata Pelajaran</label>
                <select x-model="selectedMapelId" @change="loadSantriMapel()" 
                    :disabled="!selectedKelasId"
                    class="w-full border-gray-300 rounded-md text-sm disabled:bg-gray-100">
                    <option value="">-- Pilih Mapel --</option>
                    <template x-for="m in filteredMapels" :key="m.id">
                        <option :value="m.id" x-text="m.mapel?.nama_mapel"></option>
                    </template>
                </select>
            </div>

            {{-- Cari Siswa --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Siswa</label>
                <input type="text" x-model="searchQuery" @input="filterSantri()" placeholder="Ketik nama siswa..." class="w-full border-gray-300 rounded-md text-sm">
            </div>

            {{-- Tanggal & Quick Actions --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Absensi</label>
                <input type="date" x-model="tanggalAbsensi" class="w-full border-gray-300 rounded-md text-sm mb-2">
                <button @click="tandaiSemuaHadir()" class="bg-green-500 text-white px-3 py-2 rounded text-sm hover:bg-green-600 w-full transition">
                    ✓ Tandai Semua Hadir
                </button>
            </div>
        </div>
    </div>

    {{-- Table Section --}}
    <div x-show="selectedMapelId" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider w-80">Siswa</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Input Pilihan</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider w-32">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="(santri, index) in filteredSantri" :key="santri.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div>
                                    <div class="font-medium text-gray-900" x-text="santri.nama"></div>
                                    <div class="text-xs text-gray-400" x-text="santri.nisn || 'NISN: -'"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input 
                                    type="text" 
                                    x-model="absensiData[santri.id].keterangan" 
                                    placeholder="Catatan..." 
                                    class="w-full border-gray-300 rounded text-xs text-center"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-2">
                                    <button 
                                        @click="setStatus(santri.id, 'H')"
                                        :class="absensiData[santri.id].status === 'H' ? 'bg-green-600 text-white shadow-lg transform scale-105' : 'bg-green-100 text-green-700 hover:bg-green-200'"
                                        class="px-3 py-2 rounded-lg text-xs font-bold transition-all"
                                    >Hadir</button>
                                    <button 
                                        @click="setStatus(santri.id, 'S')"
                                        :class="absensiData[santri.id].status === 'S' ? 'bg-blue-600 text-white shadow-lg transform scale-105' : 'bg-blue-100 text-blue-700 hover:bg-blue-200'"
                                        class="px-3 py-2 rounded-lg text-xs font-bold transition-all"
                                    >Sakit</button>
                                    <button 
                                        @click="setStatus(santri.id, 'I')"
                                        :class="absensiData[santri.id].status === 'I' ? 'bg-yellow-600 text-white shadow-lg transform scale-105' : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200'"
                                        class="px-3 py-2 rounded-lg text-xs font-bold transition-all"
                                    >Ijin</button>
                                    <button 
                                        @click="setStatus(santri.id, 'A')"
                                        :class="absensiData[santri.id].status === 'A' ? 'bg-red-600 text-white shadow-lg transform scale-105' : 'bg-red-100 text-red-700 hover:bg-red-200'"
                                        class="px-3 py-2 rounded-lg text-xs font-bold transition-all"
                                    >Alpha</button>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span 
                                    class="px-4 py-2 rounded-full text-xs font-bold"
                                    :class="getStatusBadgeClass(absensiData[santri.id].status)"
                                    x-text="getStatusLabel(absensiData[santri.id].status)"
                                ></span>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="filteredSantri.length === 0">
                        <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                            <div class="text-4xl mb-3">📝</div>
                            <div x-show="!selectedMapelId">Silakan pilih Kelas dan Mata Pelajaran terlebih dahulu</div>
                            <div x-show="selectedMapelId && allSantri.length === 0">Tidak ada siswa terdaftar di kelas ini</div>
                            <div x-show="selectedMapelId && allSantri.length > 0 && filteredSantri.length === 0">Tidak ada siswa yang cocok dengan pencarian</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Action Footer --}}
        <div x-show="selectedMapelId && filteredSantri.length > 0" class="border-t border-gray-200 bg-gray-50 px-4 py-3 flex justify-between items-center sticky bottom-0 z-10">
            <div class="text-sm text-gray-600">
                Total Siswa: <span class="font-bold" x-text="filteredSantri.length"></span>
            </div>
            <div class="flex gap-2">
                <button @click="resetAbsensi()" class="bg-orange-500 text-white px-4 py-2 rounded-lg text-sm hover:bg-orange-600 transition shadow-sm">
                    <i class="fas fa-undo"></i> Reset Absensi
                </button>
                <button @click="resetForm()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition shadow-sm">
                    Reset Form
                </button>
                <button @click="saveAbsensi()" :disabled="isSaving" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50 transition shadow-md flex items-center gap-2">
                    <span x-show="!isSaving">Simpan Absensi</span>
                    <span x-show="isSaving" class="flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin"></i> Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function absensiMapelHandler() {
        return {
            guruMapels: @json($guruMapels ?? []),
            uniqueKelas: [],
            filteredMapels: [],
            
            selectedKelasId: '',
            selectedMapelId: '{{ $preSelectedMapelId ?? "" }}',
            
            allSantri: [],
            filteredSantri: [],
            searchQuery: '',
            absensiData: {},
            isSaving: false,
            tanggalAbsensi: new Date().toISOString().split('T')[0],

            init() {
                // Extract unique kelas from guruMapels
                const kelasMap = new Map();
                this.guruMapels.forEach(gm => {
                    if (gm.kelas && !kelasMap.has(gm.kelas.id)) {
                        kelasMap.set(gm.kelas.id, gm.kelas);
                    }
                });
                this.uniqueKelas = Array.from(kelasMap.values()).sort((a, b) => a.level - b.level);

                // If pre-selected mapel exists
                if (this.selectedMapelId) {
                    const gm = this.guruMapels.find(g => g.id == this.selectedMapelId);
                    if (gm) {
                        this.selectedKelasId = gm.kelas_id;
                        this.filterMapel();
                        this.loadSantriMapel();
                    }
                }
                
                this.$watch('tanggalAbsensi', () => {
                    if (this.selectedMapelId) {
                        this.loadSantriMapel();
                    }
                });
            },

            filterMapel() {
                this.selectedMapelId = '';
                this.filteredMapels = [];
                this.allSantri = [];
                this.filteredSantri = [];
                
                if (this.selectedKelasId) {
                    this.filteredMapels = this.guruMapels.filter(gm => gm.kelas_id == this.selectedKelasId);
                }
            },

            async loadSantriMapel() {
                if (!this.selectedMapelId) {
                    return;
                }

                try {
                    const response = await axios.get(`/akademik/absensi/${this.selectedMapelId}/santri`, {
                        params: { date: this.tanggalAbsensi }
                    });
                    this.allSantri = response.data.santri || [];
                    this.filteredSantri = this.allSantri;
                    
                    const existing = response.data.existingAttendance || {};
                    const hasExisting = Object.keys(existing).length > 0;

                    // Initialize absensi data
                    this.absensiData = {};
                    this.allSantri.forEach(s => {
                        if (existing[s.id]) {
                            this.absensiData[s.id] = { 
                                status: existing[s.id].status, 
                                keterangan: existing[s.id].keterangan || '' 
                            };
                        } else {
                            this.absensiData[s.id] = { status: '', keterangan: '' };
                        }
                    });
                    
                    if (hasExisting) {
                        Swal.fire({
                            title: 'Sudah Diisi!',
                            text: 'Anda sudah mengisi absensi untuk kelas dan tanggal ini.',
                            icon: 'info',
                            confirmButtonText: 'Lihat / Edit',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                    
                } catch (error) {
                    console.error('Error loading santri:', error);
                    Swal.fire('Error', 'Gagal memuat data siswa', 'error');
                }
            },

            setStatus(santriId, status) {
                this.absensiData[santriId].status = status;
            },

            tandaiSemuaHadir() {
                if (!this.selectedMapelId) {
                    Swal.fire('Peringatan', 'Silakan pilih mata pelajaran terlebih dahulu', 'warning');
                    return;
                }

                this.filteredSantri.forEach(s => {
                    if (!this.absensiData[s.id].status) {
                        this.absensiData[s.id].status = 'H';
                    }
                });
            },

            getStatusBadgeClass(status) {
                const classes = {
                    'H': 'bg-green-100 text-green-700',
                    'S': 'bg-blue-100 text-blue-700',
                    'I': 'bg-yellow-100 text-yellow-700',
                    'A': 'bg-red-100 text-red-700'
                };
                return classes[status] || 'bg-gray-100 text-gray-500';
            },

            getStatusLabel(status) {
                const labels = {
                    'H': 'Hadir',
                    'S': 'Sakit',
                    'I': 'Ijin',
                    'A': 'Alpha'
                };
                return labels[status] || '-';
            },

            resetForm() {
                Object.keys(this.absensiData).forEach(id => {
                    this.absensiData[id] = { status: '', keterangan: '' };
                });
            },

            async resetAbsensi() {
                if (!this.selectedMapelId) {
                    Swal.fire('Peringatan', 'Silakan pilih mata pelajaran terlebih dahulu', 'warning');
                    return;
                }

                const result = await Swal.fire({
                    title: 'Reset Absensi?',
                    text: `Hapus semua data absensi untuk tanggal ${this.tanggalAbsensi}?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                });

                if (!result.isConfirmed) return;

                try {
                    const response = await axios.post('{{ route("akademik.absensi.reset") }}', {
                        guru_mapel_id: this.selectedMapelId,
                        tanggal: this.tanggalAbsensi,
                        _token: '{{ csrf_token() }}'
                    });

                    Swal.fire('Berhasil', 'Data absensi berhasil direset!', 'success');
                    this.loadSantriMapel(); // Reload data
                } catch (error) {
                    console.error('Error:', error);
                    Swal.fire('Error', 'Gagal mereset absensi', 'error');
                }
            },

            async saveAbsensi() {
                if (!this.selectedMapelId) {
                    Swal.fire('Peringatan', 'Silakan pilih mata pelajaran terlebih dahulu', 'warning');
                    return;
                }

                // Validate if all students have status
                const incomplete = this.filteredSantri.some(s => !this.absensiData[s.id].status);
                if (incomplete) {
                    const result = await Swal.fire({
                        title: 'Belum Lengkap',
                        text: "Beberapa siswa belum memiliki status kehadiran. Siswa tanpa status akan dianggap ALPHA. Lanjutkan?",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Ya, Simpan',
                        cancelButtonText: 'Batal'
                    });
                    if (!result.isConfirmed) return;
                }

                const absensiArray = Object.keys(this.absensiData).map(santriId => ({
                    id: parseInt(santriId),
                    kehadiran: this.absensiData[santriId].status || 'A'
                }));

                this.isSaving = true;

                try {
                    const response = await axios.post('{{ route('akademik.absensi.store') }}', {
                        guru_mapel_id: this.selectedMapelId,
                        tanggal_absensi: this.tanggalAbsensi,
                        absensi: absensiArray,
                        _token: '{{ csrf_token() }}'
                    });

                    Swal.fire('Sukses', 'Absensi berhasil disimpan!', 'success');
                } catch (error) {
                    console.error('Error:', error);
                    let msg = 'Gagal menyimpan absensi';
                    if (error.response && error.response.data && error.response.data.message) {
                        msg += ': ' + error.response.data.message;
                    }
                    Swal.fire('Error', msg, 'error');
                } finally {
                    this.isSaving = false;
                }
            },
            
            filterSantri() {
                if (!this.searchQuery) {
                    this.filteredSantri = this.allSantri;
                    return;
                }
                const query = this.searchQuery.toLowerCase();
                this.filteredSantri = this.allSantri.filter(s => 
                    s.nama.toLowerCase().includes(query)
                );
            }
        }
    }
</script>
@endsection
