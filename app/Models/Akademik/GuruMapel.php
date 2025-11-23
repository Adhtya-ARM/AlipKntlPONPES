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

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    // Removed rencanaPembelajaran relationship - rencana_pembelajaran table doesn't have guru_mapel_id column
    // Removed absensiHeaders relationship - AbsensiHeader model deleted

    public function penilaians()
    {
        return $this->hasMany(Penilaian::class, 'guru_mapel_id');
    }

    public function santriMapel()
    {
        return $this->hasMany(SantriMapel::class, 'guru_mapel_id');
    }

    // Tabel ini memiliki timestamps sesuai migration
    public $timestamps = true;

    /**
     * Relasi ke Absensi
     */
    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'guru_profile_id', 'guru_profile_id')
                    ->whereColumn('mapel_id', 'guru_mapel.mapel_id');
    }
     
     public function guruProfile()
     {
         return $this->belongsTo(GuruProfile::class, 'guru_profile_id');
     }
}

