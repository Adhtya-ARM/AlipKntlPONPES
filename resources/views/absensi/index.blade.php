@extends('layouts.app')

@section('content')
<div class="container mx-auto p-4">
    <div class="mb-4">
        <h1 class="text-2xl font-semibold">Daftar Mata Pelajaran Ajar</h1>
    </div>

    <div class="bg-white shadow rounded overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mata Pelajaran</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Pertemuan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($guruMapels as $index => $guruMapel)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $guruMapel->mapel->nama_mapel }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $guruMapel->mapel->kelas }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ optional($guruMapel->pertemuanSetting)->jumlah_pertemuan ?? '0' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button 
                            @click="openPertemuanModal({{ $guruMapel->id }})" 
                            class="bg-blue-500 text-white px-3 py-1 rounded mr-2"
                            x-show="!{{ optional($guruMapel->pertemuanSetting)->jumlah_pertemuan ? 'true' : 'false' }}"
                        >
                            Set Pertemuan
                        </button>
                        <button 
                            @click="openAbsensiModal({{ $guruMapel->id }})" 
                            class="bg-green-500 text-white px-3 py-1 rounded"
                            x-show="{{ optional($guruMapel->pertemuanSetting)->jumlah_pertemuan ? 'true' : 'false' }}"
                        >
                            Isi Absensi
                        </button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal Set Pertemuan -->
    <div x-data="pertemuanModal()" x-cloak>
        <div x-show="open" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div @click.away="close()" class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6">
                <h2 class="text-xl font-bold mb-4" x-text="editing ? 'Edit Pertemuan' : 'Tambah Pertemuan'"></h2>
                <form :action="editing ? '{{ url('/akademik/absensi') }}/' + form.id : '{{ route('akademik.absensi.store') }}'" method="POST">
                    @csrf
                    <template x-if="editing">
                        <input type="hidden" name="_method" value="PATCH">
                    </template>

                    <div class="grid grid-cols-1 gap-3">
                        <div>
                            <label class="block text-sm">Guru Profile ID</label>
                            <input type="number" name="guru_profile_id" x-model.number="form.guru_profile_id" class="mt-1 block w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm">Mapel ID</label>
                            <input type="number" name="mapel_id" x-model.number="form.mapel_id" class="mt-1 block w-full border rounded px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm">Jumlah Pertemuan</label>
                            <input type="number" name="jumlah_pertemuan" x-model.number="form.jumlah_pertemuan" class="mt-1 block w-full border rounded px-3 py-2" min="0" required>
                        </div>
                        <div>
                            <label class="block text-sm">Jumlah Bab</label>
                            <input type="number" name="jumlah_bab" x-model.number="form.jumlah_bab" class="mt-1 block w-full border rounded px-3 py-2" min="0" required>
                        </div>
                        <div>
                            <label class="block text-sm">Keterangan</label>
                            <textarea name="keterangan" x-model="form.keterangan" class="mt-1 block w-full border rounded px-3 py-2"></textarea>
                        </div>
                    </div>

                    <div class="mt-4 flex justify-end">
                        <button type="button" @click="close()" class="mr-2 px-4 py-2 bg-gray-200 rounded">Tutup</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function absensiModal(){
    return {
        open: false,
        editing: false,
        form: { id: null, guru_profile_id: '', mapel_id: '', jumlah_pertemuan: 0, jumlah_bab: 0, keterangan: '' },
        openEdit(id, data){
            this.editing = true;
            this.form = {
                id: data.id,
                guru_profile_id: data.guru_profile_id || '',
                mapel_id: data.mapel_id || '',
                jumlah_pertemuan: data.jumlah_pertemuan || 0,
                jumlah_bab: data.jumlah_bab || 0,
                keterangan: data.keterangan || ''
            };
            this.open = true;
        },
        close(){
            this.open = false;
            this.editing = false;
            this.form = { id: null, guru_profile_id: '', mapel_id: '', jumlah_pertemuan: 0, jumlah_bab: 0, keterangan: '' };
        }
    }
}
</script>

@endsection
