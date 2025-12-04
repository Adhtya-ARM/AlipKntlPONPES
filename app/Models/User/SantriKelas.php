<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

use App\Models\User\SantriProfile;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\TahunAjaran;

class SantriKelas extends Model
{
    use HasFactory;
    protected $table = "santri_kelas";
    protected $fillable = ["santri_profile_id", "kelas_id", "tahun_ajaran_id", "status"];

    public function santriProfile()
    {
        return $this->BelongsTo(SantriProfile::class, "santri_profile_id");
    }

    public function kelas()
    {
        return $this->BelongsTo(Kelas::class, "kelas_id");
    }

    public function tahunAjaran()
    {
        return $this->BelongsTo(TahunAjaran::class, "tahun_ajaran_id");
    }
}
