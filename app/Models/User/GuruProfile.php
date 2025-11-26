<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\Akademik\Mapel;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Penilaian;
use App\Models\Akademik\Kelas;

class GuruProfile extends Model
{
    use HasFactory;

    protected $table = "guru_profile";
    protected $fillable = ["guru_id", "nama", "jabatan", "alamat", "no_hp", "foto", "tampilkan_di_landing"];

    // 4. Define Inverse Relationship
    public function guru()
    {
        return $this->BelongsTo(Guru::class);
    }

    public function mapels()
    {
        return $this->BelongsToMany(
            Mapel::class,
            "guru_mapel",
            "guru_profile_id",
            "mapel_id",
        )->withTimestamps();
    }

    public function guruMapels()
    {
        return $this->HasMany(GuruMapel::class, "guru_profile_id");
    }

    /**
     * Relasi One-to-Many: Guru Profile mengisi banyak Penilaian.
     */
    public function penilaians()
    {
        return $this->HasMany(Penilaian::class, "guru_profile_id");
    }

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'guru_profile_id');
    }
}
