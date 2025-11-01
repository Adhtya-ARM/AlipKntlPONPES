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
