@extends('layouts.app')

@section('title', 'Kelola Data Guru')

@section('content')
{{-- Deklarasi Alpine.js: Gunakan x-data untuk memuat guruHandler --}}
<div x-data="guruHandler()" x-init="
    @if($errors->any()) 
        openModalOnValidationFailure(); 
    @endif
" x-cloak>
    
    {{-- ================= HEADER & FILTER AREA ================= --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Kelola Data Guru</h2>
        
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
                <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm font-medium transition shadow-sm flex items-center gap-1">
                    <span>+</span> Guru Baru
                </button>
                
                <button x-show="selectedItems.length > 0" 
                        @click="alert('Fitur hapus massal ID: ' + selectedItems.join(','))"
                        x-transition
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm font-medium transition shadow-sm">
                    Hapus (<span x-text="selectedItems.length"></span>)
                </button>
            </div>
        </div>
    </div>

    {{-- ================= TABEL DATA ================= --}}
    @if ($gurus->isEmpty())
        <div class="bg-yellow-50 text-yellow-800 p-6 rounded-lg border border-yellow-200 text-center">
            <p class="font-medium">Tidak ada data guru ditemukan.</p>
            @if(request('search'))
                <a href="{{ url()->current() }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">Reset Filter</a>
            @endif
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg overflow-hidden border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">
                            <input type="checkbox" @change="toggleAll" x-model="selectAll" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Username</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Jabatan</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">No HP</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($gurus as $guru)
                        @php
                            $profile = $guru->guruProfile ?? null;
                            $jsonData = $guru->load('guruProfile');
                        @endphp
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" value="{{ $guru->id }}" x-model="selectedItems" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3 text-gray-600 font-mono">{{ $guru->username }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $profile->nama ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $profile->jabatan ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                {{ $profile->no_hp ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center items-center space-x-2">
                                    <button @click='openEditModal(@json($jsonData))' class="text-blue-600 hover:bg-blue-100 p-1.5 rounded-md transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    
                                    <form action="{{ route('guru.destroy', $guru->id) }}" method="POST" class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" onclick="return confirm('Yakin ingin menghapus data ini?')" class="text-red-600 hover:bg-red-100 p-1.5 rounded-md transition" title="Hapus">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4 px-2">
            {{ $gurus->withQueryString()->links('vendor.pagination.tailwind') }}
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
            <form :action="modalActionUrl" method="POST" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                @csrf
                <input type="hidden" name="_method" :value="modalMethod">
                
                {{-- Input Username --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Username <span class="text-red-500">*</span></label>
                    <input type="text" name="username" x-model="formData.username" required class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('username')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                
                {{-- Input Password --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1" x-text="modalMethod === 'POST' ? 'Password Awal*' : 'Password Baru'"></label>
                    <input type="password" name="password" x-model="formData.password" placeholder="Isi hanya jika ingin ubah/buat" :required="modalMethod === 'POST'" class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Input Nama Lengkap --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="nama" x-model="formData.nama" required class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                
                {{-- Input Konfirmasi Password --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" x-model="formData.password_confirmation" :required="modalMethod === 'POST'" class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('password_confirmation')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Input Jabatan --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jabatan <span class="text-red-500">*</span></label>
                    <input type="text" name="jabatan" x-model="formData.jabatan" required class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('jabatan')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Input No HP --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">No HP</label>
                    <input type="text" name="no_hp" x-model="formData.no_hp" class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('no_hp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Input Alamat --}}
                <div class="md:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Alamat</label>
                    <textarea name="alamat" x-model="formData.alamat" rows="2" class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    @error('alamat')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Modal Footer --}}
                <div class="md:col-span-2 flex justify-end space-x-3 pt-4 border-t mt-2">
                    <button type="button" @click="isModalOpen = false" class="px-4 py-2 rounded-md bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium transition">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-medium transition shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>

{{-- ================= SCRIPTS ================= --}}
<script>
    document.addEventListener('alpine:init', () => {
        
        // --- HANDLER DATA UTAMA ---
        Alpine.data('guruHandler', () => ({
            isModalOpen: false,
            modalTitle: '',
            modalActionUrl: '',
            modalMethod: 'POST',
            
            formData: {
                username: '', nama: '', jabatan: '', no_hp: '', alamat: '',
                password: '', password_confirmation: ''
            },

            selectAll: false,
            selectedItems: [],
            toggleAll() {
                this.selectedItems = this.selectAll ? @json($gurus->pluck('id')) : [];
            },

            /**
             * 🔹 Membuka modal dan mengisi data lama jika terjadi kegagalan validasi.
             */
            openModalOnValidationFailure() {
                const oldInput = @json(session()->getOldInput());
                const isEdit = oldInput._method && oldInput._method.toUpperCase() === 'PUT';
                
                this.modalTitle = isEdit ? 'Edit Data Guru (Kesalahan Input)' : 'Tambah Guru Baru (Kesalahan Input)';
                
                // Cari ID guru yang sedang diedit (untuk URL action)
                let guruId = @json(request()->route('guru')?->id);
                if(isEdit && guruId) {
                    this.modalActionUrl = `/guru/${guruId}`;
                } else {
                    this.modalActionUrl = "{{ route('guru.store') }}"; 
                }
                
                this.modalMethod = isEdit ? 'PUT' : 'POST';

                this.formData = {
                    username: oldInput.username || '',
                    nama: oldInput.nama || '',
                    jabatan: oldInput.jabatan || '',
                    no_hp: oldInput.no_hp || '',
                    alamat: oldInput.alamat || '',
                    password: '', 
                    password_confirmation: ''
                };

                this.isModalOpen = true;
            },

            openCreateModal() {
                this.modalTitle = 'Tambah Guru Baru';
                this.modalActionUrl = "{{ route('guru.store') }}";
                this.modalMethod = 'POST';
                this.resetForm();
                this.isModalOpen = true;
            },

            openEditModal(data) {
                this.modalTitle = 'Edit Data Guru';
                this.modalActionUrl = `/guru/${data.id}`;
                this.modalMethod = 'PUT';

                this.formData = {
                    username: data.username,
                    nama: data.guru_profile?.nama || '',
                    jabatan: data.guru_profile?.jabatan || '',
                    no_hp: data.guru_profile?.no_hp || '',
                    alamat: data.guru_profile?.alamat || '',
                    password: '', 
                    password_confirmation: ''
                };
                this.isModalOpen = true;
            },

            resetForm() {
                this.formData = {
                    username: '', nama: '', jabatan: '', no_hp: '', alamat: '',
                    password: '', password_confirmation: ''
                };
            }
        }));

        // SweetAlert Toast Setup
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        });

        // Cek Session dari Controller Laravel
        @if(session('success'))
            Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
        @endif

        @if(session('error'))
            Toast.fire({ icon: 'error', title: "{{ session('error') }}" });
        @endif

        // Tampilkan error validasi jika ada
        @if($errors->any())
            Toast.fire({ icon: 'warning', title: 'Periksa kembali inputan Anda.' });
        @endif
    });
</script>
@endsection
