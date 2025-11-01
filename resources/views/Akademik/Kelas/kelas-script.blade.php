<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('alpine:init', () => {
Alpine.data('kelasCrud', () => ({
    // === STATE ===
    kelasList: @json($kelas),
    form: { nama_kelas: '', wali_kelas_id: '', is_locked: false },
    editId: null,
    showModal: false,
    modalTitle: '',
    showSantriModal: false,
    santriModalTitle: '',
    daftarSantri: [],
    selectedSantri: [],
    searchSantri: '',
    currentKelasId: null,
    showDetailModal: false,
    detailModalTitle: '',
    detailSantri: [],

    // === Modal Control ===
    openCreate() {
        this.modalTitle = 'Tambah Kelas';
        this.form = { nama_kelas: '', wali_kelas_id: '', is_locked: false };
        this.editId = null;
        this.showModal = true;
    },

    openEdit(item) {
        this.modalTitle = 'Edit Kelas';
        this.editId = item.id;
        this.form = {
            nama_kelas: item.nama_kelas,
            wali_kelas_id: item.wali_kelas_id ?? null, 
            is_locked: item.is_locked || false
        };
        this.showModal = true;
    },
    closeModal() {
        this.showModal = false;
    },

    // === SweetAlert Progress Loader ===
    showProgress(title = 'Memproses...', duration = 3000) {
        let timerInterval;
        Swal.fire({
            title,
            html: `
                <div style="width:100%;background:#eee;border-radius:6px;overflow:hidden;">
                    <div id="progress-bar" style="height:8px;width:0%;background:#6C63FF;transition:width 0.2s;"></div>
                </div>
                <div id="progress-text" style="margin-top:8px;font-size:12px;color:#666;">0%</div>
            `,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                const progressBar = Swal.getHtmlContainer().querySelector('#progress-bar');
                const progressText = Swal.getHtmlContainer().querySelector('#progress-text');
                let progress = 0;
                timerInterval = setInterval(() => {
                    progress = Math.min(progress + Math.random() * 10, 100);
                    progressBar.style.width = progress + '%';
                    progressText.textContent = Math.floor(progress) + '%';
                }, duration / 20);
            },
            willClose: () => clearInterval(timerInterval)
        });
    },

    // === Safe Fetch with progress + JSON parse ===
    async safeFetch(url, options = {}, loadingText = 'Memproses data...', duration = 2500) {
        this.showProgress(loadingText, duration);
        try {
            const res = await fetch(url, options);
            const text = await res.text();
            Swal.close();

            let data;
            try {
                data = JSON.parse(text);
            } catch {
                if (text.startsWith('<')) {
                    throw new Error('Server mengembalikan HTML (kemungkinan error Laravel atau redirect login)');
                } else {
                    throw new Error(text);
                }
            }

            if (!res.ok) throw new Error(data.message || 'Gagal memproses data');
            return data;
        } catch (err) {
            Swal.close();
            throw err;
        }
    },

    // === Save Kelas ===
    async saveKelas() {
        const url = this.editId 
            ? `/akademik/kelas/${this.editId}` 
            : `/akademik/kelas`;
        const method = this.editId ? 'PUT' : 'POST';

        try {
            const json = await this.safeFetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.form)
            }, this.editId ? 'Menyimpan perubahan kelas...' : 'Menambahkan kelas baru...', 2000);

            Swal.fire({
                icon: 'success',
                title: json.message || 'Berhasil!',
                timer: 1500,
                showConfirmButton: false
            });
            this.showModal = false;
            setTimeout(() => location.reload(), 1500);
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    },

    // === Delete Kelas ===
    async deleteKelas(id) {
        Swal.fire({
            title: 'Yakin hapus kelas ini?',
            text: 'Data tidak bisa dikembalikan!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6'
        }).then(async (result) => {
            if (result.isConfirmed) {
                try {
                    const json = await this.safeFetch(`/akademik/kelas/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    }, 'Menghapus kelas...', 2000);

                    Swal.fire('Terhapus!', json.message || 'Data berhasil dihapus.', 'success');
                    this.kelasList = this.kelasList.filter(k => k.id !== id);
                } catch (err) {
                    Swal.fire('Error', err.message, 'error');
                }
            }
        });
    },

    // === Kelola Santri ===
    async openSantri(item) {
        this.santriModalTitle = item.nama_kelas;
        this.currentKelasId = item.id;
        this.showSantriModal = true;
        this.daftarSantri = [];
        this.selectedSantri = [];

        try {
            const json = await this.safeFetch(`/akademik/kelas/${item.id}/siswa`, {}, 'Memuat daftar santri...', 3000);
            this.daftarSantri = json.santri || [];
            this.selectedSantri = json.terpilih || [];
        } catch (e) {
            Swal.fire('Gagal', e.message, 'error');
            this.showSantriModal = false;
        }
    },

    // === Detail Santri ===
    async openDetail(item) {
        this.detailModalTitle = item.nama_kelas;
        this.showDetailModal = true;
        this.detailSantri = [];

        try {
            const json = await this.safeFetch(`/akademik/kelas/${item.id}/siswa`, {}, 'Memuat detail santri...', 2500);
            this.detailSantri = (json.terpilih_data ?? []).length 
                ? json.terpilih_data 
                : json.santri.filter(s => json.terpilih?.includes(s.id));
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
            this.showDetailModal = false;
        }
    },
    closeDetailModal() {
        this.showDetailModal = false;
    },

    // === Pencarian Santri ===
    filteredSantri() {
        if (!this.searchSantri) return this.daftarSantri;
        const keyword = this.searchSantri.toLowerCase();
        return this.daftarSantri.filter(s => s.nama.toLowerCase().includes(keyword));
    },

    // === Centang Semua ===
    toggleAll(e) {
        if (e.target.checked) {
            this.selectedSantri = this.daftarSantri.map(s => s.id);
        } else {
            this.selectedSantri = [];
        }
    },

    // === Simpan Santri ===
    async saveSantri() {
        try {
            const json = await this.safeFetch(`/akademik/kelas/${this.currentKelasId}/siswa`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ santri: this.selectedSantri })
            }, 'Menyimpan daftar santri ke kelas...', 3000);

            Swal.fire({
                icon: 'success',
                title: json.message || 'Daftar santri berhasil diperbarui',
                timer: 1500,
                showConfirmButton: false
            });
            this.showSantriModal = false;
            setTimeout(() => location.reload(), 1500);
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    },

    closeSantriModal() {
        this.showSantriModal = false;
    }
}));
});
</script>
