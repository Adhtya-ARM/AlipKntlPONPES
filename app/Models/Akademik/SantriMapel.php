<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\User\SantriProfile;
use App\Models\Akademik\GuruMapel;

class SantriMapel extends Model
{
    use HasFactory;

    protected $table = 'santri_mapel';
    protected $fillable = ['guru_mapel_id', 'santri_profile_id'];

    public function guruMapel()
    {
        return $this->belongsTo(GuruMapel::class, 'guru_mapel_id');
    }

    public function santriProfile()
    {
        return $this->belongsTo(SantriProfile::class, 'santri_profile_id');
    }
}
