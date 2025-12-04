@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Tahun Ajaran</h2>
        <div class="flex gap-3">
            <a href="{{ route('manajemen-sekolah.kenaikan-kelas.index') }}" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-purple-600 to-purple-700 text-white font-semibold rounded-lg shadow-lg hover:from-purple-700 hover:to-purple-800 transition-all duration-200 transform hover:scale-105">
                <i class="fas fa-level-up-alt mr-2"></i> Kenaikan Kelas
            </a>
            <button onclick="openModal('modal-create')" class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-semibold rounded-lg shadow-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-200 transform hover:scale-105">
                <i class="fas fa-plus mr-2"></i> Tambah Tahun Ajaran
            </button>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun Ajaran</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semester</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenjang</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aktivasi</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Arsip</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($tahunAjarans as $ta)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{{ $ta->nama }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">{{ $ta->semester }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-gray-500">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $ta->jenjang == 'SMP' ? 'bg-blue-100 text-blue-800' : ($ta->jenjang == 'SMA' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800') }}">
                            {{ $ta->jenjang ?? 'Semua' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($ta->is_active)
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i> Aktif
                            </span>
                        @else
                            <form action="{{ route('manajemen-sekolah.tahun-ajaran.activate', $ta->id) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-gray-100 hover:bg-green-100 text-gray-600 hover:text-green-700 text-xs font-medium rounded-md transition-all duration-200 border border-gray-300 hover:border-green-400">
                                    <i class="fas fa-power-off mr-1"></i> Aktifkan
                                </button>
                            </form>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($ta->status === 'Aktif')
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                <i class="fas fa-play-circle mr-1"></i> Aktif
                            </span>
                        @elseif($ta->status === 'Terarsip')
                            <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-amber-100 text-amber-800">
                                <i class="fas fa-archive mr-1"></i> Terarsip
                            </span>
                        @else
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-600">
                                    <i class="fas fa-pause-circle mr-1"></i> Tidak Aktif
                                </span>
                                @if(!$ta->is_active && $ta->status !== 'Terarsip')
                                    <form id="archive-form-{{ $ta->id }}" action="{{ route('manajemen-sekolah.tahun-ajaran.archive', $ta->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="button" onclick="confirmArchiveSemester({{ $ta->id }}, '{{ addslashes($ta->nama) }}', '{{ $ta->semester }}')" class="px-3 py-1 bg-yellow-50 hover:bg-yellow-100 text-yellow-700 text-xs font-medium rounded-md transition-all duration-200 border border-yellow-200 hover:border-yellow-400">
                                            <i class="fas fa-archive mr-1"></i> Arsipkan
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($ta->status !== 'Terarsip')
                            <button onclick='editTahunAjaran(@json($ta))' class="px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 hover:text-blue-700 text-xs font-medium rounded-md transition-all duration-200 border border-blue-200 hover:border-blue-400">
                                <i class="fas fa-edit mr-1"></i> Edit
                            </button>
                            @if(!$ta->is_active)
                            <form action="{{ route('manajemen-sekolah.tahun-ajaran.destroy', $ta->id) }}" method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-600 hover:text-red-700 text-xs font-medium rounded-md transition-all duration-200 border border-red-200 hover:border-red-400">
                                    <i class="fas fa-trash mr-1"></i> Hapus
                                </button>
                            </form>
                            @endif
                            @else
                                <span class="text-sm text-gray-500 italic">(Read only)</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Create -->
<div id="modal-create" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Tambah Tahun Ajaran</h3>
            <form action="{{ route('manajemen-sekolah.tahun-ajaran.store') }}" method="POST" class="mt-2 text-left">
                @csrf
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama (Contoh: 2024/2025)</label>
                    <input type="text" name="nama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenjang</label>
                    <select name="jenjang" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="Semua">Semua (SMP & SMA)</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Semester</label>
                    <select name="semester" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="Ganjil" selected>Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" onclick="closeModal('modal-create')" class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit (Simplified, ideally use Alpine or JS to populate) -->
<div id="modal-edit" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3 text-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Edit Tahun Ajaran</h3>
            <form id="form-edit" method="POST" class="mt-2 text-left">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Nama</label>
                    <input type="text" name="nama" id="edit-nama" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Jenjang</label>
                    <select name="jenjang" id="edit-jenjang" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" required>
                        <option value="Semua">Semua (SMP & SMA)</option>
                        <option value="SMP">SMP</option>
                        <option value="SMA">SMA</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Semester</label>
                    <select name="semester" id="edit-semester" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="Ganjil">Ganjil</option>
                        <option value="Genap">Genap</option>
                    </select>
                </div>
                <div class="flex justify-end mt-4">
                    <button type="button" onclick="closeModal('modal-edit')" class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">Batal</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(id) {
        document.getElementById(id).classList.remove('hidden');
    }
    function closeModal(id) {
        document.getElementById(id).classList.add('hidden');
    }
    function editTahunAjaran(data) {
        document.getElementById('edit-nama').value = data.nama;
        document.getElementById('edit-jenjang').value = data.jenjang || 'Semua';
        document.getElementById('edit-semester').value = data.semester;
        document.getElementById('form-edit').action = "{{ route('manajemen-sekolah.tahun-ajaran.index') }}/" + data.id;
        openModal('modal-edit');
    }

    function confirmArchiveSemester(id, nama, semester) {
        Swal.fire({
            title: 'Arsipkan Semester?',
            html: `Apakah Anda yakin ingin mengarsipkan semester <strong>${nama} ${semester}</strong>?<br><br><small class="text-gray-600">✅ Semester ini akan menjadi <strong>read-only</strong> (tidak dapat diubah/dihapus)<br>✅ Semester baru dengan label yang sama akan dibuat secara otomatis<br>✅ Semester baru dimulai dari <strong>kondisi kosong</strong> (tidak ada data kelas/nilai)<br>⚠️ Proses ini <strong>tidak dapat dibatalkan</strong>!</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d97706',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Arsipkan!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('archive-form-' + id).submit();
            }
        });
    }
</script>
@endsection
