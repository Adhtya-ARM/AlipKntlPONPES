<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User\Santri;
use App\Models\Akademik\Penilaian;
use App\Models\User\SantriKelas;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\GuruMapel;

class SantriProfile extends Model
{
    use HasFactory;

    protected $table = "santri_profile";
    protected $fillable = [
        "santri_id",
        "nama",
        "asrama",
        "alamat",
        "wali",
        "kamar",
        "status",
    ];

    public function mapelGuru()
        {
            return $this->belongsToMany(GuruMapel::class,  'santri_mapel', 'santri_profile_id',  'guru_mapel_id')->withTimestamps();
        }
    
    public function santri()
    {
        return $this->BelongsTo(Santri::class, "santri_id");
    }

    public function penilaian()
    {
        return $this->HasMany(Penilaian::class, "santri_profile_id");
    }

    public function santriKelas()
    {
        // Relasi Many-to-Many ke Kelas melalui tabel pivot santri_kelas
        return $this->HasMany(SantriKelas::class, "santri_profile_id");
    }
    
    public function kelasAktif()
    {
        return $this->HasOne(SantriKelas::class, 'santri_profile_id', 'id');
    }
    
    public function kelas()
       {
           return $this->BelongsTo(Kelas::class, 'kelas_id');
       }
}
