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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            {{-- Pilih Mapel --}}
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Kelas & Mapel</label>
                <select x-model="selectedMapelId" @change="loadSantriMapel()" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    <template x-for="mapel in guruMapels" :key="mapel.id">
                        <option :value="mapel.id" x-text="`${mapel.mapel?.nama_mapel || 'N/A'} - Kelas ${mapel.kelas?.level || '?'}${mapel.kelas?.nama_unik || ''}`"></option>
                    </template>
                </select>
            </div>

            {{-- Cari Siswa --}}
            <div class="md:col-span-1">
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Siswa</label>
                <input type="text" x-model="searchQuery" @input="filterSantri()" placeholder="Ketik nama siswa..." class="w-full border-gray-300 rounded-md text-sm">
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
                <button @click="savePenilaian()" :disabled="isSaving" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm hover:bg-blue-700 disabled:opacity-50">
                    <span x-show="!isSaving">Simpan Penilaian</span>
                    <span x-show="isSaving" class="flex items-center gap-2">
                        <span class="animate-spin">↻</span> Menyimpan...
                    </span>
                </button>
            </div>
        </div>
</div>

<script>
    function penilaianMapelHandler() {
        return {
            guruMapels: @json($guruMapels ?? []),
            selectedMapelId: '',
            allSantri: [],
            filteredSantri: [],
            searchQuery: '',
            penilaianData: {},
            jenisPenilaian: 'UH',
            tanggalPenilaian: new Date().toISOString().split('T')[0],
            isSaving: false,

            init() {
                console.log('Guru Mapels:', this.guruMapels);
                
                // Watch for changes that might require reset or reload
                // this.$watch('tanggalPenilaian', () => this.resetForm());
                // this.$watch('jenisPenilaian', () => this.resetForm());
            },

            async loadSantriMapel() {
                if (!this.selectedMapelId) {
                    this.allSantri = [];
                    this.filteredSantri = [];
                    return;
                }

                try {
                    // Assuming the route is similar to absensi but for penilaian or reusing the logic
                    // If the route /akademik/penilaian/{id}/santri exists
                    const response = await axios.get(`/akademik/penilaian/${this.selectedMapelId}/santri`);
                    this.allSantri = response.data.santri || [];
                    this.filteredSantri = this.allSantri;

                    // Initialize penilaian data
                    this.penilaianData = {};
                    this.allSantri.forEach(s => {
                        this.penilaianData[s.id] = { nilai: '', keterangan: '' };
                    });
                } catch (error) {
                    console.error('Error loading santri:', error);
                    // Fallback to absensi route if penilaian route fails (just in case)
                    try {
                        const response = await axios.get(`/akademik/absensi/${this.selectedMapelId}/santri`);
                        this.allSantri = response.data.santri || [];
                        this.filteredSantri = this.allSantri;
                        this.penilaianData = {};
                        this.allSantri.forEach(s => {
                            this.penilaianData[s.id] = { nilai: '', keterangan: '' };
                        });
                    } catch (e) {
                        Swal.fire('Error', 'Gagal memuat data siswa', 'error');
                    }
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
