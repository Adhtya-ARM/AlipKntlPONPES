<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\RencanaPembelajaran;
use App\Models\User\SantriProfile;

class Penilaian extends Model
{
    protected $table = ['penilaian'];
    protected $fillable = [
        'guru_mapel_id', 'rencana_pembelajaran_id',
        'santri_profile_id', 'bab_ke', 'semester',
        'nilai', 'keterangan'
    ];

    public function guruMapel() {
        return $this->BelongsTo(GuruMapel::class);
    }

    public function rencanaPembelajaran() {
        return $this->BelongsTo(RencanaPembelajaran::class);
    }

    public function santri() {
        return $this->BelongsTo(SantriProfile::class, 'santri_profile_id');
    }
}
