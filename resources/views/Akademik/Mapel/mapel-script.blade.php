<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('alpine:init', () => {
Alpine.data('mapelCrud', () => ({

    // === STATE ===
    search: '',
    filterKelas: '',
    data: @json($guruMapels),
    form: { nama_mapel: '', kelas_id: '', tahun_ajaran: '', semester: 'Ganjil' },
    rencana: { jumlah_pertemuan: 0, jumlah_bab: 0, keterangan: '' },
    daftarSiswa: [],
    selectedSantri: [],
    currentMapelId: null,

    showModal: false,
    showRencanaModal: false,
    showSiswaModal: false,
    modalTitle: '',
    rencanaTitle: '',
    siswaModalTitle: '',
    editId: null,

    // === FILTER ===
    filteredData() {
        return this.data.filter(item => {
            const s = this.search.toLowerCase();
            const searchMatch = item.mapel.nama_mapel.toLowerCase().includes(s);
            const kelasMatch = this.filterKelas === '' || item.kelas_id == this.filterKelas;
            return searchMatch && kelasMatch;
        });
    },

    // === SWEETALERT LOADING ===
    showProgress(title = 'Memproses...', duration = 2500) {
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
                    progress = Math.min(progress + Math.random() * 12, 100);
                    progressBar.style.width = progress + '%';
                    progressText.textContent = Math.floor(progress) + '%';
                }, duration / 15);
            },
            willClose: () => clearInterval(timerInterval)
        });
    },

    // === UNIVERSAL FETCH (AMAN DARI HTML ERROR) ===
    async safeFetch(url, options = {}, loadingText = 'Memproses...', duration = 2000) {
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

    // === TAMBAH ===
    openCreate() {
        const y = new Date().getFullYear();
        this.modalTitle = 'Tambah Mapel';
        this.form = { nama_mapel: '', kelas_id: '', tahun_ajaran: `${y}/${y+1}`, semester: 'Ganjil' };
        this.editId = null;
        this.showModal = true;
    },

    // === EDIT ===
    openEdit(item) {
        this.modalTitle = 'Edit Mapel';
        this.editId = item.mapel.id;
        this.form = {
            nama_mapel: item.mapel.nama_mapel,
            kelas_id: item.kelas_id,
            tahun_ajaran: item.mapel.tahun_ajaran ?? '',
            semester: item.mapel.semester ?? 'Ganjil'
        };
        this.showModal = true;
    },

    // === SIMPAN MAPEL ===
    async saveMapel() {
        const url = this.editId 
            ? `/akademik/mapel/${this.editId}`
            : `/akademik/mapel`;
        const method = this.editId ? 'PUT' : 'POST';

        try {
            const json = await this.safeFetch(url, {
                method,
                headers: { 
                    'Content-Type': 'application/json; charset=UTF-8',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(this.form)
            }, this.editId ? 'Menyimpan perubahan mapel...' : 'Menambahkan mapel...', 2500);

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: json.message || 'Mapel berhasil disimpan.',
                timer: 1500,
                showConfirmButton: false
            });
            this.showModal = false;
            setTimeout(() => location.reload(), 1500);
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    },

    // === HAPUS MAPEL ===
    async deleteMapel(id) {
        const confirm = await Swal.fire({
            title: 'Yakin hapus?',
            text: 'Data mapel ini akan dihapus permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        });

        if (!confirm.isConfirmed) return;

        try {
            const json = await this.safeFetch(`/akademik/mapel/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            }, 'Menghapus mapel...', 1800);

            Swal.fire({
                icon: 'success',
                title: 'Terhapus!',
                text: json.message || 'Mapel berhasil dihapus.',
                timer: 1200,
                showConfirmButton: false
            });
            this.data = this.data.filter(m => m.mapel.id !== id);
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    },

    // === RENCANA ===
    openRencana(item) {
        this.rencanaTitle = item.mapel.nama_mapel;
        this.currentMapelId = item.id;
        this.rencana = {
            jumlah_pertemuan: item.rencana_pembelajaran?.jumlah_pertemuan ?? 0,
            jumlah_bab: item.rencana_pembelajaran?.jumlah_bab ?? 0,
            keterangan: item.rencana_pembelajaran?.keterangan ?? ''
        };
        this.showRencanaModal = true;
    },

    async saveRencana() {
        try {
            const json = await this.safeFetch(`/akademik/mapel/rencana/${this.currentMapelId}`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json; charset=UTF-8',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify(this.rencana)
            }, 'Menyimpan rencana pembelajaran...', 2500);

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: json.message || 'Rencana pembelajaran disimpan.',
                timer: 1500,
                showConfirmButton: false
            });
            this.showRencanaModal = false;
            setTimeout(() => location.reload(), 1500);
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    },

    // === SISWA ===
    async openSiswa(item) {
        this.siswaModalTitle = item.mapel.nama_mapel;
        this.currentMapelId = item.id;
        this.daftarSiswa = [];
        this.selectedSantri = [];
        this.showSiswaModal = true;

        try {
            const json = await this.safeFetch(`/akademik/mapel/${item.id}/siswa`, {}, 'Memuat daftar siswa...', 2500);
            this.daftarSiswa = json.santri || [];
            this.selectedSantri = json.terpilih || [];
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
            this.showSiswaModal = false;
        }
    },

    async saveSiswa() {
        try {
            const json = await this.safeFetch(`/akademik/mapel/${this.currentMapelId}/siswa`, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json; charset=UTF-8',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                },
                body: JSON.stringify({ santri: this.selectedSantri })
            }, 'Menyimpan daftar siswa...', 2500);

            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: json.message || 'Daftar siswa diperbarui.',
                timer: 1500,
                showConfirmButton: false
            });
            this.showSiswaModal = false;
            setTimeout(() => location.reload(), 1500);
        } catch (err) {
            Swal.fire('Error', err.message, 'error');
        }
    },

    // === TOGGLE CENTANG ===
    toggleAll(e) {
        if (e.target.checked) {
            this.selectedSantri = this.daftarSiswa.map(s => s.id);
        } else {
            this.selectedSantri = [];
        }
    },

    // === CLOSE ===
    closeModal() { this.showModal = false },
    closeRencanaModal() { this.showRencanaModal = false },
    closeSiswaModal() { this.showSiswaModal = false }

}));
});
</script>
