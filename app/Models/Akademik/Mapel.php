<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User\GuruProfile;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Penilaian;

class Mapel extends Model
{
    use HasFactory;

    protected $table = "mapel"; // Pastikan nama tabel benar, jika Anda menggunakan snake_case 'mata_pelajaran' ganti di sini

    protected $fillable = ["nama_mapel", "semester", "tahun_ajaran", "kelas"];

    /**
     * Relasi One-to-Many: Satu Mata Pelajaran memiliki banyak Penilaian.
     */
     public function guruMapel()
     {
         // Relasi Mapel ke GuruMapel (One-to-Many)
         return $this->hasMany(GuruMapel::class, 'mapel_id');
     }
     
     public function guruProfiles()
         {
             return $this->belongsToMany(
                 \App\Models\User\GuruProfile::class,
                 'guru_mapel',
                 'mapel_id',
                 'guru_profile_id'
             )->withTimestamps();
         }
     
     public function penilaians()
     {
         // Relasi Mapel ke Penilaian (One-to-Many)
         return $this->hasMany(Penilaian::class, 'mapel_id');
     }
}
