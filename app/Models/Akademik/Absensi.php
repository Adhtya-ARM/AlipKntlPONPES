<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User\GuruProfile;
use App\Models\Akademik\Mapel;

class Absensi extends Model
{
    use HasFactory;

    protected $table = 'absensi';

    protected $fillable = [
        'guru_profile_id',
        'mapel_id',
        'jumlah_pertemuan',
        'jumlah_bab',
        'keterangan'
    ];

    /**
     * Relasi ke GuruProfile
     */
    public function guruProfile()
    {
        return $this->belongsTo(GuruProfile::class, 'guru_profile_id');
    }

    /**
     * Relasi ke Mapel
     */
    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }
}

