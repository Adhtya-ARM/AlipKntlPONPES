<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;

class Penilaian extends Model
{
    protected $table = 'penilaians';
    protected $fillable = ['santri_profile_id', 'guru_mapel_id', 'tanggal', 'jenis_penilaian', 'nilai', 'keterangan'];

    public function santriProfile()
    {
        return $this->belongsTo(\App\Models\User\SantriProfile::class, 'santri_profile_id');
    }

    public function guruMapel()
    {
        return $this->belongsTo(\App\Models\Akademik\GuruMapel::class, 'guru_mapel_id');
    }
}
