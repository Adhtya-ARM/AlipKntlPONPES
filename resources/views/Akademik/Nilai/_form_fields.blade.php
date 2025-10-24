{{--
    Partial: Modal Form Penilaian
    File ini mendefinisikan Alpine component untuk mengelola state modal (Tambah/Edit/Detail)
    dengan x-ref="penilaianModal" agar dapat diakses dari komponen induk.
    
    Variabel Blade: $allSantri, $allMapel (menggunakan 'nama_mapel'), $allTahunAjaran, $allSemester
--}}
<div x-data="{
    isModalOpen: false,
    modalTitle: '',
    modalActionUrl: '',
    modalMethod: '',
    isDetailMode: false,
    modalData: null,
    
    // Objek data form yang akan diikat ke input
    form: {
        santri_profile_id: '',
        mapel_id: '',
        tahun_ajaran: '',
        semester: '',
        nilai_tugas: '',
        nilai_uts: '',
        nilai_uas: ''
    },

    // --- Alpine Functions ---
    resetForm() {
        this.form = {
            santri_profile_id: '',
            mapel_id: '',
            tahun_ajaran: '',
            semester: '',
            nilai_tugas: '',
            nilai_uts: '',
            nilai_uas: ''
        };
        this.setFormMode(false);
        this.$nextTick(() => {
            const santriSelect = document.getElementById('santri_profile_id');
            if (santriSelect) santriSelect.value = '';
            const mapelSelect = document.getElementById('mapel_id');
            if (mapelSelect) mapelSelect.value = '';
        });
    },

    // CATATAN: Menggunakan 'nama_mapel' untuk tampilan dan logika form
    fillForm(nilai, isReadOnly) {
        this.form.santri_profile_id = nilai.santri_profile_id ?? '';
        this.form.mapel_id = nilai.mapel_id ?? '';
        this.form.tahun_ajaran = nilai.tahun_ajaran ?? '';
        this.form.semester = nilai.semester ?? '';
        this.form.nilai_tugas = nilai.nilai_tugas ?? '';
        this.form.nilai_uts = nilai.nilai_uts ?? '';
        this.form.nilai_uas = nilai.nilai_uas ?? '';
        
        this.setFormMode(isReadOnly);
    },

    setFormMode(isReadOnly) {
        this.$nextTick(() => {
            const formContainer = document.getElementById('penilaian-form-fields');
            if (!formContainer) return;

            const inputs = formContainer.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.readOnly = isReadOnly;

                if (input.tagName === 'SELECT' || input.name === 'santri_profile_id' || input.name === 'mapel_id' || input.tagName === 'TEXTAREA') {
                    input.disabled = isReadOnly;
                }
                
                if (isReadOnly) {
                    input.removeAttribute('required');
                } else if (input.name !== 'santri_profile_id' && input.name !== 'mapel_id') {
                    input.setAttribute('required', 'required');
                }
            });

            // Santri dan Mapel dinonaktifkan saat Edit (PUT)
            if (this.modalMethod === 'PUT') {
                const santriSelect = document.getElementById('santri_profile_id');
                if (santriSelect) santriSelect.disabled = true;
                const mapelSelect = document.getElementById('mapel_id');
                if (mapelSelect) mapelSelect.disabled = true;
            }
        });
    },

    openCreateModal() {
        this.modalTitle = 'Input Nilai Baru';
        this.modalActionUrl = '{{ route('penilaian.store') }}';
        this.modalMethod = 'POST';
        this.isDetailMode = false;
        this.modalData = null;
        this.resetForm();
        this.isModalOpen = true;
    },

    openEditModal(nilai) {
        // Menggunakan properti nama_mapel
        this.modalTitle = 'Edit Nilai: ' + (nilai.santri_nama || 'Santri') + ' (' + (nilai.nama_mapel || 'Mapel') + ')';
        this.modalActionUrl = '{{ url('penilaian') }}/' + nilai.id;
        this.modalMethod = 'PUT';
        this.isDetailMode = false;
        this.modalData = nilai;
        this.fillForm(nilai, false);
        this.isModalOpen = true;
    },

    openDetailModal(nilai) {
        // Menggunakan properti nama_mapel
        this.modalTitle = 'Detail Nilai: ' + (nilai.santri_nama || 'Santri') + ' (' + (nilai.nama_mapel || 'Mapel') + ')';
        this.modalActionUrl = '';
        this.modalMethod = '';
        this.isDetailMode = true;
        this.modalData = nilai;
        this.fillForm(nilai, true);
        this.isModalOpen = true;
    }
}" x-ref="penilaianModal">


    {{-- MODAL CONTAINER --}}
    <div x-show="isModalOpen"
        x-cloak
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">

        {{-- Background Overlay --}}
        <div class="fixed inset-0 bg-gray-900 bg-opacity-75 transition-opacity" @click="isModalOpen = false"></div>

        {{-- Modal Content Container --}}
        <div class="flex items-center justify-center min-h-screen p-4 sm:p-0">
            <div x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="bg-white rounded-xl shadow-2xl transform transition-all max-w-lg w-full"
                    @click.away="isModalOpen = false">

                <div class="p-6 border-b border-gray-200 bg-gray-50 rounded-t-xl">
                    <h3 class="text-xl font-bold text-gray-900" x-text="modalTitle"></h3>
                    <p class="text-sm text-gray-500 mt-1"
                        x-text="isDetailMode ? 'Detail skor nilai santri.' : (modalMethod == 'POST' ? 'Isi formulir untuk menambahkan nilai baru.' : 'Ubah data nilai ini.')">
                    </p>
                </div>

                {{-- Form: Digunakan untuk CREATE, EDIT, dan DISPLAY DETAIL --}}
                <form :action="modalActionUrl" method="POST" class="pt-4 pb-6 space-y-4" id="penilaian-form-fields" @submit.prevent="isDetailMode ? '' : $el.submit()">
                    @csrf

                    <template x-if="modalMethod == 'PUT'">
                        @method('PUT')
                    </template>

                    <div class="grid grid-cols-1 gap-6 px-6">

                        {{-- Dropdown Santri --}}
                        <div>
                            <label for="santri_profile_id" class="block text-sm font-medium text-gray-700">Santri</label>
                            <select id="santri_profile_id" name="santri_profile_id" required
                                    x-model="form.santri_profile_id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                                <option value="" disabled>Pilih Santri</option>
                                @foreach ($allSantri as $santri)
                                    <option value="{{ $santri->id }}">{{ $santri->nama }}</option>
                                @endforeach
                            </select>
                            {{-- Tampilkan nama santri saat mode Detail atau Edit (karena disabled) --}}
                            <template x-if="isDetailMode || (modalMethod === 'PUT' && modalData)">
                                <p class="mt-1 text-sm text-gray-900 font-semibold" x-text="modalData.santri_nama"></p>
                            </template>
                        </div>

                        {{-- Dropdown Mata Pelajaran --}}
                        <div>
                            <label for="mapel_id" class="block text-sm font-medium text-gray-700">Mata Pelajaran</label>
                            <select id="mapel_id" name="mapel_id" required
                                    x-model="form.mapel_id"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                                <option value="" disabled>Pilih Mata Pelajaran</option>
                                {{-- Menggunakan properti 'nama_mapel' --}}
                                @foreach ($allMapel as $mapel)
                                    <option value="{{ $mapel->id }}">{{ $mapel->nama_mapel }}</option>
                                @endforeach
                            </select>
                            {{-- Tampilkan nama mapel saat mode Detail atau Edit (karena disabled) --}}
                            <template x-if="isDetailMode || (modalMethod === 'PUT' && modalData)">
                                <p class="mt-1 text-sm text-gray-900 font-semibold" x-text="modalData.nama_mapel"></p>
                            </template>
                        </div>

                        {{-- Dropdown Tahun Ajaran --}}
                        <div>
                            <label for="tahun_ajaran" class="block text-sm font-medium text-gray-700">Tahun Ajaran</label>
                            <select id="tahun_ajaran" name="tahun_ajaran" required
                                    x-model="form.tahun_ajaran"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                                <option value="" disabled>Pilih Tahun Ajaran</option>
                                @foreach ($allTahunAjaran as $tahun)
                                    <option value="{{ $tahun }}">{{ $tahun }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Dropdown Semester --}}
                        <div>
                            <label for="semester" class="block text-sm font-medium text-gray-700">Semester</label>
                            <select id="semester" name="semester" required
                                    x-model="form.semester"
                                    class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md shadow-sm">
                                <option value="" disabled>Pilih Semester</option>
                                @foreach ($allSemester as $sem)
                                    <option value="{{ $sem }}">{{ $sem }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-3 gap-4">
                            {{-- Nilai Tugas --}}
                            <div>
                                <label for="nilai_tugas" class="block text-sm font-medium text-gray-700">Nilai Tugas</label>
                                <input type="number" id="nilai_tugas" name="nilai_tugas" required
                                        x-model.number="form.nilai_tugas"
                                        min="0" max="100"
                                        class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="0 - 100">
                            </div>

                            {{-- Nilai UTS --}}
                            <div>
                                <label for="nilai_uts" class="block text-sm font-medium text-gray-700">Nilai UTS</label>
                                <input type="number" id="nilai_uts" name="nilai_uts" required
                                        x-model.number="form.nilai_uts"
                                        min="0" max="100"
                                        class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="0 - 100">
                            </div>

                            {{-- Nilai UAS --}}
                            <div>
                                <label for="nilai_uas" class="block text-sm font-medium text-gray-700">Nilai UAS</label>
                                <input type="number" id="nilai_uas" name="nilai_uas" required
                                        x-model.number="form.nilai_uas"
                                        min="0" max="100"
                                        class="mt-1 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md p-2 focus:ring-indigo-500 focus:border-indigo-500"
                                        placeholder="0 - 100">
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 px-6 flex justify-end space-x-3">
                        <button type="button" @click="isModalOpen = false" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition duration-150 shadow-sm">
                            Tutup
                        </button>
                        {{-- Tombol Simpan hanya muncul saat Create/Edit --}}
                        <template x-if="!isDetailMode">
                            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition duration-150 shadow-md">
                                Simpan Nilai
                            </button>
                        </template>
                    </div>
                </form>
            </div>
        </div>
    </div>


</div>