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
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-800">Kelola Data Siswa</h2>
            @php
                $activeYear = \App\Models\Akademik\TahunAjaran::active()->first();
            @endphp
            @if($activeYear)
                <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Tahun Ajaran Aktif: <strong>{{ $activeYear->nama }} - {{ $activeYear->semester }}</strong></span>
                </div>
            @else
                <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span>Tidak ada tahun ajaran aktif</span>
                </div>
            @endif
        </div>
        
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
                <button @click="openCreateModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-sm flex items-center gap-2">
                    <i class="fas fa-plus"></i> Siswa Baru
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
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">NISN</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Jenjang</th>
                        <th class="px-4 py-3 text-left font-bold text-gray-500 uppercase tracking-wider">Kelas</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($santris as $santri)
                        @php
                            $profile = $santri->santriprofile ?? null;
                            
                            // Prefer the active class for display (kelasAktif) which filters by active tahun ajaran
                            $kelasObj = $profile->kelasAktif ? ($profile->kelasAktif->kelas ?? null) : ($profile->santriKelas?->kelas ?? null);
                            $namaKelas = $kelasObj->level ?? '-';
                            $jenjang = $profile->jenjang ?? '-';
                            
                            $waliName = $profile->waliProfile->nama ?? '-';
                            
                            // Load counts for frontend logic
                            $santri->loadCount(['santriprofile as absensis_count' => function ($query) {
                                $query->has('absensis');
                            }, 'santriprofile as penilaians_count' => function ($query) {
                                $query->has('penilaians');
                            }]);
                            
                            // Manual check because loadCount on nested relation is tricky in blade loop optimization
                            // But since we eager loaded in controller, we can access counts if we did it right.
                            // Let's rely on the controller's eager loading if possible, or just pass the counts.
                            // Actually, in controller I used a closure which might not append attribute directly to santri model.
                            // Let's just pass the raw counts if available or check existence.
                            
                            $hasRelations = ($profile && ($profile->absensis_count > 0 || $profile->penilaians_count > 0));
                            
                            $jsonData = $santri->load(['santriprofile.waliProfile', 'santriprofile.santriKelas.kelas']);
                            $jsonData->has_relations = $hasRelations;
                        @endphp
                        <tr class="hover:bg-blue-50 transition">
                            <td class="px-4 py-3 text-gray-600 font-mono">{{ $santri->nisn }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $profile->nama ?? '-' }}
                                <div class="text-xs text-gray-400 mt-0.5">{{ $waliName }} (Wali)</div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">
                                <span class="bg-indigo-100 text-indigo-700 px-2 py-1 rounded text-xs font-medium border border-indigo-200">
                                    {{ $jenjang }}
                                </span>
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
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    
                                    <button @click="deleteSantri({{ $santri->id }}, '{{ $profile->nama ?? $santri->username }}')" class="text-red-600 hover:bg-red-100 p-1.5 rounded-md transition" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
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
            <form @submit.prevent="submitForm" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-700">
                
                {{-- Input NIS --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">NIS <span class="text-red-500">*</span></label>
                    <input type="text" x-model="formData.nisn" required class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3">
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

                {{-- Input Jenjang --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Jenjang <span class="text-red-500">*</span></label>
                    <select x-model="formData.jenjang" required class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3">
                        <option value="">-- Pilih Jenjang --</option>
                        <option value="SMP">SMP (Kelas 7-9)</option>
                        <option value="SMA">SMA (Kelas 10-12)</option>
                    </select>
                </div>
                
                {{-- Input No HP --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">No HP</label>
                    <input type="text" x-model="formData.no_hp" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3">
                </div>

                {{-- Input Status --}}
                <div>
                    <label class="block text-xs font-bold text-gray-700 mb-1">Status <span class="text-red-500">*</span></label>
                    <select x-model="formData.status" class="w-full border-gray-300 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3">
                        <option value="aktif">Aktif</option>
                        <option value="non-aktif">Non-Aktif</option>
                        <option value="lulus">Lulus</option>
                        <option value="dropout">Dropout</option>
                    </select>
                </div>

                {{-- Dropdown Wali --}}
                <div class="relative" 
                     x-data='dropdownSearch({ 
                         items: @json($walis), 
                         initialId: formData.wali_id, 
                         initialName: formData.wali_name 
                     })'
                     @click.outside="open = false"> 
                    
                    <label class="block text-xs font-bold text-gray-700 mb-1">Wali Santri <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text" 
                                x-model="search"
                                @focus="open = true" 
                                @input="open = true" 
                                @keydown.escape="open = false"
                                placeholder="Pilih atau cari wali..." 
                                class="w-full rounded-lg border-gray-300 text-sm focus:border-blue-500 focus:ring-blue-500 py-2 px-3" 
                                autocomplete="off">
                        
                        <div x-show="open" 
                             x-transition
                             class="absolute z-50 mt-1 w-full bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="select(item); formData.wali_id = item.id; formData.wali_name = item.nama" 
                                     class="px-3 py-2 hover:bg-blue-50 cursor-pointer text-gray-700 text-sm border-b border-gray-50 last:border-0">
                                    <span x-text="item.nama"></span>
                                </div>
                            </template>
                            <div x-show="filteredItems.length === 0" class="px-3 py-2 text-gray-400 text-xs italic">
                                Data tidak ditemukan.
                            </div>
                        </div>
                    </div>
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
        
        Alpine.data('santriHandler', () => ({
            isModalOpen: false,
            modalTitle: '',
            modalActionUrl: '',
            modalMethod: 'POST',
            isLocked: false,
            
            formData: {
                id: null,
                nisn: '', nama: '', jenjang: '', no_hp: '', alamat: '', status: 'aktif',
                password: '',
                wali_id: null, wali_name: ''
                // REMOVED: kelas_id and kelas_name
            },

            openCreateModal() {
                this.modalTitle = 'Tambah Santri Baru';
                this.modalActionUrl = "{{ route('santri.store') }}";
                this.modalMethod = 'POST';
                this.isLocked = false;
                this.resetForm();
                this.isModalOpen = true;
            },

            openEditModal(data) {
                this.modalTitle = 'Edit Data Santri';
                this.modalActionUrl = `/santri/${data.id}`;
                this.modalMethod = 'PUT';
                
                // Cek relasi untuk lock kelas
                this.isLocked = data.has_relations;

                this.formData = {
                    id: data.id,
                    nisn: data.nisn,
                    nama: data.santriprofile?.nama || '',
                    jenjang: data.santriprofile?.jenjang || '',
                    no_hp: data.santriprofile?.no_hp || '',
                    alamat: data.santriprofile?.alamat || '',
                    status: data.santriprofile?.status || 'aktif',
                    password: '', 
                    wali_id: data.santriprofile?.wali_profile_id,
                    wali_name: data.santriprofile?.wali_profile?.nama || ''
                    // REMOVED: kelas_id and kelas_name
                };
                this.isModalOpen = true;
            },

            resetForm() {
                this.formData = {
                    id: null,
                    nisn: '', nama: '', jenjang: '', no_hp: '', alamat: '', status: 'aktif',
                    password: '', 
                    wali_id: null, wali_name: ''
                    // REMOVED: kelas_id and kelas_name
                };
            },

            async submitForm() {
                try {
                    const response = await axios({
                        method: this.modalMethod,
                        url: this.modalActionUrl,
                        data: {
                            username: this.formData.nisn, // Username = NISN
                            nisn: this.formData.nisn,
                            nama: this.formData.nama,
                            jenjang: this.formData.jenjang,
                            no_hp: this.formData.no_hp,
                            alamat: this.formData.alamat,
                            status: this.formData.status,
                            password: this.formData.password,
                            wali_profile_id: this.formData.wali_id
                            // REMOVED: kelas_id
                        }
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

            async deleteSantri(id, nama) {
                const result = await Swal.fire({
                    title: 'Hapus Santri?',
                    text: `Anda yakin ingin menghapus data santri "${nama}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                });

                if (result.isConfirmed) {
                    try {
                        const response = await axios.delete(`/santri/${id}`);
                        Swal.fire('Terhapus!', response.data.message, 'success').then(() => {
                            location.reload();
                        });
                    } catch (error) {
                        console.error(error);
                        let msg = 'Gagal menghapus data.';
                        if (error.response && error.response.data.message) {
                            msg = error.response.data.message;
                        }
                        Swal.fire('Error', msg, 'error');
                    }
                }
            }
        }));

        Alpine.data('dropdownSearch', (config) => ({
            items: config.items,
            search: config.initialName || '',
            open: false,

            get filteredItems() {
                if (this.search === '' || this.search === null) return this.items;
                return this.items.filter(item => item.nama.toLowerCase().includes(this.search.toLowerCase()));
            },

            select(item) {
                this.search = item.nama;
                this.open = false;
            },
            
            init() {
                this.$watch(() => config.initialName, (val) => { this.search = val; });
            }
        }));
    });
</script>
@endsection