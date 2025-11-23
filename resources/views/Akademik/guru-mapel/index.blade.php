@extends('layouts.app')

@section('title', 'Mapel yang Saya Ajar')

@section('content')
<div x-data="guruMapelData()" class="container mx-auto p-6">
    
    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Mapel yang Saya Ajar</h1>
        <p class="text-sm text-gray-500 mt-1">Pilih mata pelajaran dan kelas yang Anda ajar</p>
    </div>

    {{-- TOMBOL TAMBAH MAPEL --}}
    <div class="mb-6">
        <button @click="showModal = true" 
            class="bg-blue-600 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-700 transition">
            <i class="fas fa-plus-circle mr-2"></i> Tambah Mapel yang Diajar
        </button>
    </div>

    {{-- TABEL MAPEL YANG DIAJAR --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Mata Pelajaran</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 uppercase">Kelas</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Semester</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Tahun Ajaran</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-700 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <template x-if="guruMapels.length === 0">
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Anda belum mengajar mapel apapun. Klik tombol "Tambah Mapel yang Diajar" untuk memulai.
                        </td>
                    </tr>
                </template>
                
                <template x-for="(gm, index) in guruMapels" :key="gm.id">
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 text-sm text-gray-600" x-text="index + 1"></td>
                        <td class="px-6 py-4">
                            <span class="text-sm font-medium text-gray-800" x-text="gm.mapel?.nama_mapel"></span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="text-sm text-gray-700" x-text="gm.kelas ? gm.kelas.level + ' ' + gm.kelas.nama_unik : '-'"></span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="px-2 py-1 text-xs rounded-full" 
                                :class="gm.semester == 'ganjil' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700'"
                                x-text="'Semester ' + (gm.semester == 'ganjil' ? 'Ganjil' : 'Genap')"></span>
                        </td>
                        <td class="px-6 py-4 text-center text-sm text-gray-600" x-text="gm.tahun_ajaran"></td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a :href="`/akademik/guru-mapel/${gm.id}/rekap`" 
                                    class="bg-green-600 text-white px-3 py-1.5 rounded text-sm hover:bg-green-700 transition">
                                    <i class="fas fa-chart-bar mr-1"></i> Rekap
                                </a>
                                <button @click="deleteGuruMapel(gm.id)" 
                                    class="bg-red-500 text-white px-3 py-1.5 rounded text-sm hover:bg-red-600 transition">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
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
        
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all max-w-lg w-full z-50">
                <div class="bg-gray-50 px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Mapel yang Diajar</h3>
                </div>

                <div class="px-6 py-4">
                    <div class="space-y-4">
                        {{-- Mapel --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Mata Pelajaran</label>
                            <select x-model="form.mapel_id" 
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Pilih Mapel --</option>
                                <template x-for="mapel in mapels" :key="mapel.id">
                                    <option :value="mapel.id" x-text="mapel.nama_mapel"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Kelas --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                            <select x-model="form.kelas_id" 
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Pilih Kelas --</option>
                                <template x-for="kls in filteredKelas" :key="kls.id">
                                    <option :value="kls.id" x-text="kls.level + ' ' + kls.nama_unik"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Semester --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                            <select x-model="form.semester" 
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                <option value="">-- Pilih Semester --</option>
                                <option value="ganjil">Semester Ganjil</option>
                                <option value="genap">Semester Genap</option>
                            </select>
                        </div>

                        {{-- Tahun Ajaran --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Ajaran</label>
                            <input type="text" 
                                x-model="form.tahun_ajaran" 
                                placeholder="2024/2025"
                                class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-6 py-4 flex justify-end gap-3">
                    <button @click="showModal = false" 
                        class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        Batal
                    </button>
                    <button @click="saveGuruMapel()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Simpan
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function guruMapelData() {
    return {
        guruMapels: @json($guruMapels),
        mapels: @json($mapels),
        kelas: @json($kelas),
        filteredKelas: [],
        showModal: false,
        form: {
            mapel_id: '',
            kelas_id: '',
            semester: '',
            tahun_ajaran: '2025/2026'
        },

        init() {
            console.log('GuruMapels Data:', this.guruMapels);
            console.log('Kelas Data:', this.kelas);
            this.filteredKelas = this.kelas;

            this.$watch('form.mapel_id', (value) => {
                this.filterKelas(value);
            });
        },

        filterKelas(mapelId) {
            if (!mapelId) {
                this.filteredKelas = this.kelas;
                return;
            }

            const selectedMapel = this.mapels.find(m => m.id == mapelId);
            if (selectedMapel && selectedMapel.tingkat && selectedMapel.tingkat.length > 0) {
                // Ensure tingkat is treated as an array of strings for comparison
                const targetLevels = Array.isArray(selectedMapel.tingkat) 
                    ? selectedMapel.tingkat.map(String) 
                    : [String(selectedMapel.tingkat)];

                this.filteredKelas = this.kelas.filter(k => targetLevels.includes(String(k.level)));
            } else {
                // If mapel has no specific target levels, show all classes (or maybe none? usually all)
                this.filteredKelas = this.kelas;
            }
            
            // Reset selected kelas if it's no longer valid
            if (this.form.kelas_id && !this.filteredKelas.find(k => k.id == this.form.kelas_id)) {
                this.form.kelas_id = '';
            }
        },

        async saveGuruMapel() {
            if (!this.form.mapel_id || !this.form.kelas_id || !this.form.semester || !this.form.tahun_ajaran) {
                alert('Semua field harus diisi');
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
                    this.guruMapels.push(result.data);
                    this.showModal = false;
                    this.form = {
                        mapel_id: '',
                        kelas_id: '',
                        semester: '',
                        tahun_ajaran: '2025/2026'
                    };
                    alert(result.message);
                } else {
                    alert(result.message || 'Gagal menambahkan mapel');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan: ' + error.message);
            }
        },

        async deleteGuruMapel(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus mapel ini?')) {
                return;
            }

            try {
                const response = await fetch(`{{ url('akademik/guru-mapel') }}/${id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const result = await response.json();

                if (response.ok) {
                    this.guruMapels = this.guruMapels.filter(gm => gm.id !== id);
                    alert(result.message);
                } else {
                    alert(result.message || 'Gagal menghapus mapel');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Terjadi kesalahan: ' + error.message);
            }
        }
    };
}
</script>
@endsection
