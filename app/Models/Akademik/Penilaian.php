<?php

namespace App\Models\Akademik;

use App\Models\User\SantriProfile;
use App\Models\User\GuruProfile; // Asumsi Model GuruProfile ada
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'santri_profile_id',
        'kelas',
        'mapel_id',
        'guru_profile_id', // DIUBAH: Mengacu ke tabel GuruProfile
        'nilai_angka',
        'tahun_ajaran',
        'semester',
    ];

    // ... (Relasi santriProfile dan mataPelajaran tetap sama)

    /**
     * Relasi Many-to-One: Penilaian diinput oleh satu Guru Profile (Detail).
     */
    public function guruProfile()
    {
        // DIUBAH: Mengacu ke Model GuruProfile
        return $this->belongsTo(GuruProfile::class, 'guru_profile_id'); 
    }
}
