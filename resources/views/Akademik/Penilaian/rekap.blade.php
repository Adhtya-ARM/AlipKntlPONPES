@extends('layouts.app')

@section('title', 'Rekap Penilaian Siswa')

@section('content')
<div x-data="rekapPenilaianHandler()" x-init="init()" class="bg-gray-50 min-h-screen p-6">
    
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Rekap Penilaian Siswa</h2>
        <p class="text-sm text-gray-500 mt-1">Rekap nilai siswa per bulan</p>
    </div>

    {{-- Filter Section --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Filter Mapel --}}
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Mata Pelajaran</label>
                <select x-model="selectedMapelId" @change="loadData()" class="w-full border-gray-300 rounded-md text-sm">
                    <option value="">-- Pilih Mata Pelajaran --</option>
                    @foreach($guruMapels as $gm)
                        <option value="{{ $gm->id }}">
                            {{ $gm->mapel->nama_mapel }} - {{ $gm->kelas->level }} {{ $gm->kelas->nama_unik }}
                        </option>
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
                <input type="text" x-model="searchQuery" @input="filterSiswa()" placeholder="Ketik nama (min 3 huruf)..." class="w-full border-gray-300 rounded-md text-sm">
            </div>
        </div>
    </div>

    {{-- Legend --}}
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-2 mb-4">
        <div class="flex flex-wrap gap-4 text-xs font-medium">
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span class="text-green-700 font-bold">≥ 75</span>
                <span class="text-gray-600">Tuntas</span>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="w-3 h-3 rounded-full bg-red-500"></span>
                <span class="text-red-700 font-bold">< 75</span>
                <span class="text-gray-600">Belum Tuntas</span>
            </div>
        </div>
    </div>

    {{-- Main Content --}}
    <div x-show="selectedMapelId" class="space-y-6">
        
        {{-- Month Header --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <h3 class="text-lg font-semibold text-gray-800" x-text="`${selectedMonthName} ${selectedYear}`"></h3>
            <p class="text-xs text-gray-500" x-text="`Rekap Nilai Bulan ${selectedMonthName}`"></p>
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
                        <div class="text-xs text-gray-500">Rata-rata:</div>
                        <div class="text-lg font-bold" :class="student.average >= 75 ? 'text-green-600' : 'text-red-600'" x-text="student.average"></div>
                    </div>
                </div>

                {{-- Grades Grid --}}
                <div class="overflow-x-auto">
                    <div class="min-w-max">
                        {{-- Date Headers --}}
                        <div class="flex gap-1 mb-1">
                            <div class="w-8 text-xs font-medium text-gray-500 text-center">No</div>
                            <template x-for="day in daysInMonth" :key="'h-' + day">
                                <div class="w-8 text-xs font-medium text-gray-500 text-center" x-text="day"></div>
                            </template>
                            <div class="w-16 text-xs font-medium text-gray-500 text-center ml-2">Rata²</div>
                        </div>

                        {{-- Student Row --}}
                        <div class="flex gap-1">
                            {{-- Number --}}
                            <div class="w-8 flex items-center justify-center">
                                <div class="text-xs font-medium text-gray-700" x-text="index + 1"></div>
                            </div>

                            {{-- Grade Cells (1-31) --}}
                            <template x-for="day in daysInMonth" :key="'grd-' + student.id + '-' + day">
                                <div class="w-8 h-8 flex items-center justify-center text-xs font-bold rounded border"
                                     :class="getGradeClass(student.grades[day]?.nilai)"
                                     x-text="student.grades[day]?.nilai || '-'"
                                     :title="student.grades[day] ? `${day} ${selectedMonthName}: ${student.grades[day].jenis} (${student.grades[day].nilai})` : ''">
                                </div>
                            </template>

                            {{-- Average Column --}} 
                            <div class="w-16 ml-2 flex items-center justify-center">
                                <div class="text-xs px-2 py-1 rounded font-bold" 
                                     :class="student.average >= 75 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'" 
                                     x-text="student.average"></div>
                            </div>
                        </div>

                        {{-- Progress Bar (Visualizing Average) --}}
                        <div class="mt-2 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full transition-all" 
                                 :class="student.average >= 75 ? 'bg-green-500' : 'bg-red-500'"
                                 :style="`width: ${student.average}%`"></div>
                        </div>

                        {{-- Summary --}}
                        <div class="mt-2 flex gap-3 text-xs text-gray-600">
                            <span>Total Penilaian: <strong class="text-gray-800" x-text="student.count || 0"></strong></span>
                            <span>Tertinggi: <strong class="text-green-600" x-text="student.max || '-'"></strong></span>
                            <span>Terendah: <strong class="text-red-600" x-text="student.min || '-'"></strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        {{-- Empty State --}}
        <div x-show="filteredStudents.length === 0" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center text-gray-500">
            <div class="text-5xl mb-3">📝</div>
            <div x-show="!selectedMapelId" class="text-lg font-medium">Silakan pilih mata pelajaran terlebih dahulu</div>
            <div x-show="selectedMapelId && allStudents.length === 0" class="text-lg font-medium">Tidak ada data siswa di kelas ini</div>
            <div x-show="selectedMapelId && allStudents.length > 0 && filteredStudents.length === 0">
                <div class="text-lg font-medium mb-2">Tidak ada siswa yang cocok dengan pencarian</div>
                <button @click="searchQuery = ''; filterSiswa();" class="text-blue-600 hover:underline text-sm">Reset Pencarian</button>
            </div>
        </div>
    </div>

    {{-- Not Selected State --}}
    <div x-show="!selectedMapelId" class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center text-gray-500">
        <div class="text-5xl mb-3">📊</div>
        <div class="text-lg font-medium">Silakan pilih mata pelajaran untuk melihat rekap penilaian</div>
    </div>
</div>

<script>
    function rekapPenilaianHandler() {
        return {
            selectedMapelId: '{{ request('guru_mapel_id') ?? '' }}',
            selectedMonth: '{{ request('bulan') ?? date('Y-m') }}',
            searchQuery: '',
            allStudents: [],
            filteredStudents: [],
            daysInMonth: 31,
            selectedMonthName: '',
            selectedYear: '',
            guruMapels: @json($guruMapels),

            init() {
                this.updateMonthInfo();
                if (this.selectedMapelId) {
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
                if (!this.selectedMapelId) return;

                this.updateMonthInfo();

                try {
                    const response = await axios.get(`{{ route('akademik.rekap-penilaian.getData') }}`, {
                        params: {
                            guru_mapel_id: this.selectedMapelId,
                            bulan: this.selectedMonth
                        }
                    });

                    this.allStudents = response.data.students || [];
                    this.filteredStudents = this.allStudents;
                } catch (error) {
                    console.error('Error loading data:', error);
                    let msg = 'Gagal memuat data penilaian';
                    if (error.response && error.response.data && error.response.data.message) {
                        msg += ': ' + error.response.data.message;
                    }
                    Swal.fire('Error', msg, 'error');
                }
            },

            filterSiswa() {
                if (!this.searchQuery || this.searchQuery.length < 3) {
                    this.filteredStudents = this.allStudents;
                    return;
                }

                const query = this.searchQuery.toLowerCase();
                this.filteredStudents = this.allStudents.filter(s => 
                    s.nama.toLowerCase().includes(query)
                );
            },

            getGradeClass(nilai) {
                if (nilai === undefined || nilai === null) return 'bg-gray-50 border-gray-100 text-gray-300';
                if (nilai >= 75) return 'bg-green-100 border-green-400 text-green-700';
                return 'bg-red-100 border-red-400 text-red-700';
            }
        }
    }
</script>
@endsection
