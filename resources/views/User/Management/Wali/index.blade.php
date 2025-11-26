@extends('layouts.app')

@section('title', 'Kelola Data Wali Murid')

@section('content')
{{-- Deklarasi Alpine.js: Gunakan x-data untuk memuat waliHandler --}}
<div x-data="waliHandler()" x-init="
    @if($errors->any()) 
        openModalOnValidationFailure(); 
    @endif
" x-cloak>
    
    {{-- ================= HEADER & FILTER AREA ================= --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Kelola Data Wali Murid</h2>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
            
            {{-- FORM PENCARIAN (Server Side) --}}
            <form action="{{ url()->current() }}" method="GET" class="flex gap-2 w-full md:w-auto">
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Username atau Nama..." class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm pl-3 pr-10">
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </form>

            {{-- TOMBOL AKSI --}}
            <div class="flex flex-wrap gap-2">
                <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-plus"></i> Wali Baru
                </button>
            </div>
        </div>
    </div>

    {{-- ================= TABEL DATA ================= --}}
    @if ($walis->isEmpty())
        <div class="bg-yellow-50 text-yellow-800 p-6 rounded-lg border border-yellow-200 text-center">
            <p class="font-medium">Tidak ada data wali ditemukan.</p>
            @if(request('search'))
                <a href="{{ url()->current() }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Reset Filter</a>
            @endif
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">No HP</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($walis as $wali)
                        @php
                            $profile = $wali->waliProfile ?? null;
                            $jsonData = $wali->load('waliProfile');
                            
                            // Hitung relasi untuk locking delete button
                            $isLocked = ($profile && $profile->santri_profiles_count > 0);
                            $jsonData->is_locked = $isLocked;
                        @endphp
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-4 py-3 text-gray-600 font-mono">{{ $wali->username }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $profile->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $profile->no_hp ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 truncate max-w-xs">
                                {{ $profile->alamat ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center items-center space-x-2">
                                    <button @click='openEditModal(@json($jsonData))' class="text-blue-600 hover:bg-blue-100 p-1.5 rounded-md transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    @if($isLocked)
                                        <button @click="showCannotDeleteInfo()" class="text-gray-400 cursor-not-allowed p-1.5" title="Tidak dapat dihapus (Ada Relasi)">
                                            <i class="fas fa-lock"></i>
                                        </button>
                                    @else
                                        <button @click="deleteWali({{ $wali->id }}, '{{ $profile->nama ?? $wali->username }}')" class="text-red-600 hover:bg-red-100 p-1.5 rounded-md transition" title="Hapus">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 px-2">
            {{ $walis->withQueryString()->links('vendor.pagination.tailwind') }}
        </div>
    @endif

    {{-- ================= MODAL (CREATE / EDIT) ================= --}}
    <div x-show="isModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl mx-4 overflow-hidden transform transition-all" @click.away="isModalOpen = false">
            
            {{-- Modal Header --}}
            <div class="flex justify-between items-center px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-800" x-text="modalTitle"></h3>
                <button @click="isModalOpen = false" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
            </div>

            {{-- Modal Body --}}
            <form @submit.prevent="submitForm" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                
                {{-- Input Username --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.username" required class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3">
                </div>
                
                {{-- Input Password --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1" x-text="modalMethod === 'POST' ? 'Password Awal*' : 'Password Baru'"></label>
                    <input type="password" x-model="formData.password" placeholder="Isi hanya jika ingin ubah" :required="modalMethod === 'POST'" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3">
                    <p class="text-[10px] text-gray-400 mt-1" x-show="modalMethod === 'PUT'">Kosongkan jika tidak ingin mengubah password.</p>
                </div>

                {{-- Input Nama Lengkap --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.nama" required class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3">
                </div>
                
                {{-- Input No HP --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">No HP</label>
                    <input type="text" x-model="formData.no_hp" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3">
                </div>

                {{-- Input Alamat --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-bold text-gray-700 mb-1">Alamat</label>
                    <textarea x-model="formData.alamat" rows="2" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3"></textarea>
                </div>

                {{-- Modal Footer --}}
                <div class="md:col-span-2 flex justify-end space-x-3 pt-4 border-t mt-2">
                    <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-lg bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- ================= SCRIPTS ================= --}}
<script>
    document.addEventListener('alpine:init', () => {
        
        Alpine.data('waliHandler', () => ({
            isModalOpen: false,
            modalTitle: '',
            modalActionUrl: '',
            modalMethod: 'POST',
            
            formData: {
                id: null,
                username: '', nama: '', no_hp: '', alamat: '',
                password: ''
            },

            openCreateModal() {
                this.modalTitle = 'Tambah Wali Baru';
                this.modalActionUrl = "{{ route('wali.store') }}";
                this.modalMethod = 'POST';
                this.resetForm();
                this.isModalOpen = true;
            },

            openEditModal(data) {
                this.modalTitle = 'Edit Data Wali';
                this.modalActionUrl = `/wali/${data.id}`;
                this.modalMethod = 'PUT';

                this.formData = {
                    id: data.id,
                    username: data.username,
                    nama: data.wali_profile?.nama || '',
                    no_hp: data.wali_profile?.no_hp || '',
                    alamat: data.wali_profile?.alamat || '',
                    password: ''
                };
                this.isModalOpen = true;
            },

            resetForm() {
                this.formData = {
                    id: null,
                    username: '', nama: '', no_hp: '', alamat: '',
                    password: ''
                };
            },

            async submitForm() {
                try {
                    const response = await axios({
                        method: this.modalMethod,
                        url: this.modalActionUrl,
                        data: this.formData
                    });

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.data.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });

                } catch (error) {
                    console.error(error);
                    let msg = 'Terjadi kesalahan pada server.';
                    if (error.response && error.response.data.message) {
                        msg = error.response.data.message;
                    }
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: msg
                    });
                }
            },

            async deleteWali(id, nama) {
                const result = await Swal.fire({
                    title: 'Hapus Wali?',
                    text: `Anda yakin ingin menghapus data wali "${nama}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                });

                if (result.isConfirmed) {
                    try {
                        const response = await axios.delete(`/wali/${id}`);
                        Swal.fire('Terhapus!', response.data.message, 'success').then(() => {
                            location.reload();
                        });
                    } catch (error) {
                        console.error(error);
                        let msg = 'Gagal menghapus data.';
                        if (error.response && error.response.data.message) {
                            msg = error.response.data.message;
                        }
                        Swal.fire('Gagal', msg, 'error');
                    }
                }
            },

            showCannotDeleteInfo() {
                Swal.fire({
                    icon: 'info',
                    title: 'Tidak Dapat Dihapus',
                    text: 'Wali ini masih memiliki santri terkait. Silakan hapus relasi tersebut terlebih dahulu.'
                });
            }
        }));
    });
</script>
@endsection