<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use App\Models\Akademik\AbsensiDetail;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\RencanaPembelajaran;
class AbsensiHeader extends Model

{
    protected $table = ['absensi_header'];
    protected $fillable = ['guru_mapel_id', 'rencana_pembelajaran_id', 'pertemuan_ke', 'tanggal_absensi', 'keterangan'];

    public function guruMapel() {
        return $this->belongsTo(GuruMapel::class);
    }

    public function rencanaPembelajaran() {
        return $this->belongsTo(RencanaPembelajaran::class);
    }

    public function details() {
        return $this->hasMany(AbsensiDetail::class, 'absensi_header_id');
    }
}
