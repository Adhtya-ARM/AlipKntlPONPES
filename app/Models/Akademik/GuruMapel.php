<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

use App\Models\Akademik\Mapel;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\RencanaPembelajaran;
use App\Models\Akademik\Penilaian;
use App\Models\User\GuruProfile;
use App\Models\User\SantriProfile;

class GuruMapel extends Model
{
    use HasFactory;

    protected $table = 'guru_mapel';

    protected $fillable = [
        'guru_profile_id',
        'mapel_id',
        'kelas_id',
        'semester',
        'tahun_ajaran',
    ];

<<<<<<< HEAD
    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function rencanaPembelajaran(): HasOne
    {
        return $this->hasOne(RencanaPembelajaran::class, 'guru_mapel_id');
    }

    public function absensiHeaders(): HasMany
    {
        return $this->hasMany(AbsensiHeader::class, 'guru_mapel_id');
    }

    public function penilaians(): HasMany
    {
        return $this->hasMany(Penilaian::class, 'guru_mapel_id');
    }

    public function santriMapel(): HasMany
    {
        return $this->hasMany(\App\Models\Akademik\SantriMapel::class, 'guru_mapel_id');
    }
}
=======
    // Karena ini adalah tabel pivot, secara default ia tidak memiliki timestamps.
    // Jika tabel Anda memiliki kolom created_at dan updated_at, atur ini menjadi true.
    public $timestamps = false; // Ubah menjadi true jika tabel Anda memiliki timestamps

    /**
     * Relasi ke Absensi
     */
    public function absensi()
    {
        return $this->hasMany(\App\Models\Akademik\Absensi::class, 'guru_profile_id', 'guru_profile_id')
                    ->whereColumn('mapel_id', 'guru_mapel.mapel_id');
    }

    // Relasi (Relationships)

    /**
     * Relasi ke GuruProfile (Banyak GuruMapel dimiliki oleh satu GuruProfile).
     */
     public function mapel()
     {
         return $this->belongsTo(Mapel::class, 'mapel_id');
     }
     
     public function guruProfile()
     {
         return $this->belongsTo(GuruProfile::class, 'guru_profile_id');
     }
}
>>>>>>> f050ae17c144e6079ae8b8ec27ed5f44f35675f6
