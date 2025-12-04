@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-indigo-50 via-white to-purple-50 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        
        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-graduation-cap text-white text-2xl"></i>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Kenaikan Kelas</h1>
                        <p class="text-gray-600 mt-1">Pindahkan santri ke tahun ajaran berikutnya</p>
                    </div>
                </div>
                <a href="{{ route('manajemen-sekolah.tahun-ajaran.index') }}" class="inline-flex items-center px-4 py-2.5 bg-white border-2 border-gray-300 hover:border-gray-400 text-gray-700 font-medium rounded-lg transition-all duration-200 shadow-sm hover:shadow">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            @if($activeYear)
            {{-- Active Year Badge --}}
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-100 border-2 border-green-300 rounded-lg">
                <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                <span class="text-sm font-semibold text-green-800">
                    Tahun Aktif: {{ $activeYear->nama }} - Semester {{ ucfirst($activeYear->semester) }}
                </span>
            </div>
            @endif
        </div>

        @if($activeYear)
        {{-- Progress Steps --}}
        <div class="mb-8">
            <div class="flex items-center justify-between max-w-3xl mx-auto">
                <div class="flex flex-col items-center flex-1">
                    <div id="step1-circle" class="w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-lg transition-all duration-300">
                        1
                    </div>
                    <span id="step1-text" class="mt-2 text-sm font-semibold text-indigo-600 transition-all duration-300">Pilih Kelas</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-2 -mt-6">
                    <div id="progress-1-2" class="h-full bg-indigo-600 transition-all duration-500" style="width: 0%"></div>
                </div>
                <div class="flex flex-col items-center flex-1">
                    <div id="step2-circle" class="w-12 h-12 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center font-bold text-lg shadow-md transition-all duration-300">
                        2
                    </div>
                    <span id="step2-text" class="mt-2 text-sm font-medium text-gray-500 transition-all duration-300">Tahun Tujuan</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-2 -mt-6">
                    <div id="progress-2-3" class="h-full bg-indigo-600 transition-all duration-500" style="width: 0%"></div>
                </div>
                <div class="flex flex-col items-center flex-1">
                    <div id="step3-circle" class="w-12 h-12 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center font-bold text-lg shadow-md transition-all duration-300">
                        3
                    </div>
                    <span id="step3-text" class="mt-2 text-sm font-medium text-gray-500 transition-all duration-300">Pilih Santri</span>
                </div>
                <div class="flex-1 h-1 bg-gray-300 mx-2 -mt-6">
                    <div id="progress-3-4" class="h-full bg-indigo-600 transition-all duration-500" style="width: 0%"></div>
                </div>
                <div class="flex flex-col items-center flex-1">
                    <div id="step4-circle" class="w-12 h-12 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center font-bold text-lg shadow-md transition-all duration-300">
                        4
                    </div>
                    <span id="step4-text" class="mt-2 text-sm font-medium text-gray-500 transition-all duration-300">Konfirmasi</span>
                </div>
            </div>
        </div>

        {{-- Main Content Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            {{-- Step 1: Pilih Kelas --}}
            <div class="bg-white rounded-2xl shadow-lg border-2 border-indigo-100 overflow-hidden transition-all duration-300 hover:shadow-xl">
                <div class="bg-gradient-to-r from-indigo-600 to-indigo-700 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-school text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Step 1</h3>
                            <p class="text-indigo-100 text-sm">Pilih Kelas Sumber</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Kelas yang akan dipindahkan</label>
                    <select id="kelasSelect" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-100 transition-all duration-200 bg-white font-medium text-gray-900 shadow-sm">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" data-level="{{ $k->level }}">
                                {{ $k->nama }} (Tingkat {{ $k->level }})
                            </option>
                        @endforeach
                    </select>
                    <div id="kelasInfo" class="hidden mt-4 p-4 bg-indigo-50 border-l-4 border-indigo-500 rounded-lg">
                        <p class="text-sm text-indigo-800">
                            <i class="fas fa-info-circle mr-2"></i>Sedang memuat data santri...
                        </p>
                    </div>
                </div>
            </div>

            {{-- Step 2: Tahun Ajaran Tujuan --}}
            <div class="bg-white rounded-2xl shadow-lg border-2 border-purple-100 overflow-hidden transition-all duration-300 hover:shadow-xl">
                <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Step 2</h3>
                            <p class="text-purple-100 text-sm">Tahun Tujuan</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Pindah ke tahun ajaran</label>
                    <select id="targetYearSelect" class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:border-purple-500 focus:ring-4 focus:ring-purple-100 transition-all duration-200 bg-white font-medium text-gray-900 shadow-sm">
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        @foreach($allYears as $year)
                            @if($year->id !== $activeYear->id)
                                <option value="{{ $year->id }}" data-semester="{{ $year->semester }}">
                                    {{ $year->nama }} - {{ $year->semester }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                    <div id="yearInfo" class="hidden mt-4 p-4 bg-purple-50 border-l-4 border-purple-500 rounded-lg">
                        <p class="text-sm text-purple-800">
                            <i class="fas fa-check-circle mr-2"></i>Tahun tujuan dipilih
                        </p>
                    </div>
                </div>
            </div>

            {{-- Step 3: Summary Box --}}
            <div class="bg-white rounded-2xl shadow-lg border-2 border-amber-100 overflow-hidden transition-all duration-300 hover:shadow-xl">
                <div class="bg-gradient-to-r from-amber-500 to-amber-600 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-chart-bar text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Ringkasan</h3>
                            <p class="text-amber-100 text-sm">Status Proses</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Kelas Sumber</span>
                            <span id="summaryKelas" class="text-sm font-bold text-gray-900">-</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-600">Tahun Tujuan</span>
                            <span id="summaryYear" class="text-sm font-bold text-gray-900">-</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-indigo-50 rounded-lg border-2 border-indigo-200">
                            <span class="text-sm font-semibold text-indigo-900">Santri Terpilih</span>
                            <span id="summaryCount" class="text-lg font-bold text-indigo-600">0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Santri Selection Area --}}
        <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-gray-700 to-gray-800 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold text-lg">Daftar Santri</h3>
                            <p class="text-gray-300 text-sm">Pilih santri yang akan dinaikkan kelasnya</p>
                        </div>
                    </div>
                    <button id="selectAllBtn" onclick="toggleSelectAll()" class="hidden px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-lg transition-all duration-200">
                        <i class="fas fa-check-double mr-2"></i>Pilih Semua
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div id="santriContainer" class="max-h-96 overflow-y-auto">
                    <div class="text-center py-16">
                        <i class="fas fa-hand-pointer text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg font-medium">Pilih kelas terlebih dahulu</p>
                        <p class="text-gray-400 text-sm mt-2">Data santri akan muncul di sini</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Archive Options & Actions --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Archive Option Card --}}
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 rounded-2xl shadow-lg border-2 border-amber-200 p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg">
                        <i class="fas fa-archive text-white text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-lg font-bold text-amber-900 mb-2">Opsi Pengarsipan</h4>
                        <label class="flex items-start cursor-pointer group">
                            <div class="flex items-center h-6">
                                <input type="checkbox" id="archiveCheckbox" class="w-5 h-5 text-amber-600 rounded border-2 border-amber-400 focus:ring-2 focus:ring-amber-500 cursor-pointer transition-all">
                            </div>
                            <div class="ml-3">
                                <span class="text-sm font-semibold text-amber-900 group-hover:text-amber-700 transition-colors">
                                    Arsipkan data semester ini ke tabel arsip
                                </span>
                                <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                                    ✓ Data disimpan ke tabel arsip untuk dokumentasi permanen<br>
                                    ✓ Jika tidak dicentang, data tetap ada tapi read-only
                                </p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Action Buttons Card --}}
            <div class="bg-white rounded-2xl shadow-lg border-2 border-gray-200 p-6 flex flex-col justify-center">
                <div class="space-y-3">
                    <button onclick="processKenaikan()" class="w-full px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-95">
                        <i class="fas fa-rocket mr-2"></i> Proses Kenaikan Kelas
                    </button>
                    <button onclick="resetForm()" class="w-full px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all duration-200">
                        <i class="fas fa-redo mr-2"></i> Reset Form
                    </button>
                </div>
            </div>
        </div>

        {{-- Info Tips --}}
        <div class="mt-6 bg-gradient-to-r from-blue-50 to-cyan-50 border-2 border-blue-200 rounded-2xl p-6 shadow-lg">
            <div class="flex gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-lightbulb text-white text-xl"></i>
                    </div>
                </div>
                <div>
                    <h4 class="text-lg font-bold text-blue-900 mb-2">Tips Penggunaan</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-sm text-blue-800">
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                            <span>Santri tetap di kelas yang sama, hanya pindah tahun ajaran</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                            <span>Pilih "Arsipkan" untuk simpan data ke tabel arsip</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                            <span>Pastikan semua penilaian sudah selesai sebelum proses</span>
                        </div>
                        <div class="flex items-start gap-2">
                            <i class="fas fa-check-circle text-blue-600 mt-0.5"></i>
                            <span>Data lama tetap tersimpan meskipun tidak diarsipkan</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @else
        {{-- No Active Year Warning --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <div class="p-16 text-center">
                <div class="mb-6">
                    <div class="w-24 h-24 bg-yellow-100 rounded-full flex items-center justify-center mx-auto">
                        <i class="fas fa-exclamation-triangle text-yellow-600 text-5xl"></i>
                    </div>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-3">Tidak Ada Tahun Ajaran Aktif</h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    Silakan aktifkan tahun ajaran terlebih dahulu di halaman Tahun Ajaran sebelum melakukan kenaikan kelas.
                </p>
                <a href="{{ route('manajemen-sekolah.tahun-ajaran.index') }}" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold rounded-xl hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:scale-105">
                    <i class="fas fa-arrow-left mr-2"></i> Ke Halaman Tahun Ajaran
                </a>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- Hidden Form --}}
<form id="kenaikanForm" action="{{ route('manajemen-sekolah.kenaikan-kelas.process') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="source_kelas_id" id="sourceKelasId">
    <input type="hidden" name="target_tahun_ajaran_id" id="targetTahunAjaranId">
    <input type="hidden" name="santri_ids" id="santriIds">
    <input type="hidden" name="archive_data" id="archiveData" value="false">
</form>

<script>
let currentStep = 1;
let selectedKelas = null;
let selectedYear = null;
let allSantriData = [];
let selectAllState = false;

// Update progress steps
function updateSteps() {
    // Reset all steps
    for (let i = 1; i <= 4; i++) {
        const circle = document.getElementById(`step${i}-circle`);
        const text = document.getElementById(`step${i}-text`);
        
        if (i < currentStep) {
            circle.className = 'w-12 h-12 rounded-full bg-green-500 text-white flex items-center justify-center font-bold text-lg shadow-lg transition-all duration-300';
            circle.innerHTML = '<i class="fas fa-check"></i>';
            text.className = 'mt-2 text-sm font-semibold text-green-600 transition-all duration-300';
        } else if (i === currentStep) {
            circle.className = 'w-12 h-12 rounded-full bg-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-lg transition-all duration-300';
            circle.textContent = i;
            text.className = 'mt-2 text-sm font-semibold text-indigo-600 transition-all duration-300';
        } else {
            circle.className = 'w-12 h-12 rounded-full bg-gray-300 text-gray-500 flex items-center justify-center font-bold text-lg shadow-md transition-all duration-300';
            circle.textContent = i;
            text.className = 'mt-2 text-sm font-medium text-gray-500 transition-all duration-300';
        }
    }
    
    // Update progress bars
    document.getElementById('progress-1-2').style.width = currentStep > 1 ? '100%' : '0%';
    document.getElementById('progress-2-3').style.width = currentStep > 2 ? '100%' : '0%';
    document.getElementById('progress-3-4').style.width = currentStep > 3 ? '100%' : '0%';
}

// Update summary
function updateSummary() {
    document.getElementById('summaryKelas').textContent = selectedKelas ? selectedKelas.name : '-';
    document.getElementById('summaryYear').textContent = selectedYear ? selectedYear.name : '-';
    
    const selectedCount = document.querySelectorAll('.santriCheckbox:checked').length;
    document.getElementById('summaryCount').textContent = selectedCount;
    document.getElementById('summaryCount').className = selectedCount > 0 
        ? 'text-lg font-bold text-green-600' 
        : 'text-lg font-bold text-gray-400';
}

// Kelas selection
document.getElementById('kelasSelect').addEventListener('change', function() {
    const kelasId = this.value;
    const kelasName = this.options[this.selectedIndex].text;
    
    if (!kelasId) {
        selectedKelas = null;
        currentStep = 1;
        document.getElementById('santriContainer').innerHTML = `
            <div class="text-center py-16">
                <i class="fas fa-hand-pointer text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg font-medium">Pilih kelas terlebih dahulu</p>
                <p class="text-gray-400 text-sm mt-2">Data santri akan muncul di sini</p>
            </div>`;
        document.getElementById('kelasInfo').classList.add('hidden');
        document.getElementById('selectAllBtn').classList.add('hidden');
        updateSteps();
        updateSummary();
        return;
    }

    selectedKelas = { id: kelasId, name: kelasName };
    currentStep = 2;
    updateSteps();
    updateSummary();

    document.getElementById('kelasInfo').classList.remove('hidden');
    document.getElementById('santriContainer').innerHTML = `
        <div class="text-center py-16">
            <div class="animate-spin w-16 h-16 border-4 border-indigo-200 border-t-indigo-600 rounded-full mx-auto mb-4"></div>
            <p class="text-gray-600 font-medium">Memuat data santri...</p>
        </div>`;

    // Get santri via AJAX
    fetch(`{{ route('manajemen-sekolah.kenaikan-kelas.getSantri') }}?kelas_id=${kelasId}&tahun_ajaran_id={{ $activeYear->id ?? '' }}`)
        .then(response => response.json())
        .then(data => {
            allSantriData = data;
            document.getElementById('kelasInfo').classList.add('hidden');
            
            if (data.length === 0) {
                document.getElementById('santriContainer').innerHTML = `
                    <div class="text-center py-16">
                        <i class="fas fa-inbox text-gray-300 text-6xl mb-4"></i>
                        <p class="text-gray-500 text-lg font-medium">Tidak ada santri di kelas ini</p>
                        <p class="text-gray-400 text-sm mt-2">Silakan pilih kelas lain</p>
                    </div>`;
                document.getElementById('selectAllBtn').classList.add('hidden');
            } else {
                document.getElementById('selectAllBtn').classList.remove('hidden');
                let html = '<div class="grid grid-cols-1 md:grid-cols-2 gap-3">';
                data.forEach(santri => {
                    html += `
                        <label class="flex items-center p-4 bg-gray-50 hover:bg-indigo-50 rounded-xl cursor-pointer transition-all duration-200 border-2 border-transparent hover:border-indigo-300 group">
                            <input type="checkbox" class="santriCheckbox w-5 h-5 text-indigo-600 rounded border-2 border-gray-300 focus:ring-2 focus:ring-indigo-500 cursor-pointer transition-all" value="${santri.id}" onchange="updateSummary()">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-user-graduate text-indigo-600 group-hover:text-indigo-700"></i>
                                    <span class="font-semibold text-gray-900 group-hover:text-indigo-900">${santri.nama}</span>
                                </div>
                                <span class="text-xs text-gray-500 mt-1 block">NISN: ${santri.nisn}</span>
                            </div>
                        </label>
                    `;
                });
                html += '</div>';
                document.getElementById('santriContainer').innerHTML = html;
            }
        })
        .catch(error => {
            document.getElementById('santriContainer').innerHTML = `
                <div class="text-center py-16">
                    <i class="fas fa-exclamation-triangle text-red-400 text-6xl mb-4"></i>
                    <p class="text-red-600 font-medium">Gagal memuat data santri</p>
                    <p class="text-gray-500 text-sm mt-2">Silakan coba lagi</p>
                </div>`;
        });
});

// Year selection
document.getElementById('targetYearSelect').addEventListener('change', function() {
    const yearId = this.value;
    const yearName = this.options[this.selectedIndex].text;
    
    if (!yearId) {
        selectedYear = null;
        document.getElementById('yearInfo').classList.add('hidden');
        if (currentStep > 2) currentStep = 2;
    } else {
        selectedYear = { id: yearId, name: yearName };
        document.getElementById('yearInfo').classList.remove('hidden');
        if (currentStep === 2) currentStep = 3;
    }
    
    updateSteps();
    updateSummary();
});

// Toggle select all
function toggleSelectAll() {
    selectAllState = !selectAllState;
    const checkboxes = document.querySelectorAll('.santriCheckbox');
    checkboxes.forEach(cb => cb.checked = selectAllState);
    
    const btn = document.getElementById('selectAllBtn');
    if (selectAllState) {
        btn.innerHTML = '<i class="fas fa-times mr-2"></i>Batal Pilih Semua';
        btn.className = 'px-4 py-2 bg-red-500/80 hover:bg-red-600 text-white text-sm font-medium rounded-lg transition-all duration-200';
    } else {
        btn.innerHTML = '<i class="fas fa-check-double mr-2"></i>Pilih Semua';
        btn.className = 'px-4 py-2 bg-white/20 hover:bg-white/30 text-white text-sm font-medium rounded-lg transition-all duration-200';
    }
    
    updateSummary();
}

function getSelectedSantri() {
    const checkboxes = document.querySelectorAll('.santriCheckbox:checked');
    return Array.from(checkboxes).map(cb => cb.value);
}

function processKenaikan() {
    const sourceKelasId = document.getElementById('kelasSelect').value;
    const targetTahunAjaranId = document.getElementById('targetYearSelect').value;
    const santriIds = getSelectedSantri();
    const shouldArchive = document.getElementById('archiveCheckbox').checked;

    // Validation
    if (!sourceKelasId) {
        Swal.fire({
            icon: 'error',
            title: 'Pilih Kelas Dulu',
            text: 'Silakan pilih kelas sumber terlebih dahulu',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    if (!targetTahunAjaranId) {
        Swal.fire({
            icon: 'error',
            title: 'Pilih Tahun Ajaran',
            text: 'Silakan pilih tahun ajaran tujuan terlebih dahulu',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    if (santriIds.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Pilih Santri',
            text: 'Pilih minimal satu santri untuk dinaikkan kelasnya',
            confirmButtonColor: '#4f46e5'
        });
        return;
    }

    // Confirmation
    let htmlContent = `
        <div class="text-left space-y-4">
            <div class="bg-indigo-50 p-4 rounded-lg border-l-4 border-indigo-500">
                <p class="text-sm font-semibold text-indigo-900 mb-2">📋 Detail Kenaikan Kelas:</p>
                <ul class="text-sm text-indigo-800 space-y-1">
                    <li>• Kelas: <strong>${selectedKelas.name}</strong></li>
                    <li>• Tujuan: <strong>${selectedYear.name}</strong></li>
                    <li>• Jumlah Santri: <strong>${santriIds.length} orang</strong></li>
                </ul>
            </div>
    `;
    
    if (shouldArchive) {
        htmlContent += `
            <div class="bg-amber-50 p-4 rounded-lg border-l-4 border-amber-500">
                <p class="text-sm font-semibold text-amber-900 mb-2">
                    <i class="fas fa-archive mr-2"></i>Pengarsipan Aktif
                </p>
                <p class="text-sm text-amber-800">
                    ✓ Data akan disimpan ke <strong>tabel arsip</strong> untuk dokumentasi permanen
                </p>
            </div>
        `;
    } else {
        htmlContent += `
            <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-500">
                <p class="text-sm font-semibold text-blue-900 mb-2">
                    <i class="fas fa-info-circle mr-2"></i>Mode Read-Only
                </p>
                <p class="text-sm text-blue-800">
                    ℹ Data tetap tersimpan dan dapat dilihat, namun tidak dapat diubah (read-only)
                </p>
            </div>
        `;
    }
    
    htmlContent += '</div>';

    Swal.fire({
        title: 'Konfirmasi Kenaikan Kelas',
        html: htmlContent,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-check mr-2"></i>Ya, Proses Sekarang',
        cancelButtonText: '<i class="fas fa-times mr-2"></i>Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-lg px-6 py-3 font-semibold',
            cancelButton: 'rounded-lg px-6 py-3 font-semibold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Memproses...',
                html: 'Sedang memindahkan santri ke tahun ajaran baru',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Submit form
            document.getElementById('sourceKelasId').value = sourceKelasId;
            document.getElementById('targetTahunAjaranId').value = targetTahunAjaranId;
            document.getElementById('santriIds').value = JSON.stringify(santriIds);
            document.getElementById('archiveData').value = shouldArchive ? 'true' : 'false';
            document.getElementById('kenaikanForm').submit();
        }
    });
}

function resetForm() {
    Swal.fire({
        title: 'Reset Form?',
        text: 'Semua pilihan akan dikembalikan ke awal',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#4f46e5',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('kelasSelect').value = '';
            document.getElementById('targetYearSelect').value = '';
            document.getElementById('archiveCheckbox').checked = false;
            document.getElementById('santriContainer').innerHTML = `
                <div class="text-center py-16">
                    <i class="fas fa-hand-pointer text-gray-300 text-6xl mb-4"></i>
                    <p class="text-gray-500 text-lg font-medium">Pilih kelas terlebih dahulu</p>
                    <p class="text-gray-400 text-sm mt-2">Data santri akan muncul di sini</p>
                </div>`;
            document.getElementById('kelasInfo').classList.add('hidden');
            document.getElementById('yearInfo').classList.add('hidden');
            document.getElementById('selectAllBtn').classList.add('hidden');
            
            selectedKelas = null;
            selectedYear = null;
            currentStep = 1;
            selectAllState = false;
            
            updateSteps();
            updateSummary();
            
            Swal.fire({
                icon: 'success',
                title: 'Form Direset',
                text: 'Silakan mulai dari awal',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
}

// Initialize
updateSteps();
updateSummary();
</script>
@endsection
