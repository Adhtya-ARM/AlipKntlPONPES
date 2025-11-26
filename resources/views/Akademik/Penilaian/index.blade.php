@extends('layouts.app')

@section('title', 'Input Penilaian')

@section('content')
<div x-data="penilaianMapelHandler()" x-init="init()" class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Input Penilaian</h2>
        <p class="text-sm text-gray-500 mt-1">Input nilai per mata pelajaran yang Anda ajar</p>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            {{-- Pilih Kelas --}}
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">1. Pilih Kelas</label>
                <select x-model="selectedKelasId" @change="filterMapel()" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">-- Pilih Kelas --</option>
                    <template x-for="k in uniqueKelas" :key="k.id">
                        <option :value="k.id" x-text="`Kelas ${k.level} ${k.nama_unik || ''}`"></option>
                    </template>
                </select>
            </div>

            {{-- Pilih Mapel --}}
            <div class="md:col-span-1">
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

            {{-- Jenis Penilaian --}}
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Jenis Penilaian</label>
                <select x-model="jenisPenilaian" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="UH">Ulangan Harian (UH)</option>
                    <option value="Tugas">Tugas</option>
                    <option value="UTS">UTS</option>
                    <option value="UAS">UAS</option>
                    <option value="Praktek">Praktek</option>
                </select>
            </div>

            {{-- Tanggal --}}
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Penilaian</label>
                <input type="date" x-model="tanggalPenilaian" class="w-full border-gray-300 rounded-md text-sm">
            </div>

            {{-- Cari Siswa --}}
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Siswa</label>
                <input type="text" x-model="searchQuery" @input="filterSantri()" placeholder="Ketik nama siswa..." class="w-full border-gray-300 rounded-md text-sm">
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
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider w-32">Nilai (0-100)</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Keterangan</th>
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
                                    type="number" 
                                    min="0" 
                                    max="100"
                                    x-model="penilaianData[santri.id].nilai" 
                                    placeholder="0" 
                                    class="w-full border-gray-300 rounded text-sm text-center font-bold text-blue-600 focus:ring-blue-500 focus:border-blue-500"
                                >
                            </td>
                            <td class="px-4 py-3 text-center">
                                <input 
                                    type="text" 
                                    x-model="penilaianData[santri.id].keterangan" 
                                    placeholder="Catatan..." 
                                    class="w-full border-gray-300 rounded text-xs"
                                >
                            </td>
                        </tr>
                    </template>
                    
                    <tr x-show="filteredSantri.length === 0">
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                            <div class="text-4xl mb-3">📊</div>
                            <div x-show="!selectedMapelId">Silakan pilih Kelas dan Mata Pelajaran terlebih dahulu</div>
                            <div x-show="selectedMapelId && allSantri.length === 0">Tidak ada siswa terdaftar di mapel ini</div>
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
                <button @click="resetForm()" class="bg-white border border-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm hover:bg-gray-50 transition shadow-sm">
                    Reset
                </button>
                <button @click="savePenilaian()" :disabled="isSaving" class="bg-blue-600 text-white px-6 py-2 rounded-lg text-sm hover:bg-blue-700 disabled:opacity-50 transition shadow-md flex items-center gap-2">
                    <span x-show="!isSaving">Simpan Penilaian</span>
                    <span x-show="isSaving" class="flex items-center gap-2">
                        <i class="fas fa-spinner fa-spin"></i> Menyimpan...
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function penilaianMapelHandler() {
        return {
            guruMapels: @json($guruMapels ?? []),
            uniqueKelas: [],
            filteredMapels: [],
            
            selectedKelasId: '',
            selectedMapelId: '{{ $preSelectedMapelId ?? "" }}',
            
            allSantri: [],
            filteredSantri: [],
            searchQuery: '',
            penilaianData: {},
            jenisPenilaian: 'UH',
            tanggalPenilaian: new Date().toISOString().split('T')[0],
            isSaving: false,

            init() {
                // Extract unique kelas from guruMapels
                const kelasMap = new Map();
                this.guruMapels.forEach(gm => {
                    if (gm.kelas && !kelasMap.has(gm.kelas.id)) {
                        kelasMap.set(gm.kelas.id, gm.kelas);
                    }
                });
                this.uniqueKelas = Array.from(kelasMap.values()).sort((a, b) => a.level - b.level);
                
                if (this.selectedMapelId) {
                    const gm = this.guruMapels.find(g => g.id == this.selectedMapelId);
                    if (gm) {
                        this.selectedKelasId = gm.kelas_id;
                        this.filterMapel();
                        this.loadSantriMapel();
                    }
                }
                
                // Watch for changes that might require reset or reload
                this.$watch('tanggalPenilaian', () => {
                    if (this.selectedMapelId) this.loadSantriMapel();
                });
                this.$watch('jenisPenilaian', () => {
                    if (this.selectedMapelId) this.loadSantriMapel();
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
                    const response = await axios.get(`/akademik/penilaian/${this.selectedMapelId}/santri`, {
                        params: {
                            date: this.tanggalPenilaian,
                            type: this.jenisPenilaian
                        }
                    });
                    this.allSantri = response.data.santri || [];
                    this.filteredSantri = this.allSantri;
                    
                    const existing = response.data.existingGrades || {};
                    const hasExisting = Object.keys(existing).length > 0;

                    // Initialize penilaian data
                    this.penilaianData = {};
                    this.allSantri.forEach(s => {
                        if (existing[s.id]) {
                            this.penilaianData[s.id] = { 
                                nilai: existing[s.id].nilai, 
                                keterangan: existing[s.id].keterangan || '' 
                            };
                        } else {
                            this.penilaianData[s.id] = { nilai: '', keterangan: '' };
                        }
                    });
                    
                    if (hasExisting) {
                        Swal.fire({
                            title: 'Sudah Ada Nilai',
                            text: 'Data penilaian untuk tanggal dan jenis ini sudah ada.',
                            icon: 'info',
                            timer: 3000,
                            timerProgressBar: true
                        });
                    }
                } catch (error) {
                    console.error('Error loading santri:', error);
                    Swal.fire('Error', 'Gagal memuat data siswa', 'error');
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
            },

            resetForm() {
                Object.keys(this.penilaianData).forEach(id => {
                    this.penilaianData[id] = { nilai: '', keterangan: '' };
                });
            },

            async savePenilaian() {
                if (!this.selectedMapelId) {
                    Swal.fire('Peringatan', 'Silakan pilih mata pelajaran terlebih dahulu', 'warning');
                    return;
                }

                // Prepare data
                const payload = {
                    guru_mapel_id: this.selectedMapelId,
                    tanggal_penilaian: this.tanggalPenilaian,
                    jenis_penilaian: this.jenisPenilaian,
                    penilaian: Object.keys(this.penilaianData).map(santriId => ({
                        id: parseInt(santriId),
                        nilai: this.penilaianData[santriId].nilai === '' ? null : this.penilaianData[santriId].nilai,
                        keterangan: this.penilaianData[santriId].keterangan
                    })),
                    _token: '{{ csrf_token() }}'
                };

                this.isSaving = true;

                try {
                    const response = await axios.post('{{ route('akademik.penilaian.store') }}', payload);

                    Swal.fire('Sukses', 'Penilaian berhasil disimpan!', 'success');
                } catch (error) {
                    console.error('Error:', error);
                    let msg = 'Gagal menyimpan penilaian';
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
