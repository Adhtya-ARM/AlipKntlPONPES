@extends('layouts.app')

@section('title', 'Manajemen Struktur Organisasi')

@section('content')
<div class="bg-gray-50 min-h-screen p-6" x-data="strukturHandler()">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h2 class="text-3xl font-bold text-gray-800">Manajemen Struktur Organisasi</h2>
            <p class="text-sm text-gray-500 mt-1">Pilih guru dengan jabatan struktural yang akan ditampilkan di Landing Page (Maksimal {{ $maxDisplayLimit }} guru).</p>
        </div>

        <!-- Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-start gap-3">
            <i class="fas fa-info-circle text-blue-600 mt-1"></i>
            <div class="flex-1">
                <p class="text-sm text-blue-800 font-medium">Informasi Penting:</p>
                <ul class="text-sm text-blue-700 mt-1 space-y-1 list-disc list-inside">
                    <li>Hanya guru dengan jabatan struktural (bukan "Guru") yang dapat ditampilkan</li>
                    <li>Maksimal <strong>{{ $maxDisplayLimit }} guru</strong> dapat ditampilkan di Landing Page</li>
                    <li>Saat ini: <strong>{{ $currentDisplayCount }}/{{ $maxDisplayLimit }}</strong> slot terisi</li>
                    <li>Pastikan foto profil guru sudah diupload untuk tampilan terbaik</li>
                </ul>
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @forelse($gurus as $guru)
            <div class="bg-white rounded-xl shadow-sm hover:shadow-md transition duration-300 border border-gray-200 overflow-hidden">
                <!-- Card Header with Toggle -->
                <div class="p-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-100 flex justify-between items-center">
                    <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        {{ $guru->jabatan ?? 'Jabatan' }}
                    </span>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" class="sr-only peer" 
                               @change="updateStatus({{ $guru->id }}, $event.target.checked)"
                               {{ $guru->tampilkan_di_landing ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                    </label>
                </div>
                
                <!-- Photo & Info -->
                <div class="p-6 text-center">
                    <div class="w-24 h-24 mx-auto mb-4 rounded-full overflow-hidden border-4 border-green-50 shadow-md">
                        @if($guru->foto)
                            <img src="{{ asset('storage/' . $guru->foto) }}" alt="{{ $guru->nama }}" class="w-full h-full object-cover">
                        @else
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($guru->nama) }}&background=10b981&color=fff&size=200" alt="{{ $guru->nama }}" class="w-full h-full object-cover">
                        @endif
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1 line-clamp-2">{{ $guru->nama }}</h3>
                    <p class="text-sm text-gray-500">
                        @if(!$guru->foto)
                            <span class="text-amber-600"><i class="fas fa-exclamation-triangle"></i> Belum upload foto</span>
                        @else
                            <i class="fas fa-check-circle text-green-600"></i> Foto tersedia
                        @endif
                    </p>
                </div>
                
                <!-- Status Badge -->
                <div class="px-6 pb-4">
                    <div class="text-center py-2 px-4 rounded-lg {{ $guru->tampilkan_di_landing ? 'bg-green-50 text-green-700' : 'bg-gray-50 text-gray-500' }}">
                        <i class="fas {{ $guru->tampilkan_di_landing ? 'fa-eye' : 'fa-eye-slash' }} mr-1"></i>
                        <span class="text-sm font-medium">
                            {{ $guru->tampilkan_di_landing ? 'Ditampilkan' : 'Tidak Ditampilkan' }}
                        </span>
                    </div>

                    <div class="mt-3 flex justify-center gap-2">
                        @if($guru->guru)
                        <a href="{{ route('guru.edit', $guru->guru->id) }}" class="px-3 py-1.5 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                            <i class="fas fa-user-edit mr-2"></i> Edit Profil Guru
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <i class="fas fa-users text-gray-300 text-6xl mb-4"></i>
                <p class="text-gray-500 text-lg">Tidak ada guru dengan jabatan struktural.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>

<script>
    function strukturHandler() {
        return {
            async updateStatus(guruId, isChecked) {
                try {
                    const response = await axios.post('{{ route("informasi.struktur-organisasi.update") }}', {
                        guru_id: guruId,
                        tampilkan: isChecked
                    });
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                    
                    // Reload to update count
                    setTimeout(() => window.location.reload(), 1500);
                } catch (error) {
                    console.error(error);
                    
                    // Revert checkbox
                    event.target.checked = !isChecked;
                    
                    const errorMsg = error.response?.data?.message || 'Gagal memperbarui status';
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: errorMsg
                    });
                }
            }
        }
    }
</script>
@endsection
