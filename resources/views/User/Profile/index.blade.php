@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
<div class="bg-gray-50 min-h-screen p-6">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6 sm:p-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Pengaturan Profil</h2>

                @if(session('success'))
                    <div class="bg-green-50 text-green-700 p-4 rounded-lg mb-6 flex items-center gap-3">
                        <i class="fas fa-check-circle"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="bg-red-50 text-red-700 p-4 rounded-lg mb-6">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        {{-- Left Column: Photo --}}
                        <div class="col-span-1 flex flex-col items-center">
                            <div class="relative group">
                                <div class="w-40 h-40 rounded-full overflow-hidden border-4 border-white shadow-lg bg-gray-100">
                                    @if($profile && $profile->foto)
                                        <img src="{{ asset('storage/' . $profile->foto) }}" alt="Profile Photo" class="w-full h-full object-cover">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($profile->nama ?? 'User') }}&background=random&size=200" alt="Profile Photo" class="w-full h-full object-cover">
                                    @endif
                                </div>
                                <label for="foto" class="absolute bottom-2 right-2 bg-blue-600 text-white p-2 rounded-full cursor-pointer hover:bg-blue-700 transition shadow-md" title="Ubah Foto">
                                    <i class="fas fa-camera"></i>
                                </label>
                                <input type="file" id="foto" name="foto" class="hidden" accept="image/*">
                            </div>
                            <p class="text-xs text-gray-500 mt-3 text-center">Klik ikon kamera untuk mengubah foto. Format: JPG, PNG. Max: 2MB.</p>
                        </div>

                        {{-- Right Column: Form Data --}}
                        <div class="col-span-1 md:col-span-2 space-y-5">
                            
                            {{-- Nama --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                                <input type="text" name="nama" value="{{ old('nama', $profile->nama ?? '') }}" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- Jabatan (Guru Only) --}}
                            @if($guard === 'guru')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan</label>
                                <input type="text" value="{{ $profile->jabatan ?? '-' }}" disabled class="w-full border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed text-gray-500">
                                <p class="text-xs text-gray-500 mt-1">Jabatan hanya dapat diubah oleh Kepala Sekolah/Admin.</p>
                            </div>
                            @endif

                            {{-- NISN (Santri Only) --}}
                            @if($guard === 'santri')
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">NISN</label>
                                <input type="text" value="{{ $user->nisn ?? '-' }}" disabled class="w-full border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed text-gray-500">
                            </div>
                            @endif

                            {{-- No HP --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nomor HP / WhatsApp</label>
                                <input type="text" name="no_hp" value="{{ old('no_hp', $profile->no_hp ?? '') }}" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            {{-- Alamat --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                                <textarea name="alamat" rows="3" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">{{ old('alamat', $profile->alamat ?? '') }}</textarea>
                            </div>

                            <div class="border-t border-gray-200 pt-5 mt-5">
                                <h3 class="text-lg font-semibold text-gray-800 mb-4">Ganti Password</h3>
                                <p class="text-sm text-gray-500 mb-4">Biarkan kosong jika tidak ingin mengganti password.</p>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                                        <input type="password" name="password" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password</label>
                                        <input type="password" name="password_confirmation" class="w-full border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button type="submit" class="bg-blue-600 text-white px-6 py-2.5 rounded-lg hover:bg-blue-700 transition font-medium shadow-sm flex items-center gap-2">
                                    <i class="fas fa-save"></i> Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // Photo Preview
    document.getElementById('foto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const img = document.querySelector('.w-40.h-40 img');
                if (img) {
                    img.src = event.target.result;
                }
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endsection
