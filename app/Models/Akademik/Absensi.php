<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensis';
    protected $fillable = ['santri_profile_id', 'kelas_id', 'mapel_id', 'tanggal', 'status', 'keterangan'];

    public function santriProfile()
    {
        return $this->belongsTo(\App\Models\User\SantriProfile::class, 'santri_profile_id');
    }

    public function kelas()
    {
        return $this->belongsTo(\App\Models\Akademik\Kelas::class, 'kelas_id');
    }
}
