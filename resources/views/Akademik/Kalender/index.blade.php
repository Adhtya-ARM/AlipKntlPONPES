@extends('layouts.app')

@section('title','Kalender Pendidikan')

@section('content')
@php
    $user = Auth::guard('guru')->user();
    $jabatan = strtolower($user->guruProfile->jabatan ?? '');
    $canManage = in_array($jabatan, ['kepala sekolah', 'wakil kepala sekolah', 'kepsek', 'waka']);
@endphp

<div class="p-8 bg-gray-50 min-h-screen font-sans text-gray-800">
    {{-- Title --}}
    <h1 class="text-xl font-bold text-gray-900 mb-6">Kalender Pendidikan</h1>

    {{-- 💡 Initializing Alpine.js Component --}}
    <div x-data="rencanaApp({{ json_encode($dataForAlpine ?? []) }})" x-init="init()" class="space-y-6">
        
        {{-- Card: Calendar --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            {{-- Calendar Header --}}
            {{-- HANYA BERISI KONTROL NAVIGASI DAN TOMBOL AKSI ENTRI. EXPORT PDF TELAH DIHAPUS. --}}
            <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
                {{-- Left Side: Year Selector & Nav --}}
                <div class="flex items-center gap-4 w-full md:w-auto">
                    
                    {{-- Year Selector --}}
                    <div class="relative">
                        <select x-model="selectedYear" @change="goTo(selectedYear, selectedMonth)" class="appearance-none border border-gray-200 rounded-lg px-4 py-2 pr-8 bg-white text-gray-700 font-medium focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 transition-all cursor-pointer hover:border-gray-300">
                            <template x-for="y in years" :key="y"> 
                                <option :value="y" x-text="y + '-' + (y + 1)"></option>
                            </template>
                        </select>
                    </div>
                    
                    {{-- Month Label and Nav Buttons --}}
                    <span class="text-lg font-bold text-gray-800" x-text="monthLabel()"></span>
                    <div class="flex items-center gap-2">
                        <button @click="prevMonth()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <button @click="nextMonth()" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-500 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Right Side: Action Buttons --}}
                @if($canManage)
                <div class="flex items-center gap-3">
                    <button @click="clearSelection()" class="px-4 py-2 text-sm border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all font-medium">
                        Kosongkan Seleksi
                    </button>
                    <button @click="removeBySelection()" class="px-4 py-2 text-sm border border-red-100 text-red-500 rounded-lg hover:bg-red-50 hover:border-red-200 transition-all font-medium" :disabled="selectedDates.length === 0">
                        Hapus Berdasarkan Seleksi
                    </button>
                </div>
                @endif
            </div>

            {{-- Calendar Grid --}}
            <div class="border border-gray-100 rounded-xl overflow-hidden">
                {{-- Days Header: SEN, SEL, RAB, KAM, JUM, SAB, MIN --}}
                <div class="grid grid-cols-7 border-b border-gray-100 bg-gray-50/50">
                    <template x-for="d in ['SEN','SEL','RAB','KAM','JUM','SAB','MIN']" :key="d">
                        <div class="py-4 text-center text-xs font-semibold text-gray-400 tracking-widest" x-text="d"></div>
                    </template>
                </div>

                {{-- Days Cells (Crucial part) --}}
                <div class="grid grid-cols-7 bg-white">
                    <template x-for="(cell, index) in calendarDays" :key="index">
                        <div
                            class="min-h-[120px] border-b border-r border-gray-100 relative p-3 cursor-pointer transition-all duration-200 group hover:bg-gray-50/30"
                            :class="{
                                'bg-white': cell.isCurrentMonth && !isWeekend(cell.dayIndex),
                                'bg-red-50/30': cell.isCurrentMonth && isWeekend(cell.dayIndex),
                                'bg-gray-50/50': !cell.isCurrentMonth,
                                'ring-2 ring-indigo-500 ring-inset z-10': isDateSelected(cell.date),
                                'border-b-0': Math.ceil((index+1)/7) === Math.ceil(calendarDays.length/7),
                                'border-r-0': (index+1)%7 === 0
                            }"
                            @click="toggleSelect(cell.date, $event)"
                        >
                            {{-- Day Number --}}
                            <div class="flex justify-between items-start">
                                <span 
                                    class="text-sm font-medium" 
                                    :class="{
                                        'text-gray-700': cell.isCurrentMonth && !isWeekend(cell.dayIndex),
                                        'text-red-500': cell.isCurrentMonth && isWeekend(cell.dayIndex),
                                        'text-gray-300': !cell.isCurrentMonth
                                    }" 
                                    x-text="cell.day"
                                ></span>
                            </div>
                            
                            {{-- Indicators --}}
                            <div class="mt-3 flex flex-col gap-1.5">
                                <template x-if="hasEntry(cell.date)">
                                    <div class="flex flex-col gap-1">
                                        <template x-for="entry in getEntries(cell.date)" :key="entry.id"> 
                                            <div class="flex items-center gap-1.5 px-1.5 py-0.5 rounded hover:bg-gray-100 transition-colors">
                                                <span class="w-1.5 h-1.5 rounded-full flex-shrink-0"
                                                    :class="{
                                                        'bg-red-500': entry.jenis === 'Libur',
                                                        'bg-blue-500': entry.jenis === 'KBM',
                                                        'bg-yellow-500': entry.jenis === 'Ujian',
                                                        'bg-gray-500': !['Libur','KBM','Ujian'].includes(entry.jenis)
                                                    }"
                                                ></span>
                                                <span class="text-[11px] text-gray-600 truncate font-medium" x-text="entry.jenis"></span>
                                            </div>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        {{-- Bottom Section: Form & Selected List --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            {{-- Form Card --}}
            @if($canManage)
            <div class="lg:col-span-8 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-800 mb-6 text-lg">Tambah / Ubah Rentang</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Dari</label>
                        <div class="relative">
                            <input type="date" x-model="form.from" class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 pl-4 pr-4 py-2.5 text-gray-700 transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Sampai</label>
                        <div class="relative">
                            <input type="date" x-model="form.to" class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 pl-4 pr-4 py-2.5 text-gray-700 transition-all">
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis</label>
                    <div class="relative">
                        <select x-model="form.jenis" class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 appearance-none py-2.5 pl-4 pr-10 text-gray-700 transition-all cursor-pointer bg-white">
                            <option value="KBM">KBM</option>
                            <option value="Ujian">Ujian</option>
                            <option value="Libur">Libur</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Judul (opsional)</label>
                    <input type="text" x-model="form.judul" class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 py-2.5 px-4 text-gray-700 transition-all" placeholder="Contoh: Libur Semester Ganjil">
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Catatan</label>
                    <textarea x-model="form.catatan" rows="3" class="w-full border-gray-200 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 py-2.5 px-4 text-gray-700 transition-all resize-none"></textarea>
                </div>

                <div>
                    <button @click="saveRange()" class="px-6 py-2.5 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-all shadow-sm hover:shadow focus:ring-2 focus:ring-green-500/50">Simpan</button>
                </div>
            </div>
            @endif

            {{-- Selected Entries Card --}}
            <div class="lg:col-span-4 bg-white p-6 rounded-xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-800 mb-4 text-lg">Entri Tanggal Terpilih</h3>
                <div class="text-xs text-gray-400 mb-4" x-text="selectedInfo()"></div>
                
                <div class="space-y-3 max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                    <template x-if="selectedDates.length === 0">
                        <div class="text-sm text-gray-400 italic py-4 text-center bg-gray-50 rounded-lg border border-dashed border-gray-200">
                            Pilih tanggal pada kalender untuk melihat atau mengisi entri.
                        </div>
                    </template>

                    <template x-for="d in selectedDates" :key="d">
                        <div class="group border-l-4 border-indigo-500 bg-gray-50 p-4 rounded-r-lg hover:bg-indigo-50/30 transition-colors">
                            <div class="text-sm font-bold text-gray-800" x-text="formatDate(d)"></div>
                            <div class="mt-2">
                                <template x-if="entriesByDate[d] && entriesByDate[d].length">
                                    <div class="space-y-2">
                                        <template x-for="e in entriesByDate[d]" :key="e.id">
                                            <div class="text-xs text-gray-600 flex items-center gap-2 bg-white p-2 rounded border border-gray-100 shadow-sm cursor-pointer hover:bg-gray-50" @click="editEntry(e.id)">
                                                <span class="w-2 h-2 rounded-full flex-shrink-0" 
                                                    :class="{
                                                        'bg-red-500': e.jenis === 'Libur',
                                                        'bg-blue-500': e.jenis === 'KBM',
                                                        'bg-yellow-500': e.jenis === 'Ujian',
                                                        'bg-gray-500': !['Libur','KBM','Ujian'].includes(e.jenis)
                                                    }"
                                                ></span>
                                                <span class="font-medium" x-text="e.jenis"></span>
                                                <template x-if="e.judul">
                                                    <span class="text-gray-400 mx-1">|</span>
                                                </template>
                                                <span class="truncate" x-text="e.judul || '(Tanpa Judul)'"></span>
                                                @if($canManage)
                                                <button @click.stop="deleteEntry(e.id)" class="ml-auto text-red-400 hover:text-red-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                                @endif
                                            </div>
                                        </template>
                                    </div>
                                </template>
                                <template x-if="!entriesByDate[d] || !entriesByDate[d].length">
                                    <div class="text-xs text-gray-400 italic mt-1">- Tidak ada kegiatan -</div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Memastikan file script dipanggil di akhir --}}
@include('Akademik.Kalender.rencana-script') 
@endsection