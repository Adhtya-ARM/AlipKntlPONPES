@extends('layouts.app')

@section('title', 'Daftar Guru')

@section('content')

{{-- Container Utama Alpine --}}
<div x-data="{
        isModalOpen: false,
        modalTitle: '',
        modalActionUrl: '',
        modalMethod: '',
        isDetailMode: false,
        modalData: null,

        // --- Alpine Functions ---
        openCreateModal() {
            this.modalTitle = 'Tambah Guru Baru';
            this.modalActionUrl = '{{ route('guru.store') }}';
            this.modalMethod = 'POST';
            this.isDetailMode = false;
            this.modalData = null;
            this.isModalOpen = true;
            this.$nextTick(() => this.resetForm()); // Memastikan form bersih saat Create
        },

        openEditModal(guru) {
            const guruName = guru.guruProfile ? guru.guruProfile.nama : 'N/A';
            this.modalTitle = 'Edit Guru: ' + guruName;
            this.modalActionUrl = '{{ url('guru') }}/' + guru.id;
            this.modalMethod = 'PUT';
            this.isDetailMode = false;
            this.modalData = guru;
            this.isModalOpen = true;
            this.$nextTick(() => this.fillForm(guru, false)); // Mengisi form saat Edit
        },

        openDetailModal(guru) {
            const guruName = guru.guruProfile ? guru.guruProfile.nama : 'N/A';
            this.modalTitle = 'Detail Guru: ' + guruName;
            this.modalActionUrl = '';
            this.modalMethod = '';
            this.isDetailMode = true;
            this.modalData = guru;
            this.isModalOpen = true;
            this.$nextTick(() => this.fillForm(guru, true)); // Mengisi form dan Read-only saat Detail
        },

        // FUNGSI KRITIS: Mengisi semua field dengan data guru
        fillForm(guru, isReadOnly) {
            // Data Guru (tabel 'guru')
            document.getElementById('username').value = guru.username ?? '';

            // Data Profile (tabel 'guru_profiles' melalui relasi)
            if (guru.guruProfile) {
                document.getElementById('nama').value = guru.guruProfile.nama ?? '';
                document.getElementById('jabatan').value = guru.guruProfile.jabatan ?? '';
                document.getElementById('alamat').value = guru.guruProfile.alamat ?? '';
                document.getElementById('no_hp').value = guru.guruProfile.no_hp ?? '';
            } else {
                 this.resetFormFields(); // Bersihkan jika profile null
            }

            // Atur read-only/disabled
            const inputs = document.querySelectorAll('#guru-form-fields input, #guru-form-fields select, #guru-form-fields textarea');
            inputs.forEach(input => {
                // Pastikan input password tidak read-only/disabled di mode edit
                if (input.id !== 'password' && input.id !== 'password_confirmation') {
                    input.readOnly = isReadOnly;
                    if (input.tagName === 'SELECT' || input.tagName === 'TEXTAREA') {
                        input.disabled = isReadOnly;
                    }
                }
            });

            // Sembunyikan/tampilkan field password di mode Detail
            document.getElementById('password-group').style.display = isReadOnly ? 'none' : 'block';
            document.getElementById('password_confirmation-group').style.display = isReadOnly ? 'none' : 'block';
        },

        resetForm() {
            document.getElementById('username').value = '';
            document.getElementById('password').value = '';
            document.getElementById('password_confirmation').value = '';
            this.resetFormFields();

            // Tampilkan kembali password fields saat reset (mode Create)
            document.getElementById('password-group').style.display = 'block';
            document.getElementById('password_confirmation-group').style.display = 'block';

            // Reset read-only status
            const inputs = document.querySelectorAll('#guru-form-fields input, #guru-form-fields select, #guru-form-fields textarea');
            inputs.forEach(input => {
                input.readOnly = false;
                if (input.tagName === 'SELECT' || input.tagName === 'TEXTAREA') {
                    input.disabled = false;
                }
            });
        },

        resetFormFields() {
            // Membersihkan field profile
            ['nama', 'jabatan', 'alamat', 'no_hp'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.value = '';
            });
        }
        // -----------------------
    }">

    {{-- Header & Tombol Tambah --}}
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-semibold text-gray-700">Daftar Guru</h2>
        <button @click="openCreateModal()"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-150 shadow-md">
            + Tambah Guru Baru
        </button>
    </div>

    {{-- Tabel Data Guru --}}
    @if ($gurus->isEmpty())
    <div class="bg-yellow-100 text-yellow-800 p-4 rounded-md border border-yellow-400">
        Belum ada data guru yang tercatat.
    </div>
    @else
    <div class="bg-white shadow overflow-hidden rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jabatan
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No HP
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Mata
                        Pelajaran
                    </th>
                    <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($gurus as $guru)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                        {{ $loop->iteration + ($gurus->currentPage() - 1) * $gurus->perPage() }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $guru->guruProfile->nama ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $guru->username }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $guru->guruProfile->jabatan ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $guru->guruProfile->no_hp ?? '-' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <button
                            class="text-xs font-semibold px-2 py-1 rounded text-blue-600 hover:bg-blue-100 transition duration-150">
                            Pilih Mapel
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">

                        {{-- HARD FIX JSON BINDING --}}
                        <div x-data='{ guruData: @php echo json_encode($guru->load("guruProfile")->toArray()) @endphp
                            }' class="inline-flex space-x-2">

                            {{-- Detail Button --}}
                            <button @click="openDetailModal(guruData)"
                                class="text-xs font-semibold px-2 py-1 rounded text-indigo-600 hover:bg-indigo-100 transition duration-150">
                                Detail
                            </button>

                            {{-- Edit Button --}}
                            <button @click="openEditModal(guruData)"
                                class="text-xs font-semibold px-2 py-1 rounded text-yellow-600 hover:bg-yellow-100 transition duration-150">
                                Edit
                            </button>
                        </div>

                        {{-- Delete Form (ROUTE TUNGGAL: guru.destroy) --}}
                        <form action="{{ route('guru.destroy', $guru) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus guru {{ $guru->guruProfile->nama ?? 'ini' }}?')"
                                class="text-xs font-semibold px-2 py-1 rounded text-red-600 hover:bg-red-100 transition duration-150">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        @if (method_exists($gurus, 'links'))
        {{ $gurus->links('vendor.pagination.tailwind') }}
        @else
        <p class="text-sm text-gray-600">Menampilkan {{ count($gurus) }} data. Pagination dinonaktifkan.</p>
        @endif
    </div>
    @endif

    {{-- MODAL CONTAINER --}}
    <div x-show="isModalOpen" x-cloak x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">

        {{-- Background Overlay --}}
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="isModalOpen = false"></div>

        {{-- Modal Content Container --}}
        <div class="flex items-center justify-center min-h-screen">
            <div x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="bg-white rounded-lg shadow-xl transform transition-all max-w-lg w-full mx-4 my-8"
                @click.away="isModalOpen = false">

                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-900" x-text="modalTitle"></h3>
                    <p class="text-sm text-gray-500 mt-1"
                        x-text="isDetailMode ? 'Data lengkap guru bersifat baca-saja.' : (modalMethod == 'POST' ? 'Masukkan data guru baru.' : 'Ubah data guru ini.')">
                    </p>
                </div>

                {{-- Form: Digunakan untuk CREATE, EDIT, dan DISPLAY DETAIL --}}
                {{-- ID guru-form-fields sangat penting untuk fungsi fillForm dan resetForm --}}
                <form :action="modalActionUrl" method="POST" class="px-6 pb-6" id="guru-form-fields"
                    @submit.prevent="isDetailMode ? '' : $el.submit()">
                    @csrf

                    <template x-if="modalMethod == 'PUT'">
                        @method('PUT')
                    </template>

                    {{-- Input Fields --}}
                    <div class="py-6 space-y-4">

                        {{-- Username --}}
                        <div class="border-b border-gray-200 py-2">
                            <label for="username" class="block text-xs font-medium text-gray-500">Username</label>
                            <input type="text" id="username" name="username"
                                class="mt-1 block w-full border-0 p-0 text-sm text-gray-900 focus:ring-0" required>
                        </div>

                        {{-- Password Group --}}
                        <div id="password-group" class="border-b border-gray-200 py-2">
                            <label for="password" class="block text-xs font-medium text-gray-500"
                                x-text="modalMethod == 'PUT' ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password'">Password</label>
                            <input type="password" id="password" name="password"
                                class="mt-1 block w-full border-0 p-0 text-sm text-gray-900 focus:ring-0"
                                :required="modalMethod == 'POST'">
                        </div>

                        {{-- Konfirmasi Password Group --}}
                        <div id="password_confirmation-group" class="border-b border-gray-200 py-2">
                            <label for="password_confirmation"
                                class="block text-xs font-medium text-gray-500">Konfirmasi Password</label>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="mt-1 block w-full border-0 p-0 text-sm text-gray-900 focus:ring-0"
                                :required="modalMethod == 'POST'">
                        </div>

                        <h4 class="text-base font-semibold text-gray-700 pt-4">Data Profil</h4>

                        {{-- Nama --}}
                        <div class="border-b border-gray-200 py-2">
                            <label for="nama" class="block text-xs font-medium text-gray-500">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama"
                                class="mt-1 block w-full border-0 p-0 text-sm text-gray-900 focus:ring-0" required>
                        </div>

                        {{-- Jabatan --}}
                        <div class="border-b border-gray-200 py-2">
                            <label for="jabatan" class="block text-xs font-medium text-gray-500">Jabatan</label>
                            <input type="text" id="jabatan" name="jabatan"
                                class="mt-1 block w-full border-0 p-0 text-sm text-gray-900 focus:ring-0" required>
                        </div>

                        {{-- Alamat (Menggunakan textarea untuk alamat) --}}
                        <div class="border-b border-gray-200 py-2">
                            <label for="alamat" class="block text-xs font-medium text-gray-500">Alamat</label>
                            <textarea id="alamat" name="alamat"
                                class="mt-1 block w-full border-0 p-0 text-sm text-gray-900 focus:ring-0" rows="2"
                                required></textarea>
                        </div>

                        {{-- No HP --}}
                        <div class="py-2">
                            <label for="no_hp" class="block text-xs font-medium text-gray-500">No HP</label>
                            <input type="text" id="no_hp" name="no_hp"
                                class="mt-1 block w-full border-0 p-0 text-sm text-gray-900 focus:ring-0">
                        </div>

                    </div>


                    <div class="mt-4 flex justify-end space-x-3">
                        <button type="button" @click="isModalOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                            Tutup
                        </button>
                        {{-- Tombol Simpan hanya muncul saat Create/Edit --}}
                        <template x-if="!isDetailMode">
                            <button type="submit"
                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                                Simpan Data
                            </button>
                        </template>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection