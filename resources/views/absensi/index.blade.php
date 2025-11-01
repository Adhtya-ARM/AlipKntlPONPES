@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold">Daftar Mata Pelajaran Ajar</h1>
    </div>

    <div class="bg-white shadow rounded overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Pelajaran</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($guruMapels as $index => $guruMapel)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $guruMapel->mapel->nama_mapel }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $guruMapel->mapel->kelas }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button @click="openAbsensiModal({{ $guruMapel->id }})"
                            class="bg-green-500 text-white px-3 py-1 rounded">
                            Isi Absensi
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>



    <!-- Modal Absensi -->
    <div x-data="absensiModal()" x-cloak>
        <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-4xl max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Input Absensi</h2>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Pertemuan Ke</label>
                        <select x-model="pertemuanKe" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                            <template x-for="i in maxPertemuan" :key="i">
                                <option :value="i" x-text="i"></option>
                            </template>
                        </select>
                    </div>

                    <div x-show="loading" class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                        <p class="mt-2 text-gray-600">Memuat data santri...</p>
                    </div>

                    <div x-show="!loading" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama
                                        Santri</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        Keterangan</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <template x-for="(santri, index) in santriList" :key="santri.id">
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap" x-text="index + 1"></td>
                                        <td class="px-6 py-4 whitespace-nowrap" x-text="santri.nama"></td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <select x-model="santri.status"
                                                class="border-gray-300 rounded-md shadow-sm">
                                                <option value="hadir">Hadir</option>
                                                <option value="sakit">Sakit</option>
                                                <option value="izin">Izin</option>
                                                <option value="alpha">Alpha</option>
                                                <option value="X">X</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="text" x-model="santri.keterangan"
                                                class="border-gray-300 rounded-md shadow-sm w-full"
                                                placeholder="Opsional">
                                        </td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <button @click="close()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">Batal</button>
                        <button @click="submitAbsensi()" :disabled="loading"
                            class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50">
                            <span x-show="loading">Menyimpan...</span>
                            <span x-show="!loading">Simpan Absensi</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function absensiModal() {
    return {
        open: false,
        guruMapelId: null,
        pertemuanKe: 1,
        santriList: [],
        maxPertemuan: 16,
        loading: false,

        openAbsensiModal(guruMapelId) {
            this.open = true;
            this.guruMapelId = guruMapelId;
            this.loading = true;

            fetch(`/akademik/absensi/santri/${guruMapelId}`)
                .then(response => response.json())
                .then(data => {
                    this.santriList = data.santri.map(santri => ({
                        ...santri,
                        status: 'hadir',
                        keterangan: ''
                    }));
                    this.maxPertemuan = data.maxPertemuan;
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.loading = false;
                });
        },

        close() {
            this.open = false;
            this.guruMapelId = null;
            this.pertemuanKe = 1;
            this.santriList = [];
        },

        submitAbsensi() {
            this.loading = true;

            const data = {
                guru_mapel_id: this.guruMapelId,
                pertemuan_ke: this.pertemuanKe,
                absensi: this.santriList.map(santri => ({
                    id: santri.id,
                    status: santri.status,
                    keterangan: santri.keterangan
                }))
            };

            fetch('/akademik/absensi', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    alert('Absensi berhasil disimpan!');
                    this.close();
                    this.loading = false;
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat menyimpan absensi');
                    this.loading = false;
                });
        }
    }
}
</script>

@endsection