<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('rencanaApp', (initialData = {}) => ({
            // State
            today: new Date(),
            selectedYear: null,
            selectedMonth: null,
            years: [],
            months: ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'],
            calendarDays: [],
            
            // Form & Selection
            selectedDates: [],
            form: {
                id: null,
                from: null,
                to: null,
                jenis: 'kbm', // Default lowercase match DB
                judul: '',
                catatan: ''
            },
            
            // Data from Server
            entries: [],
            entriesByDate: {},

            // Configuration
            endpointUrl: '/akademik/rencana-pembelajaran',

            init() {
                console.log('RencanaApp Initializing...', initialData);

                // 1. Initialize Data from Server
                if (initialData.entries) {
                    this.entries = initialData.entries;
                }
                if (initialData.entriesByDate) {
                    this.entriesByDate = initialData.entriesByDate;
                }

                // 2. Initialize Date (Year/Month)
                // Check if 'month' param was passed in initialData (custom logic if you added it to controller)
                // Otherwise default to Today
                this.selectedYear = this.today.getFullYear();
                this.selectedMonth = this.today.getMonth();

                // 3. Initialize Year Dropdown (Current Year +/- 5)
                const currentYear = this.today.getFullYear();
                this.years = [];
                for (let y = currentYear - 3; y <= currentYear + 3; y++) {
                    this.years.push(y);
                }

                // 4. Build Calendar Grid
                this.buildCalendar();
                
                // 5. Rebuild Index (just to be safe)
                this.rebuildIndex();
            },

            // --- Calendar Logic ---
            buildCalendar() {
                const year = parseInt(this.selectedYear);
                const month = parseInt(this.selectedMonth);
                
                const firstDayOfMonth = new Date(year, month, 1);
                const daysInMonth = new Date(year, month + 1, 0).getDate();
                
                const startDayIndex = firstDayOfMonth.getDay(); // 0 (Sun) - 6 (Sat)
                // Adjust for Monday start (Senin=0, ..., Minggu=6)
                // If Sunday (0) -> 6. If Monday (1) -> 0.
                let offset = startDayIndex === 0 ? 6 : startDayIndex - 1;

                const cells = [];

                // Previous Month Padding
                const prevMonthLastDate = new Date(year, month, 0).getDate();
                for (let i = 0; i < offset; i++) {
                    const day = prevMonthLastDate - offset + 1 + i;
                    const date = new Date(year, month - 1, day);
                    cells.push(this.createDayObject(date, false));
                }

                // Current Month Days
                for (let i = 1; i <= daysInMonth; i++) {
                    const date = new Date(year, month, i);
                    cells.push(this.createDayObject(date, true));
                }

                // Next Month Padding (to fill 42 cells grid 6x7)
                const remainingCells = 42 - cells.length;
                for (let i = 1; i <= remainingCells; i++) {
                    const date = new Date(year, month + 1, i);
                    cells.push(this.createDayObject(date, false));
                }

                this.calendarDays = cells;
                console.log('Calendar Built:', this.calendarDays.length, 'days');
            },

            createDayObject(dateObj, isCurrentMonth) {
                return {
                    date: this.fmt(dateObj), // YYYY-MM-DD
                    day: dateObj.getDate(),
                    dayIndex: dateObj.getDay(), // 0-6
                    isCurrentMonth: isCurrentMonth,
                    isToday: this.fmt(dateObj) === this.fmt(this.today)
                };
            },

            // --- Navigation ---
            prevMonth() {
                if (this.selectedMonth === 0) {
                    this.selectedMonth = 11;
                    this.selectedYear--;
                } else {
                    this.selectedMonth--;
                }
                this.buildCalendar();
            },

            nextMonth() {
                if (this.selectedMonth === 11) {
                    this.selectedMonth = 0;
                    this.selectedYear++;
                } else {
                    this.selectedMonth++;
                }
                this.buildCalendar();
            },

            goTo(year, month) {
                this.selectedYear = parseInt(year);
                this.selectedMonth = parseInt(month);
                this.buildCalendar();
            },

            monthLabel() {
                if (this.selectedMonth === null) return '';
                return `${this.months[this.selectedMonth]} ${this.selectedYear}`;
            },

            // --- Helpers ---
            fmt(d) {
                const D = new Date(d);
                const year = D.getFullYear();
                const month = String(D.getMonth() + 1).padStart(2, '0');
                const day = String(D.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            },

            formatDate(isoDate) {
                if (!isoDate) return '';
                const date = new Date(isoDate + 'T00:00:00'); // Fix timezone
                return date.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
            },

            isWeekend(dayIndex) {
                return dayIndex === 0 || dayIndex === 6; // Minggu (0) or Sabtu (6)
            },

            // --- Selection & Form ---
            isDateSelected(date) {
                return this.selectedDates.includes(date);
            },

            toggleSelect(date) {
                if (this.selectedDates.includes(date)) {
                    this.selectedDates = this.selectedDates.filter(d => d !== date);
                } else {
                    this.selectedDates.push(date);
                }
                this.selectedDates.sort();
                this.updateFormDates();
            },

            updateFormDates() {
                if (this.selectedDates.length > 0) {
                    this.form.from = this.selectedDates[0];
                    this.form.to = this.selectedDates[this.selectedDates.length - 1];

                    // Check if the FIRST selected date has an entry
                    const firstDate = this.selectedDates[0];
                    const existingEntries = this.getEntries(firstDate);

                    if (existingEntries.length > 0) {
                        // Edit Mode: Populate form with the first entry found
                        const entry = existingEntries[0];
                        this.form.id = entry.id;
                        this.form.jenis = entry.jenis; // Ensure this matches select values (lowercase)
                        this.form.judul = entry.judul;
                        this.form.catatan = entry.catatan;
                    } else {
                        // Add Mode: Reset form (except dates)
                        this.form.id = null;
                        this.form.jenis = 'kbm';
                        this.form.judul = '';
                        this.form.catatan = '';
                    }
                } else {
                    this.form.from = null;
                    this.form.to = null;
                    this.form.id = null;
                    this.form.jenis = 'kbm';
                    this.form.judul = '';
                    this.form.catatan = '';
                }
            },

            clearSelection() {
                this.selectedDates = [];
                this.form = {
                    id: null,
                    from: null,
                    to: null,
                    jenis: 'kbm',
                    judul: '',
                    catatan: ''
                };
            },

            selectedInfo() {
                if (this.selectedDates.length === 0) return 'Pilih tanggal pada kalender.';
                return `${this.selectedDates.length} tanggal dipilih.`;
            },

            // --- Entries Management ---
            rebuildIndex() {
                this.entriesByDate = {};
                this.entries.forEach(entry => {
                    const start = new Date(entry.from + 'T00:00:00');
                    const end = new Date(entry.to + 'T00:00:00');
                    
                    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                        const key = this.fmt(d);
                        if (!this.entriesByDate[key]) {
                            this.entriesByDate[key] = [];
                        }
                        // Avoid duplicates if rebuilding multiple times
                        if (!this.entriesByDate[key].find(e => e.id === entry.id)) {
                            this.entriesByDate[key].push(entry);
                        }
                    }
                });
            },

            hasEntry(date) {
                return this.entriesByDate[date] && this.entriesByDate[date].length > 0;
            },

            getEntries(date) {
                return this.entriesByDate[date] || [];
            },

            editEntry(id) {
                const entry = this.entries.find(e => e.id === id);
                if (entry) {
                    this.form = { ...entry }; // Copy data
                    // Select dates on calendar
                    this.selectedDates = [];
                    const start = new Date(entry.from + 'T00:00:00');
                    const end = new Date(entry.to + 'T00:00:00');
                    for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
                        this.selectedDates.push(this.fmt(d));
                    }
                    this.showToast('info', 'Edit Mode', 'Silakan ubah data entri ini.');
                }
            },

            // --- API Actions ---
            getCsrfToken() {
                return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            },

            async saveRange() {
                // 1. Determine Ranges
                let ranges = [];

                if (this.selectedDates.length > 0) {
                    // Sort dates to ensure correct order
                    const sortedDates = [...this.selectedDates].sort();
                    
                    // Group into continuous ranges
                    let currentRange = { start: sortedDates[0], end: sortedDates[0] };
                    
                    for (let i = 1; i < sortedDates.length; i++) {
                        const date = new Date(sortedDates[i]);
                        const prevDate = new Date(sortedDates[i-1]);
                        
                        // Calculate difference in days
                        const diffTime = Math.abs(date - prevDate);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                        
                        if (diffDays === 1) {
                            // Continuous (next day)
                            currentRange.end = sortedDates[i];
                        } else {
                            // Gap found, push current range and start new
                            ranges.push(currentRange);
                            currentRange = { start: sortedDates[i], end: sortedDates[i] };
                        }
                    }
                    ranges.push(currentRange);
                } else {
                    // Use form input if no dates selected on calendar
                    if (!this.form.from || !this.form.to) {
                        return this.showToast('error', 'Validasi', 'Tanggal Dari dan Sampai harus diisi.');
                    }
                    ranges.push({ start: this.form.from, end: this.form.to });
                }

                // 2. Validation for Update Mode
                if (this.form.id && ranges.length > 1) {
                    return this.showToast('error', 'Validasi', 'Edit data hanya bisa dilakukan untuk satu rentang tanggal yang bersambung.');
                }

                this.showToast('info', 'Menyimpan...', 'Sedang memproses data...');

                // 3. Send Requests
                try {
                    const promises = ranges.map(range => {
                        const payload = {
                            ...this.form,
                            from: range.start,
                            to: range.end,
                            _method: this.form.id ? 'PUT' : 'POST'
                        };
                        
                        // If updating, use the ID in the URL
                        const url = this.form.id ? `${this.endpointUrl}/${this.form.id}` : this.endpointUrl;

                        return fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': this.getCsrfToken(),
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                    });

                    const responses = await Promise.all(promises);
                    
                    // Check if all requests were successful
                    const allOk = responses.every(r => r.ok);

                    if (allOk) {
                        // Refresh Data
                        // For simplicity, we'll just fetch all data again or reload, 
                        // but here we try to update locally if possible.
                        // Since we might have created multiple entries, it's safer to just reload the page 
                        // or fetch the updated list if we had an API for that.
                        // But our current init() uses initialData from Blade.
                        // Let's try to manually update entries if it was a single update.
                        
                        if (this.form.id && responses.length === 1) {
                            const result = await responses[0].json();
                            this.entries = this.entries.map(e => e.id === result.entry.id ? result.entry : e);
                        } else {
                            // For multiple creates, we need to get the new IDs. 
                            // Reading streams of multiple responses is async.
                            // Let's just reload the page to be safe and simple for now, 
                            // OR we can just add them if we parse all JSONs.
                            
                            const newEntries = await Promise.all(responses.map(r => r.json()));
                            newEntries.forEach(res => {
                                if (res.entry) this.entries.push(res.entry);
                            });
                        }
                        
                        this.rebuildIndex();
                        this.clearSelection();
                        this.showToast('success', 'Berhasil', 'Data berhasil disimpan.');
                    } else {
                        this.showToast('error', 'Gagal', 'Sebagian atau semua data gagal disimpan.');
                    }
                } catch (error) {
                    console.error(error);
                    this.showToast('error', 'Error', 'Gagal menghubungi server.');
                }
            },

            async deleteEntry(id) {
                if (!confirm('Hapus entri ini?')) return;

                try {
                    const response = await fetch(`${this.endpointUrl}/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ _method: 'DELETE' })
                    });

                    if (response.ok) {
                        this.entries = this.entries.filter(e => e.id !== id);
                        this.rebuildIndex();
                        this.showToast('success', 'Terhapus', 'Entri berhasil dihapus.');
                    }
                } catch (error) {
                    console.error(error);
                }
            },

            async removeBySelection() {
                if (this.selectedDates.length === 0) return;
                if (!confirm('Hapus semua entri pada tanggal terpilih?')) return;

                // Find IDs to delete
                const idsToDelete = new Set();
                this.selectedDates.forEach(date => {
                    const entries = this.getEntries(date);
                    entries.forEach(e => idsToDelete.add(e.id));
                });

                if (idsToDelete.size === 0) {
                    return this.showToast('info', 'Info', 'Tidak ada entri pada tanggal yang dipilih.');
                }

                try {
                    const response = await fetch(`${this.endpointUrl}/mass-destroy`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.getCsrfToken(),
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: Array.from(idsToDelete) })
                    });

                    if (response.ok) {
                        this.entries = this.entries.filter(e => !idsToDelete.has(e.id));
                        this.rebuildIndex();
                        this.clearSelection();
                        this.showToast('success', 'Berhasil', 'Entri terpilih berhasil dihapus.');
                    }
                } catch (error) {
                    console.error(error);
                }
            },

            // --- Toast Notification (SweetAlert2 wrapper) ---
            showToast(icon, title, text) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: icon,
                        title: title,
                        text: text,
                        showConfirmButton: false,
                        timer: 3000
                    });
                } else {
                    alert(`${title}: ${text}`);
                }
            }
        }));
    });
</script>
