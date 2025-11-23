<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; 

use App\Models\User\GuruProfile;
use App\Models\User\SantriProfile;
use App\Models\User\SantriKelas;
use App\Models\Akademik\GuruMapel;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'level',
        'nama_unik',
        'guru_profile_id', // kalau ini masih digunakan
        'wali_kelas_id',
    ];
    
    protected function namaDisplay(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Kelas ' . $this->level . $this->nama_unik
        );
    }

    // 🔹 Relasi ke guru_mapel
    public function guruMapels()
    {
        return $this->HasMany(GuruMapel::class, 'kelas_id');
    }

    // 🔹 Relasi pivot ke tabel santri_kelas
    public function santriKelas()
    {
        return $this->HasMany(SantriKelas::class, 'kelas_id');
    }

    // 🔹 Ambil data santri dari pivot
    public function santriProfile()
      {
          return $this->BelongsToMany(SantriProfile::class, 'santri_kelas', 'kelas_id', 'santri_profile_id',)
              ->withPivot(['tahun_ajaran'])
              ->withTimestamps();
      }

    public function waliKelas()
    {
        return $this->BelongsTo(GuruProfile::class, 'wali_kelas_id');
    }
}
