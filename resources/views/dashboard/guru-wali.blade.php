@extends('layouts.app')

@section('title', 'Data Wali - Guru')

@section('content')

<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Data Wali Santri yang Diajar</h1>

    {{-- Notifikasi --}}
    @if (session('success'))
    <div x-data="{ show: true }" x-show="show"
        class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
        <span class="block sm:inline">{{ session('success') }}</span>
        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" @click="show=false">
            <svg class="fill-current h-6 w-6 text-green-500" viewBox="0 0 20 20">
                <path
                    d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.854l-2.651 2.995a1.2 1.2 0 1 1-1.697-1.697l2.995-2.651-2.995-2.651a1.2 1.2 0 0 1 1.697-1.697L10 8.157l2.651-2.995a1.2 1.2 0 1 1 1.697 1.697L11.854 10l2.995 2.651a1.2 1.2 0 0 1 0 1.698z" />
            </svg>
        </button>
    </div>
    @endif

    {{-- Tabel --}}
    <div class="bg-white shadow-lg rounded-lg overflow-hidden">
        <div class="px-6 py-4 bg-indigo-600 text-white font-semibold">Daftar Wali</div>
        <div class="p-6 overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No HP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Alamat</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($walis as $wali)
                    <tr class="hover:bg-gray-100">
                        <td class="px-6 py-4">{{ $loop->iteration }}</td>
                        <td class="px-6 py-4">{{ $wali->nama }}</td>
                        <td class="px-6 py-4">{{ $wali->email }}</td>
                        <td class="px-6 py-4">{{ $wali->no_hp }}</td>
                        <td class="px-6 py-4">{{ $wali->alamat }}</td>
                        <td class="px-6 py-4 flex space-x-2">
                            <button @click="openDetail({{ $wali->toJson() }})"
                                class="p-2 text-indigo-600 border border-indigo-600 rounded hover:bg-indigo-600 hover:text-white">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-gray-500 py-4">Belum ada data Wali</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4 flex justify-center">
                {{ $walis->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Detail --}}
    <template x-if="showDetail">
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60">
            <div class="bg-white rounded-lg w-full max-w-lg shadow-lg">
                <div class="flex justify-between items-center bg-blue-600 text-white px-4 py-3">
                    <h2 class="font-semibold text-lg">Detail Wali</h2>
                    <button @click="closeModals()"><i class="fas fa-times"></i></button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Nama</label>
                        <p class="mt-1 block w-full border-0 p-0 text-sm text-gray-900" x-text="detail.nama"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Email</label>
                        <p class="mt-1 block w-full border-0 p-0 text-sm text-gray-900" x-text="detail.email"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">No HP</label>
                        <p class="mt-1 block w-full border-0 p-0 text-sm text-gray-900" x-text="detail.no_hp"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Alamat</label>
                        <p class="mt-1 block w-full border-0 p-0 text-sm text-gray-900" x-text="detail.alamat"></p>
                    </div>
                </div>

                <div class="flex justify-end space-x-2 p-4">
                    <button type="button" @click="closeModals()" class="bg-gray-200 px-4 py-2 rounded">Tutup</button>
                </div>
            </div>
        </div>
    </template>
</div>

<script>
function guruWaliManager() {
    return {
        showDetail: false,
        detail: {},

        openDetail(data) {
            this.closeModals();
            this.detail = data;
            this.showDetail = true;
        },

        closeModals() {
            this.showDetail = false;
            this.detail = {};
        },
    }
}
</script>

@endsection