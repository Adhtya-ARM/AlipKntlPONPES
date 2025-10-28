@extends('layouts.app')

@section('title', 'Manajemen Mata Pelajaran')

@section('content')
<div x-data="mapelManager()" class="container mx-auto p-4 sm:p-6 lg:p-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Manajemen Mata Pelajaran (Mapel)</h1>

    {{-- Notifikasi --}}
    @if (session('success'))
        <div x-data="{ show: true }" x-show="show" class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
            <span class="block sm:inline">{{ session('success') }}</span>
            <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show=false">
                <svg class="fill-current h-6 w-6 text-green-500" viewBox="0 0 20 20">
                    <path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.854l-2.651 2.995a1.2 1.2 0 1 1-1.697-1.697l2.995-2.651-2.995-2.651a1.2 1.2 0 0 1 1.697-1.697L10 8.157l2.651-2.995a1.2 1.2 0 1 1 1.697 1.697L11.854 10l2.995 2.651a1.2 1.2 0 0 1 0 1.698z"/>
                </svg>
            </button>
        </div>
    @endif

    {{-- Tombol Tambah --}}
    <div class="flex justify-end mb-4">
        <button @click="openCreate()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-md transition">
            <i class="fas fa-plus mr-2"></i> Tambah Mapel Baru
        </button>
    </div>

    {{-- Tabel --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-indigo-600 text-white font-semibold">Daftar Mata Pelajaran</div>
        <div class="p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Mapel</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Semester</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tahun Ajaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Guru</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($mapels as $mapel)
                        <tr class="hover:bg-gray-100">
                            <td class="px-6 py-4">{{ $loop->iteration }}</td>
                            <td class="px-6 py-4">{{ $mapel->nama_mapel }}</td>
                            <td class="px-6 py-4">{{ $mapel->kelas }}</td>
                            <td class="px-6 py-4">{{ $mapel->semester }}</td>
                            <td class="px-6 py-4">{{ $mapel->tahun_ajaran }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-200 text-gray-800">
                                    {{ $mapel->guru_profiles_count ?? 0 }} Guru
                                </span>
                            </td>
                            <td class="px-6 py-4 flex space-x-2">
                                <button @click="openEdit({{ $mapel->toJson() }})" 
                                    class="p-2 text-indigo-600 border border-indigo-600 rounded hover:bg-indigo-600 hover:text-white">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <form action="{{ route('akademik.mapel.destroy', $mapel->id) }}" method="POST" onsubmit="return confirm('Hapus {{ $mapel->nama_mapel }}?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 text-red-600 border border-red-600 rounded hover:bg-red-600 hover:text-white">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-gray-500 py-4">Belum ada data Mapel</td></tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4 flex justify-center">
                {{ $mapels->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Tambah --}}
    <template x-if="showCreate">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
            <div class="bg-white rounded-lg w-full max-w-lg shadow-lg">
                <div class="flex justify-between items-center bg-blue-600 text-white px-4 py-3">
                    <h2 class="font-semibold text-lg">Tambah Mapel</h2>
                    <button @click="closeModals()"><i class="fas fa-times"></i></button>
                </div>

                <form action="{{ route('akademik.mapel.store') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Mapel</label>
                        <input type="text" name="nama_mapel" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <input type="text" name="kelas" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Semester</label>
                        <select name="semester" class="w-full border p-2 rounded" required>
                            <option value="">Pilih</option>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" placeholder="2024/2025" class="w-full border p-2 rounded" required>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="closeModals()" class="bg-gray-200 px-4 py-2 rounded">Batal</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Modal Edit --}}
    <template x-if="showEdit">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
            <div class="bg-white rounded-lg w-full max-w-lg shadow-lg">
                <div class="flex justify-between items-center bg-green-600 text-white px-4 py-3">
                    <h2 class="font-semibold text-lg">Edit Mapel</h2>
                    <button @click="closeModals()"><i class="fas fa-times"></i></button>
                </div>

                <form :action="updateRoute" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama Mapel</label>
                        <input type="text" name="nama_mapel" x-model="edit.nama_mapel" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kelas</label>
                        <input type="text" name="kelas" x-model="edit.kelas" class="w-full border p-2 rounded" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Semester</label>
                        <select name="semester" x-model="edit.semester" class="w-full border p-2 rounded" required>
                            <option value="Ganjil">Ganjil</option>
                            <option value="Genap">Genap</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                        <input type="text" name="tahun_ajaran" x-model="edit.tahun_ajaran" class="w-full border p-2 rounded" required>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="closeModals()" class="bg-gray-200 px-4 py-2 rounded">Batal</button>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </template>
</div>

<script>
function mapelManager() {
    return {
        showCreate: false,
        showEdit: false,
        edit: { id: '', nama_mapel: '', kelas: '', semester: '', tahun_ajaran: '' },

        get updateRoute() {
            return this.edit.id ? `/akademik/mapel/${this.edit.id}` : '#';
        },

        openCreate() {
            this.closeModals();
            this.showCreate = true;
        },
        openEdit(data) {
            this.closeModals();
            this.edit = { ...data };
            this.showEdit = true;
        },
        closeModals() {
            this.showCreate = false;
            this.showEdit = false;
        },
    }
}
</script>
@endsection
