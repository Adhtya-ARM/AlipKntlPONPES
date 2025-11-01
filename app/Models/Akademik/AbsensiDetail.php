<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Relationships\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Akademik\AbsensiDetail;
use App\Models\User\SantriProfile;

class AbsensiDetail extends Model
{
    protected $table = ['absensi_detail'];
    protected $fillable = ['absensi_header_id', 'santri_profile_id', 'kehadiran', 'catatan'];

    public function header() {
        return $this->BelongsTo(AbsensiHeader::class, 'absensi_header_id');
    }

    public function santri() {
        return $this->BelongsTo(SantriProfile::class, 'santri_profile_id');
    }
}
