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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Pilih Mapel --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Kelas</label>
                <select x-model="selectedMapelId" @change="loadSantriMapel()" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <template x-for="mapel in guruMapels" :key="mapel.id">
                        <option :value="mapel.id" x-text="`${mapel.mapel?.nama_mapel || 'N/A'} - Kelas ${mapel.kelas?.level || '?'}${mapel.kelas?.nama_unik || ''}`"></option>
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
                <button @click="tandaiSemuaHadir()" class="bg-green-500 text-white px-3 py-2 rounded text-sm hover:bg-green-600 w-full">
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
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider w-24">Elastis</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-for="(santri, index) in filteredSantri" :key="santri.id">
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3">
                                <div>
                                    <div class="font-medium text-gray-900" x-text="santri.nama"></div>
                                    <div class="text-xs text-gray-400" x-text="santri.nisn || 'X STN'"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input 
                                    type="text" 
                                    x-model="absensiData[santri.id].keterangan" 
                                    placeholder="Kosong atau minta..." 
                                    class="w-full border-gray-300 rounded text-xs text-center"
                                >
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-center gap-1">
                                    <template x-for="status in ['H', 'S', 'I', 'A']" :key="status">
                                        <button 
                                            @click="setStatus(santri.id, status)"
                                            :class="absensiData[santri.id].status === status ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                            class="px-2 py-1 rounded text-xs font-medium min-w-[32px] transition"
                                            x-text="status"
                                        ></button>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span 
                                    class="px-2 py-1 rounded text-xs font-bold"
                                    :class="getStatusBadgeClass(absensiData[santri.id].status)"
                                    x-text="absensiData[santri.id].status || '-'"
                                ></span>
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="filteredSantri.length === 0">
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            <div class="text-lg mb-2">📚</div>
                            <div x-show="!selectedMapelId">Silakan pilih mata pelajaran terlebih dahulu</div>
                            <div x-show="selectedMapelId && allSantri.length === 0">Tidak ada siswa terdaftar di mapel ini</div>
                            <div x-show="selectedMapelId && allSantri.length > 0 && filteredSantri.length === 0">Tidak ada siswa yang cocok dengan pencarian</div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- Action Footer --}}
        <div x-show="selectedMapelId && filteredSantri.length > 0" class="border-t border-gray-200 bg-gray-50 px-4 py-3 flex justify-between items-center">
            <div class="text-sm text-gray-600">
                Total Siswa: <span class="font-bold" x-text="filteredSantri.length"></span>
            </div>
            <div class="flex gap-2">
                <button @click="resetForm()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm hover:bg-gray-300">
                    Reset
                </button>
                <button @click="saveAbsensi()" :disabled="isSaving" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!isSaving">Simpan Absensi</span>
                    <span x-show="isSaving" class="flex items-center gap-2">
                        <span class="animate-spin">↻</span> Menyimpan...
                    </span>
                </button>
            </div>
        </div>
</div>

<script>
    function absensiMapelHandler() {
        return {
            guruMapels: @json($guruMapels ?? []),
            selectedMapelId: '',
            allSantri: [],
            filteredSantri: [],
            searchQuery: '',
            absensiData: {},
            currentDate: new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }),
            isSaving: false,
            tanggalAbsensi: new Date().toISOString().split('T')[0],

            init() {
                console.log('Guru Mapels:', this.guruMapels);
                
                // Watch for date changes and reset form
                this.$watch('tanggalAbsensi', () => {
                    this.resetForm();
                });
            },

            async loadSantriMapel() {
                if (!this.selectedMapelId) {
                    this.allSantri = [];
                    this.filteredSantri = [];
                    return;
                }

                try {
                    const response = await axios.get(`/akademik/absensi/${this.selectedMapelId}/santri`);
                    this.allSantri = response.data.santri || [];
                    this.filteredSantri = this.allSantri;

                    // Initialize absensi data
                    this.absensiData = {};
                    this.allSantri.forEach(s => {
                        this.absensiData[s.id] = { status: '', keterangan: '' };
                    });
                } catch (error) {
                    console.error('Error loading santri:', error);
                    Swal.fire('Error', 'Gagal memuat data siswa', 'error');
                }
            },

            mapStatusToKehadiran(status) {
                // Status is already H, S, I, A
                return status || 'A';
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
            },

            setStatus(santriId, status) {
                this.absensiData[santriId].status = status;
            },

            tandaiSemuaHadir() {
                if (!this.selectedMapelId) {
                    Swal.fire('Peringatan', 'Silakan pilih mata pelajaran terlebih dahulu', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Tandai Semua Hadir?',
                    text: `Apakah Anda yakin ingin menandai ${this.filteredSantri.length} siswa sebagai HADIR?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: 'Ya, Tandai Semua!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        this.filteredSantri.forEach(s => {
                            this.absensiData[s.id].status = 'H';
                        });
                        Swal.fire('Sukses!', 'Semua siswa ditandai hadir', 'success');
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

            resetForm() {
                Object.keys(this.absensiData).forEach(id => {
                    this.absensiData[id] = { status: '', keterangan: '' };
                });
            },

            async saveAbsensi() {
                if (!this.selectedMapelId) {
                    Swal.fire('Peringatan', 'Silakan pilih mata pelajaran terlebih dahulu', 'warning');
                    return;
                }

                // Prepare data
                const absensiArray = Object.keys(this.absensiData).map(santriId => ({
                    id: parseInt(santriId),
                    kehadiran: this.mapStatusToKehadiran(this.absensiData[santriId].status)
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
                    
                    // Optionally reset form after save
                    // this.resetForm();
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
            }
        }
    }
</script>
@endsection
