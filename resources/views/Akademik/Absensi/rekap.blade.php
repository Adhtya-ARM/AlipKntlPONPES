@extends('layouts.app')

@section('title', 'Daftar Kehadiran Siswa')

@section('content')
<div x-data="rekapKehadiranHandler()" x-init="init()" class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Daftar Kehadiran Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Rekap kehadiran siswa per bulan</p>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Filter Kelas --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Kelas</label>
                <select x-model="selectedKelasId" @change="loadData()" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->level }} {{ $kelas->nama_unik }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Filter Bulan --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Bulan</label>
                <input type="month" x-model="selectedMonth" @change="loadData()" class="w-full border-gray-300 rounded-md text-sm">
            </div>

            {{-- Cari Siswa --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Cari Siswa</label>
                <input type="text" x-model="searchQuery" @input="filterSiswa()" placeholder="Ketik nama (min 5 huruf)..." class="w-full border-gray-300 rounded-md text-sm">
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-2 mb-4">
        <div class="flex flex-wrap gap-4 text-xs font-medium">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-green-700 font-bold">H</span>
                <span class="text-gray-600">Hadir</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                <span class="text-blue-700 font-bold">S</span>
                <span class="text-gray-600">Sakit</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                <span class="text-yellow-700 font-bold">I</span>
                <span class="text-gray-600">Izin</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-red-700 font-bold">A</span>
                <span class="text-gray-600">Alpha</span>
            </div>
        </div>
    </div>

    {{-- Main Content - Show ALL Students by Default --}}
    <div x-show="selectedKelasId" class="space-y-6">
        
        {{-- Month Header --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-lg font-semibold text-gray-800" x-text="`${selectedMonthName} ${selectedYear}`"></h3>
            <p class="text-xs text-gray-500" x-text="`November ${selectedYear}`"></p>
        </div>

        {{-- Student List --}}
        <template x-for="(student, index) in filteredStudents" :key="student.id">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                {{-- Student Header --}}
                <div class="flex justify-between items-center mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-sm" x-text="index + 1"></div>
                        <div>
                            <div class="font-medium text-gray-900" x-text="student.nama"></div>
                            <div class="text-xs text-gray-400" x-text="student.nisn || 'NISN: -'"></div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-gray-500">Persentase:</div>
                        <div class="text-lg font-bold" :class="student.persentase >= 75 ? 'text-green-600' : 'text-red-600'" x-text="student.persentase + '%'"></div>
                    </div>
                </div>

                {{-- Attendance Grid --}}
                <div class="overflow-x-auto">
                    <div class="min-w-max">
                        {{-- Date Headers --}}
                        <div class="flex gap-1 mb-1">
                            <div class="w-8 text-xs font-medium text-gray-500 text-center">No</div>
                            <template x-for="day in daysInMonth" :key="'h-' + day">
                                <div class="w-8 text-xs font-medium text-gray-500 text-center" x-text="day"></div>
                            </template>
                            <div class="w-16 text-xs font-medium text-gray-500 text-center ml-2">Tampil</div>
                        </div>

                        {{-- Student Row --}}
                        <div class="flex gap-1">
                            {{-- Student Name in Row --}}
                            <div class="w-8 flex items-center justify-center">
                                <div class="text-xs font-medium text-gray-700" x-text="index + 1"></div>
                            </div>

                            {{-- Attendance Cells (1-31) --}}
                            <template x-for="day in daysInMonth" :key="'att-' + student.id + '-' + day">
                                <div class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded border"
                                     :class="getStatusClass(student.attendance[day])"
                                     x-text="student.attendance[day] || '-'"
                                     :title="`${day} ${selectedMonthName}: ${getStatusLabel(student.attendance[day])}`">
                                </div>
                            </template>

                            {{-- Tampil Column (Summary Indicator) --}} 
                            <div class="w-16 ml-2 flex items-center justify-center">
                                <div class="text-xs px-2 py-1 rounded" :class="student.persentase >= 75 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" x-text="student.persentase + '%'"></div>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full transition-all" 
                                 :class="student.persentase >= 75 ? 'bg-green-500' : 'bg-red-500'"
                                 :style="`width: ${student.persentase}%`"></div>
                        </div>

                        {{-- Summary --}}
                        <div class="mt-2 flex gap-3 text-xs text-gray-600">
                            <span>Hadir: <strong class="text-green-600" x-text="student.summary.H || 0"></strong></span>
                            <span>Sakit: <strong class="text-blue-600" x-text="student.summary.S || 0"></strong></span>
                            <span>Izin: <strong class="text-yellow-600" x-text="student.summary.I || 0"></strong></span>
                            <span>Alpha: <strong class="text-red-600" x-text="student.summary.A || 0"></strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty State --}}
        <div x-show="filteredStudents.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center text-gray-500">
            <div class="text-5xl mb-3">📚</div>
            <div x-show="!selectedKelasId" class="text-lg font-medium">Silakan pilih kelas terlebih dahulu</div>
            <div x-show="selectedKelasId && allStudents.length === 0" class="text-lg font-medium">Tidak ada data siswa di kelas ini</div>
            <div x-show="selectedKelasId && allStudents.length > 0 && filteredStudents.length === 0">
                <div class="text-lg font-medium mb-2">Tidak ada siswa yang cocok dengan pencarian</div>
                <button @click="searchQuery = ''; filterSiswa();" class="text-blue-600 hover:underline text-sm">Reset Pencarian</button>
            </div>
        </div>
    </div>

    {{-- Not Selected State --}}
    <div x-show="!selectedKelasId" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center text-gray-500">
        <div class="text-5xl mb-3">🎓</div>
        <div class="text-lg font-medium">Silakan pilih kelas untuk melihat rekap kehadiran</div>
    </div>
</div>

<script>
    function rekapKehadiranHandler() {
        return {
            selectedKelasId: '{{ request('kelas_id') ?? '' }}',
            selectedMonth: '{{ request('bulan') ?? date('Y-m') }}',
            searchQuery: '',
            allStudents: [],
            filteredStudents: [],
            daysInMonth: 31,
            selectedMonthName: '',
            selectedYear: '',

            init() {
                this.updateMonthInfo();
                if (this.selectedKelasId) {
                    this.loadData();
                }
            },

            updateMonthInfo() {
                const [year, month] = this.selectedMonth.split('-');
                const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                this.selectedMonthName = monthNames[parseInt(month) - 1];
                this.selectedYear = year;
                this.daysInMonth = new Date(year, month, 0).getDate();
            },

            async loadData() {
                if (!this.selectedKelasId) return;

                this.updateMonthInfo();

                try {
                    const response = await axios.get(`/akademik/rekap-kehadiran/data`, {
                        params: {
                            kelas_id: this.selectedKelasId,
                            bulan: this.selectedMonth
                        }
                    });

                    this.allStudents = response.data.students || [];
                    this.filteredStudents = this.allStudents; // Show ALL students by default
                } catch (error) {
                    console.error('Error loading data:', error);
                    Swal.fire('Error', 'Gagal memuat data kehadiran', 'error');
                }
            },

            filterSiswa() {
                if (!this.searchQuery || this.searchQuery.length < 5) {
                    // Show all students if search < 5 chars
                    this.filteredStudents = this.allStudents;
                    return;
                }

                const query = this.searchQuery.toLowerCase();
                this.filteredStudents = this.allStudents.filter(s => 
                    s.nama.toLowerCase().includes(query)
                );
            },

            getStatusClass(status) {
                const classes = {
                    'H': 'bg-green-100 border-green-400 text-green-700',
                    'S': 'bg-blue-100 border-blue-400 text-blue-700',
                    'I': 'bg-yellow-100 border-yellow-400 text-yellow-700',
                    'A': 'bg-red-100 border-red-400 text-red-700'
                };
                return classes[status] || 'bg-white border-gray-200 text-gray-300';
            },

            getStatusLabel(status) {
                const labels = {
                    'H': 'Hadir',
                    'S': 'Sakit',
                    'I': 'Izin',
                    'A': 'Alpha',
                    'Tk': 'Tidak Keterangan',
                    'D': 'Dispensasi',
                    'T': 'Terlambat',
                };
                return labels[status] || 'Tidak ada data';
            }
        }
    }
</script>
@endsection
