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
use App\Models\Akademik\JadwalPelajaran;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'level',
        'guru_profile_id',
        'status',
    ];
    
    protected function namaDisplay(): Attribute
    {
        return Attribute::make(
            get: fn() => 'Kelas ' . $this->level 
        );
    }

    protected function jenjang(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->level <= 9 ? 'SMP' : 'SMA'
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
            ->withPivot(['tahun_ajaran_id', 'status'])
            ->withTimestamps();
    }

    public function waliKelas()
    {
        return $this->belongsTo(GuruProfile::class, 'guru_profile_id');
    }

    public function jadwalPelajaran()
    {
        return $this->hasMany(JadwalPelajaran::class, 'kelas_id');
    }
}
