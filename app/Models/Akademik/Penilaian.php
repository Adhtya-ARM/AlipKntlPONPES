<?php

namespace App\Models\Akademik;

use App\Models\User\SantriProfile;
use illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User\GuruProfile; // Asumsi Model GuruProfile ada
use App\Models\Akademik\Mapel;
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
        'nilai',
        'uas',
        'uts',
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
    
    public function santriProfile()
        {
            return $this->belongsTo(SantriProfile::class, 'santri_profile_id');
        }
    
        public function mapel()
        {
            return $this->belongsTo(Mapel::class, 'mapel_id');
        }
}
