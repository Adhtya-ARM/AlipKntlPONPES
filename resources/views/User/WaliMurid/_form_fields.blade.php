@php
    // Definisikan variabel $santri dan $statuses jika belum ada
    $santri = $santri ?? new App\Models\User\Santri();
    // Gunakan relasi santriprofile
    $santriprofile = $santri->santriprofile ?? (object)['nama' => null, 'alamat' => null, 'wali' => null, 'kelas' => null, 'kamar' => null, 'status' => null]; 
    $statuses = $statuses ?? ['aktif', 'non-aktif', 'lulus', 'dropout']; // Status baru ditambahkan
@endphp

<div class="space-y-4">
    {{-- Data Santri (Tabel santris) --}}
    <div>
        <label for="nis" class="block text-sm font-medium text-gray-700">NIS</label>
        <input type="text" name="nis" id="nis" value="{{ old('nis', $santri->nis) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('nis')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-medium text-gray-700">{{ $santri->exists ? 'Password Baru (Kosongkan jika tidak diubah)' : 'Password' }}</label>
        <input type="password" name="password" id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" {{ $santri->exists ? '' : 'required' }}>
        @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" {{ $santri->exists ? '' : 'required' }}>
    </div>

    <h3 class="text-lg font-medium pt-4 border-t mt-4">Data Profil</h3>

    {{-- Data Profile Santri (Tabel santri_profiles) --}}
    <div>
        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Lengkap</label>
        <input type="text" name="nama" id="nama" value="{{ old('nama', $santriprofile->nama) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('nama')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
    
    <div>
        <label for="alamat" class="block text-sm font-medium text-gray-700">Alamat</label>
        <textarea name="alamat" id="alamat" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('alamat', $santriprofile->alamat) }}</textarea>
        @error('alamat')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="wali" class="block text-sm font-medium text-gray-700">Wali (Nama Orang Tua)</label>
        <input type="text" name="wali" id="wali" value="{{ old('wali', $santriprofile->wali) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('wali')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
        <input type="text" name="kelas" id="kelas" value="{{ old('kelas', $santriprofile->kelas) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('kelas')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="kamar" class="block text-sm font-medium text-gray-700">Kamar</label>
        <input type="text" name="kamar" id="kamar" value="{{ old('kamar', $santriprofile->kamar) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
        @error('kamar')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
        <select name="status" id="status" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            @foreach ($statuses as $statusValue)
                @php
                    // Logic untuk menampilkan label
                    $statusLabel = match ($statusValue) {
                        'aktif' => 'Aktif',
                        'non-aktif' => 'Non-Aktif',
                        'lulus' => 'Lulus/Alumni',
                        'dropout' => 'Dropout',
                        default => $statusValue,
                    };
                @endphp
                <option 
                    value="{{ $statusValue }}" 
                    @selected(old('status', $santriprofile->status) == $statusValue)>
                    {{ $statusLabel }}
                </option>
            @endforeach
        </select>
        @error('status')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>
</div>