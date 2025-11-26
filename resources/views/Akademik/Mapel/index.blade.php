@extends('layouts.app')

@section('title', 'Pengaturan Mata Pelajaran')

@section('content')
<div x-data="mapelData()" class="container mx-auto p-6">
    
    {{-- HEADER --}}
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Pengaturan Mata Pelajaran</h1>
    </div>

    {{-- KELOMPOK MAPEL SMP (7, 8, 9) --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-700 mb-4">Kelompok Mapel SMP</h2>
        
        <template x-for="(kelompok, kIndex) in kelompokMapels.filter(k => k.jenis === 'smp')" :key="kelompok.id">
                <table class="min-w-full border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border">#</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border">Nama Mapel</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700 border">JJM</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700 border">Target Tingkat</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(mapel, mIndex) in kelompok.mapels" :key="mapel.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-600 border" x-text="mIndex + 1"></td>
                                <td class="px-4 py-2 border">
                                    <input type="text" 
                                        x-model="mapel.nama_mapel"
                                        @blur="updateMapel(mapel)"
                                        class="w-full border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-4 py-2 text-center border">
                                    <input type="number" 
                                        x-model="mapel.jjm"
                                        @blur="updateMapel(mapel)"
                                        min="0"
                                        class="w-20 border-gray-300 rounded px-2 py-1 text-sm text-center">
                                </td>
                                <td class="px-4 py-2 border">
                                    <div class="flex items-center justify-center space-x-3">
                                        <template x-for="tingkat in ['7', '8', '9']" :key="tingkat">
                                            <label class="flex items-center space-x-1 text-sm" :title="mapel.used_levels && mapel.used_levels.includes(tingkat) ? 'Tingkat ini sedang digunakan (terkunci)' : ''">
                                                <input type="checkbox" 
                                                    :checked="mapel.tingkat && mapel.tingkat.includes(tingkat)"
                                                    @change="toggleTingkat(mapel, tingkat, $event.target.checked)"
                                                    :disabled="mapel.used_levels && mapel.used_levels.includes(tingkat)"
                                                    class="w-4 h-4 text-purple-600 border-gray-300 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span class="bg-purple-100 px-2 py-0.5 rounded" x-text="tingkat" :class="{'opacity-50': mapel.used_levels && mapel.used_levels.includes(tingkat)}"></span>
                                            </label>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-center border">
                                    <template x-if="mapel.guru_mapels_count > 0">
                                        <button @click="showCannotDeleteInfo()" 
                                            class="bg-gray-300 text-gray-500 w-8 h-8 rounded-full cursor-not-allowed" 
                                            title="Mapel tidak dapat dihapus karena sedang digunakan">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    </template>
                                    <template x-if="!mapel.guru_mapels_count || mapel.guru_mapels_count == 0">
                                        <button @click="deleteMapel(mapel.id)" 
                                            class="bg-red-500 text-white w-8 h-8 rounded-full hover:bg-red-600"
                                            title="Hapus Mapel">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </template>

                        {{-- FORM TAMBAH MAPEL BARU --}}
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border"></td>
                            <td class="px-4 py-2 border">
                                <input type="text" 
                                    x-model="newMapel[kelompok.id].nama_mapel"
                                    placeholder="Nama mapel baru"
                                    class="w-full border-gray-300 rounded px-2 py-1 text-sm">
                            </td>
                            <td class="px-4 py-2 text-center border">
                                <input type="number" 
                                    x-model="newMapel[kelompok.id].jjm"
                                    placeholder="0"
                                    min="0"
                                    class="w-20 border-gray-300 rounded px-2 py-1 text-sm text-center">
                            </td>
                            <td class="px-4 py-2 border">
                                <div class="flex items-center justify-center space-x-3">
                                    <template x-for="tingkat in ['7', '8', '9']" :key="tingkat">
                                        <label class="flex items-center space-x-1 text-sm">
                                            <input type="checkbox" 
                                                x-model="newMapel[kelompok.id].tingkat"
                                                :value="tingkat"
                                                class="w-4 h-4 text-purple-600 border-gray-300 rounded">
                                            <span class="bg-purple-100 px-2 py-0.5 rounded" x-text="tingkat"></span>
                                        </label>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center border">
                                <button @click="addMapel(kelompok.id)" 
                                    class="bg-purple-600 text-white px-4 py-1 rounded hover:bg-purple-700 text-sm">
                                    Tambah
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

    {{-- KELOMPOK MAPEL SMA (10, 11, 12) --}}
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-bold text-gray-700 mb-4">Kelompok Mapel SMA</h2>
        
        <template x-for="(kelompok, kIndex) in kelompokMapels.filter(k => k.jenis === 'sma')" :key="kelompok.id">
                <table class="min-w-full border">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border">No</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-700 border">Nama Mapel</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700 border">JJM</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700 border">Target Tingkat</th>
                            <th class="px-4 py-2 text-center text-sm font-semibold text-gray-700 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(mapel, mIndex) in kelompok.mapels" :key="mapel.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-sm text-gray-600 border" x-text="mIndex + 1"></td>
                                <td class="px-4 py-2 border">
                                    <input type="text" 
                                        x-model="mapel.nama_mapel"
                                        @blur="updateMapel(mapel)"
                                        class="w-full border-gray-300 rounded px-2 py-1 text-sm">
                                </td>
                                <td class="px-4 py-2 text-center border">
                                    <input type="number" 
                                        x-model="mapel.jjm"
                                        @blur="updateMapel(mapel)"
                                        min="0"
                                        class="w-20 border-gray-300 rounded px-2 py-1 text-sm text-center">
                                </td>
                                <td class="px-4 py-2 border">
                                    <div class="flex items-center justify-center space-x-3">
                                        <template x-for="tingkat in ['10', '11', '12']" :key="tingkat">
                                            <label class="flex items-center space-x-1 text-sm" :title="mapel.used_levels && mapel.used_levels.includes(tingkat) ? 'Tingkat ini sedang digunakan (terkunci)' : ''">
                                                <input type="checkbox" 
                                                    :checked="mapel.tingkat && mapel.tingkat.includes(tingkat)"
                                                    @change="toggleTingkat(mapel, tingkat, $event.target.checked)"
                                                    :disabled="mapel.used_levels && mapel.used_levels.includes(tingkat)"
                                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded disabled:opacity-50 disabled:cursor-not-allowed">
                                                <span class="bg-blue-100 px-2 py-0.5 rounded" x-text="tingkat" :class="{'opacity-50': mapel.used_levels && mapel.used_levels.includes(tingkat)}"></span>
                                            </label>
                                        </template>
                                    </div>
                                </td>
                                <td class="px-4 py-2 text-center border">
                                    <template x-if="mapel.guru_mapels_count > 0">
                                        <button @click="showCannotDeleteInfo()" 
                                            class="bg-gray-300 text-gray-500 w-8 h-8 rounded-full cursor-not-allowed" 
                                            title="Mapel tidak dapat dihapus karena sedang digunakan">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    </template>
                                    <template x-if="!mapel.guru_mapels_count || mapel.guru_mapels_count == 0">
                                        <button @click="deleteMapel(mapel.id)" 
                                            class="bg-red-500 text-white w-8 h-8 rounded-full hover:bg-red-600"
                                            title="Hapus Mapel">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </template>
                                </td>
                            </tr>
                        </template>

                        {{-- FORM TAMBAH MAPEL BARU --}}
                        <tr class="bg-gray-50">
                            <td class="px-4 py-2 border"></td>
                            <td class="px-4 py-2 border">
                                <input type="text" 
                                    x-model="newMapel[kelompok.id].nama_mapel"
                                    placeholder="Nama mapel baru"
                                    class="w-full border-gray-300 rounded px-2 py-1 text-sm">
                            </td>
                            <td class="px-4 py-2 text-center border">
                                <input type="number" 
                                    x-model="newMapel[kelompok.id].jjm"
                                    placeholder="0"
                                    min="0"
                                    class="w-20 border-gray-300 rounded px-2 py-1 text-sm text-center">
                            </td>
                            <td class="px-4 py-2 border">
                                <div class="flex items-center justify-center space-x-3">
                                    <template x-for="tingkat in ['10', '11', '12']" :key="tingkat">
                                        <label class="flex items-center space-x-1 text-sm">
                                            <input type="checkbox" 
                                                x-model="newMapel[kelompok.id].tingkat"
                                                :value="tingkat"
                                                class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                                            <span class="bg-blue-100 px-2 py-0.5 rounded" x-text="tingkat"></span>
                                        </label>
                                    </template>
                                </div>
                            </td>
                            <td class="px-4 py-2 text-center border">
                                <button @click="addMapel(kelompok.id)" 
                                    class="bg-blue-600 text-white px-4 py-1 rounded hover:bg-blue-700 text-sm">
                                    Tambah
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </template>
    </div>

</div>

{{-- INLINE SCRIPT --}}
<script>
function mapelData() {
    return {
        kelompokMapels: @json($kelompokMapels),
        newMapel: {},

        init() {
            this.kelompokMapels.forEach(kelompok => {
                this.newMapel[kelompok.id] = {
                    nama_mapel: '',
                    jjm: 0,
                    tingkat: []
                };
            });
        },

        showCannotDeleteInfo() {
            Swal.fire({
                icon: 'info',
                title: 'Tidak Dapat Dihapus',
                text: 'Mata pelajaran ini sedang digunakan (memiliki relasi dengan guru/kelas/jadwal).',
                confirmButtonColor: '#3085d6',
            });
        },

        async addMapel(kelompokId) {
            const formData = this.newMapel[kelompokId];

            if (!formData.nama_mapel) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Validasi',
                    text: 'Nama mapel harus diisi',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            try {
                const response = await fetch('{{ route("akademik.mapel.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nama_mapel: formData.nama_mapel,
                        jjm: formData.jjm || 0,
                        tingkat: formData.tingkat || []
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    const kelompok = this.kelompokMapels.find(k => k.id === kelompokId);
                    if (kelompok) {
                        // Add guru_mapels_count = 0 for new item
                        result.data.guru_mapels_count = 0;
                        kelompok.mapels.push(result.data);
                    }

                    this.newMapel[kelompokId] = {
                        nama_mapel: '',
                        jjm: 0,
                        tingkat: []
                    };

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: result.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    let errorMsg = result.message || 'Gagal menambahkan mapel';
                    if (result.errors) {
                        errorMsg = Object.values(result.errors).flat().join('\n');
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMsg
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan sistem'
                });
            }
        },

        async updateMapel(mapel) {
            try {
                const response = await fetch(`{{ url('akademik/mapel') }}/${mapel.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        nama_mapel: mapel.nama_mapel,
                        jjm: mapel.jjm || 0,
                        tingkat: mapel.tingkat || []
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Tersimpan',
                        text: 'Perubahan berhasil disimpan',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: result.message || 'Gagal memperbarui mapel',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        async deleteMapel(mapelId) {
            const result = await Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Mapel yang dihapus tidak dapat dikembalikan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            });

            if (!result.isConfirmed) return;

            try {
                const response = await fetch(`{{ url('akademik/mapel') }}/${mapelId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const resJson = await response.json();

                if (response.ok) {
                    this.kelompokMapels.forEach(kelompok => {
                        kelompok.mapels = kelompok.mapels.filter(m => m.id !== mapelId);
                    });
                    Swal.fire({
                        icon: 'success',
                        title: 'Terhapus!',
                        text: resJson.message,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: resJson.message || 'Gagal menghapus mapel'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Terjadi kesalahan sistem'
                });
            }
        },

        toggleTingkat(mapel, tingkat, checked) {
            if (!mapel.tingkat) {
                mapel.tingkat = [];
            }

            if (checked) {
                if (!mapel.tingkat.includes(tingkat)) {
                    mapel.tingkat.push(tingkat);
                }
            } else {
                mapel.tingkat = mapel.tingkat.filter(t => t !== tingkat);
            }

            this.updateMapel(mapel);
        }
    };
}
</script>
@endsection
