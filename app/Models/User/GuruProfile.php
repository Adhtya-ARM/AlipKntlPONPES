<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use illuminate\Database\Eloquent\Relations\BelongsTo;
use illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Akademik\Mapel;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Penilaian;

class GuruProfile extends Model
{
    use HasFactory;

    protected $table = "guru_profile";
    protected $fillable = ["guru_id", "nama", "jabatan", "alamat", "no_hp"];

    // 4. Define Inverse Relationship
    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mapels()
        {
            return $this->belongsToMany(
                \App\Models\Akademik\Mapel::class,
                'guru_mapel',
                'guru_profile_id',
                'mapel_id'
            )->withTimestamps();
        }
    
    public function guruMapels()
        {
            return $this->hasMany(GuruMapel::class, 'guru_profile_id');
        }

    /**
     * Relasi One-to-Many: Guru Profile mengisi banyak Penilaian.
     */
    public function penilaians()
    {
        return $this->hasMany(Penilaian::class, "guru_profile_id");
    }
}
