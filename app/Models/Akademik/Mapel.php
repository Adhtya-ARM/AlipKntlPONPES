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
    public function penilaians()
    {
        return $this->hasMany(Penilaian::class, "mapel_id");
    }

    /**
     * Relasi Many-to-Many: Mata Pelajaran diajar oleh banyak Guru (User).
     */
    public function gurus()
    {
        return $this->belongsToMany(
            GuruProfile::class,
            "guru_mapel",
            "mapel_id",
            "guru_profile_id",
        );
    }
    
    public function guruMapels()
        {
            return $this->hasMany(GuruMapel::class, 'mapel_id');
        }
}
