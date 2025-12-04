<?php
namespace App\Models\User;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User\Santri;
use App\Models\Akademik\PenilaianDetail;
use App\Models\User\SantriKelas;
use App\Models\User\WaliProfile;

use App\Models\Akademik\Kelas;
use App\Models\Akademik\GuruMapel;

class SantriProfile extends Model
{
    use HasFactory;

    protected $table = "santri_profile";
    protected $fillable = [
        "santri_id",
        "nama",
        "jenjang",
        "no_hp",
        "wali_profile_id",
        "alamat",
        "status",
        "foto",
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
        return $this->HasOne(SantriKelas::class, "santri_profile_id");
    }
    
    public function kelasAktif()
    {
        return $this->hasOne(SantriKelas::class, 'santri_profile_id')
            ->whereHas('tahunAjaran', function($q) {
                $q->where('is_active', true);
            })
            ->where('status', 'Aktif');
    }
    
    public function riwayatKelas()
    {
        return $this->hasMany(SantriKelas::class, 'santri_profile_id')->orderBy('id', 'desc');
    }

    // Deprecated: kelas_id does not exist on santri_profile
    // public function kelas() { ... }
       
       public function waliProfile()
          {
              return $this->BelongsTo(WaliProfile::class, 'wali_profile_id');
          }
       
       public function penilaians()
       {
           return $this->hasMany(\App\Models\Akademik\Penilaian::class, 'santri_profile_id');
       }
       
       public function absensis()
       {
           return $this->hasMany(\App\Models\Akademik\Absensi::class, 'santri_profile_id');
       }
       
}
