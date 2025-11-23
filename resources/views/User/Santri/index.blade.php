@extends('layouts.app')

@section('title', 'Kelola Data Siswa')

@section('content')
{{-- Deklarasi Alpine.js: Gunakan x-data untuk memuat santriHandler --}}
<div x-data="santriHandler()" x-init="
    @if($errors->any()) 
        openModalOnValidationFailure(); 
    @endif
" x-cloak>
    
    {{-- ================= HEADER & FILTER AREA ================= --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">Kelola Data Siswa</h2>
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white p-4 rounded-lg shadow-sm border border-gray-100">
            
            {{-- FORM FILTER & PENCARIAN (Server Side) --}}
            <form action="{{ url()->current() }}" method="GET" class="flex gap-2 w-full md:w-auto">
                
                {{-- Filter Kelas --}}
                <select name="filter_kelas" onchange="this.form.submit()" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm cursor-pointer">
                    <option value="">Semua Kelas</option>
                    @foreach($kelas as $k)
                        @php 
                        $kId = $k['id'] ?? $k->id; 
                        $kNama = $k['nama'] ?? $k['level'];
                        @endphp
                        <option value="{{ $kId }}" {{ request('filter_kelas') == $kId ? 'selected' : '' }}>
                            {{ $kNama }}
                        </option>
                    @endforeach
                </select>

                {{-- Search Input --}}
                <div class="relative w-full md:w-64">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari NIS atau Nama..." class="w-full border-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-md shadow-sm text-sm pl-3 pr-10">
                    <button type="submit" class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 hover:text-blue-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </button>
                </div>
            </form>

            {{-- TOMBOL AKSI --}}
            <div class="flex flex-wrap gap-2">
                <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2 rounded text-sm font-medium transition shadow-sm flex items-center gap-1">
                    <span>+</span> Siswa Baru
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
    @if ($santris->isEmpty())
        <div class="bg-yellow-50 text-yellow-800 p-6 rounded-lg border border-yellow-200 text-center">
            <p class="font-medium">Tidak ada data santri ditemukan.</p>
            @if(request('filter_kelas') || request('search'))
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
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">NISN</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($santris as $santri)
                        @php
                            $profile = $santri->santriprofile ?? null;
                            
                            $kelasObj = $profile->santriKelas->kelas ?? null; 
                            $namaKelas = $kelasObj->level ?? '-';
                            
                            $waliName = $profile->waliProfile->nama ?? '-';
                            
                            $jsonData = $santri->load(['santriprofile.waliProfile', 'santriprofile.santriKelas.kelas']);
                        @endphp
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-4 py-3 text-center">
                                <input type="checkbox" value="{{ $santri->id }}" x-model="selectedItems" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-4 py-3 text-gray-600 font-mono">{{ $santri->nisn }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $profile->nama ?? '-' }}
                                <div class="text-xs text-gray-400 mt-0.5">{{ $waliName }} (Wali)</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <span class="bg-gray-100 text-gray-600 px-2 py-1 rounded text-xs border border-gray-200">
                                    {{ $namaKelas }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php
                                    $status = strtolower($profile->status ?? '');
                                    $color = match($status) {
                                        'aktif' => 'bg-green-100 text-green-800 border-green-200',
                                        'non-aktif' => 'bg-red-100 text-red-800 border-red-200',
                                        'lulus' => 'bg-blue-100 text-blue-800 border-blue-200',
                                        default => 'bg-gray-100 text-gray-800 border-gray-200'
                                    };
                                @endphp
                                <span class="px-2 py-1 rounded-full text-xs font-medium border {{ $color }}">
                                    {{ ucfirst($status ?: '-') }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center items-center space-x-2">
                                    <button @click='openEditModal(@json($jsonData))' class="text-blue-600 hover:bg-blue-100 p-1.5 rounded-md transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    
                                    <form action="{{ route('santri.destroy', $santri->id) }}" method="POST" class="inline">
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
            {{ $santris->withQueryString()->links('vendor.pagination.tailwind') }}
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
                <input type="hidden" name="id" x-model="formData.id">
                
                {{-- Input NIS --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">NIS <span class="text-red-500">*</span></label>
                    <input type="text" name="nisn" x-model="formData.nisn" required class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('nisn')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                
                {{-- Input Password (Baru) --}}
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

                {{-- Input No HP --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">No HP</label>
                    <input type="text" name="no_hp" x-model="formData.no_hp" class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                    @error('no_hp')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Input Status --}}
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Status <span class="text-red-500">*</span></label>
                    <select name="status" x-model="formData.status" class="w-full border-gray-300 rounded-md text-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="aktif">Aktif</option>
                        <option value="non-aktif">Non-Aktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="dropout">Dropout</option>
                    </select>
                    @error('status')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Dropdown Wali (FIXED x-data binding) --}}
                <div class="relative mb-4" 
                     x-data='dropdownSearch({ 
                         items: @json($walis), 
                         initialId: $data.formData.wali_id, 
                         initialName: $data.formData.wali_name 
                     })'
                     @click.outside="open = false"> 
                    
                    <label class="block text-xs font-medium text-gray-500 mb-1">Wali Santri <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" 
                                x-model="search"
                                @focus="open = true" 
                                @input="open = true" 
                                @keydown.escape="open = false"
                                placeholder="Pilih atau cari wali..." 
                                class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" 
                                autocomplete="off">
                        
                        <input type="hidden" name="wali_profile_id" x-model="selectedId">
                        
                        <div x-show="open" 
                             x-transition.opacity.duration.200ms
                             class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                            
                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="select(item)" 
                                     class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-gray-700 text-sm border-b border-gray-50 last:border-0"
                                     :class="{'bg-blue-50 font-semibold': selectedId == item.id}">
                                    <span x-text="item.nama"></span>
                                </div>
                            </template>
                            <div x-show="filteredItems.length === 0" class="px-3 py-2 text-gray-400 text-xs italic">
                                Data tidak ditemukan.
                            </div>
                        </div>
                    </div>
                    @error('wali_profile_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Dropdown Kelas (FIXED x-data binding) --}}
                <div class="relative mb-4" 
                     x-data='dropdownSearch({ 
                         items: @json($kelas), 
                         initialId: $data.formData.kelas_id, 
                         initialName: $data.formData.kelas_name 
                     })'
                     @click.outside="open = false">
                    
                    <label class="block text-xs font-medium text-gray-500 mb-1">Kelas <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" 
                                x-model="search" 
                                @focus="open = true"
                                @input="open = true"
                                @keydown.escape="open = false"
                                placeholder="Pilih atau cari kelas..." 
                                class="w-full rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500" 
                                autocomplete="off">
                                
                        <input type="hidden" name="kelas_id" x-model="selectedId">
                        
                        <div x-show="open" 
                             x-transition.opacity.duration.200ms
                             class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-md shadow-lg max-h-48 overflow-y-auto">
                           <template x-for="item in filteredItems" :key="item.id">
                                <div @click="select(item)" 
                                     class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-gray-700 text-sm border-b border-gray-50 last:border-0"
                                     :class="{'bg-blue-50 font-semibold': selectedId == item.id}">
                                    <span x-text="item.nama"></span>
                                </div>
                           </template>
                           <div x-show="filteredItems.length === 0" class="px-3 py-2 text-gray-400 text-xs italic">
                                Data tidak ditemukan.
                           </div>
                        </div>
                    </div>
                    @error('kelas_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Input Alamat (Sekarang Opsional) --}}
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
        // --- HANDLER DATA UTAMA ---
        Alpine.data('santriHandler', () => ({
            isModalOpen: false,
            modalTitle: '',
            modalActionUrl: '',
            modalMethod: 'POST',
            
            formData: {
                id: null, // Added ID field
                nisn: '', nama: '', no_hp: '', alamat: '', status: 'aktif',
                password: '', password_confirmation: '',
                wali_id: null, wali_name: '', 
                kelas_id: null, kelas_name: ''
            },

            selectAll: false,
            selectedItems: [],
            toggleAll() {
                this.selectedItems = this.selectAll ? @json($santris->pluck('id')) : [];
            },

            /**
             * 🔹 Membuka modal dan mengisi data lama jika terjadi kegagalan validasi.
             */
            openModalOnValidationFailure() {
                const oldInput = @json(session()->getOldInput());
                const isEdit = oldInput._method && oldInput._method.toUpperCase() === 'PUT';
                
                this.modalTitle = isEdit ? 'Edit Data Santri (Kesalahan Input)' : 'Tambah Santri Baru (Kesalahan Input)';
                
                // Cari ID santri dari old input (jika ada) atau dari route parameter (jarang terjadi di index)
                let santriId = oldInput.id || @json(request()->route('santri')?->id);
                
                if(isEdit && santriId) {
                    this.modalActionUrl = `/santri/${santriId}`;
                } else {
                    this.modalActionUrl = "{{ route('santri.store') }}"; 
                }
                
                this.modalMethod = isEdit ? 'PUT' : 'POST';

                this.formData = {
                    id: santriId || null,
                    nisn: oldInput.nisn || '',
                    nama: oldInput.nama || '',
                    no_hp: oldInput.no_hp || '',
                    alamat: oldInput.alamat || '',
                    status: oldInput.status || 'aktif',
                    password: '', 
                    password_confirmation: '', 
                    wali_id: oldInput.wali_profile_id || null, 
                    wali_name: '', 
                    kelas_id: oldInput.kelas_id || null, 
                    kelas_name: '', 
                };

                // REVISI: Cari Nama Wali dan Kelas dari ID lama (oldInput)
                // Data Wali
                if (oldInput.wali_profile_id) {
                    const walisData = @json($walis);
                    const wali = walisData.find(w => w.id == oldInput.wali_profile_id);
                    this.formData.wali_name = wali ? wali.nama : '';
                }
                
                // Data Kelas (Hanya gunakan Level)
                if (oldInput.kelas_id) {
                    const kelasData = @json($kelas);
                    const kelas = kelasData.find(k => k.id == oldInput.kelas_id);
                    
                    if (kelas) {
                        this.formData.kelas_name = kelas.nama || '';
                    } else {
                        this.formData.kelas_name = '';
                    }
                }

                this.isModalOpen = true;
            },

            openCreateModal() {
                this.modalTitle = 'Tambah Santri Baru';
                this.modalActionUrl = "{{ route('santri.store') }}";
                this.modalMethod = 'POST';
                this.resetForm();
                this.isModalOpen = true;
            },

            openEditModal(data) {
                this.modalTitle = 'Edit Data Santri';
                this.modalActionUrl = `/santri/${data.id}`;
                this.modalMethod = 'PUT';

                let namaKelas = '';
                if(data.santriprofile?.santri_kelas?.kelas) {
                    const k = data.santriprofile.santri_kelas.kelas;
                    // Tampilkan hanya Level (misal: 1A)
                    namaKelas = k.level; 
                }

                this.formData = {
                    id: data.id, // Set ID
                    nisn: data.nisn,
                    nama: data.santriprofile?.nama || '',
                    no_hp: data.santriprofile?.no_hp || '',
                    alamat: data.santriprofile?.alamat || '',
                    status: data.santriprofile?.status || 'aktif',
                    password: '', 
                    password_confirmation: '', 
                    wali_id: data.santriprofile?.wali_profile_id,
                    wali_name: data.santriprofile?.wali_profile?.nama || '',
                    kelas_id: data.santriprofile?.santri_kelas?.kelas_id,
                    kelas_name: namaKelas
                };
                this.isModalOpen = true;
            },

            resetForm() {
                this.formData = {
                    id: null,
                    nisn: '', nama: '', no_hp: '', alamat: '', status: 'aktif',
                    password: '', password_confirmation: '', 
                    wali_id: null, wali_name: '', kelas_id: null, kelas_name: ''
                };
            }
        }));

        // --- DROPDOWN SEARCH GOOGLE-STYLE ---
        Alpine.data('dropdownSearch', (config) => ({
            items: config.items,
            search: config.initialName || '',
            selectedId: config.initialId || null,
            open: false,

            init() {
                // Watcher untuk sinkronisasi data dari tombol Edit (parent scope)
                this.$watch(() => this.$root.querySelector('input[name="wali_profile_id"], input[name="kelas_id"]').value, () => {
                     this.selectedId = config.initialId;
                     this.search = config.initialName;
                });

                this.$watch(() => config.initialId, (val) => { 
                    this.selectedId = val; 
                    if (val === null) this.search = '';
                });
                this.$watch(() => config.initialName, (val) => { 
                    this.search = val; 
                });
                
                // Watcher: Jika user menghapus text search manual sampai habis, reset ID
                this.$watch('search', (val) => {
                    if(val === '' && this.selectedId !== null) {
                        this.selectedId = null;
                        // Hapus ID juga dari formData di parent scope
                        if (config.initialId === $data.formData.wali_id) {
                            $data.formData.wali_id = null;
                        } else if (config.initialId === $data.formData.kelas_id) {
                            $data.formData.kelas_id = null;
                        }
                    }
                });
            },

            get filteredItems() {
                if (this.search === '' || this.search === null) {
                    return this.items;
                }
                return this.items.filter(item => {
                    // Filter berdasarkan nama lengkap (Level Nama_Kelas atau Nama Wali)
                    return item.nama.toLowerCase().includes(this.search.toLowerCase());
                });
            },

            select(item) {
                this.selectedId = item.id;
                
                let displayNama = item.nama;
                
                this.search = displayNama;
                this.open = false;
                
                // Sinkronkan ke formData di parent scope
                if (config.initialId === $data.formData.wali_id) {
                    $data.formData.wali_id = item.id;
                    $data.formData.wali_name = item.nama;
                } else if (config.initialId === $data.formData.kelas_id) {
                    $data.formData.kelas_id = item.id;
                    $data.formData.kelas_name = displayNama; 
                }
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