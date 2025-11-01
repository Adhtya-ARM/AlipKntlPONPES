<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relationship\Hasmany;
use Illuminate\Database\Eloquent\Relationship\BelongsTo;

use App\Models\User\GuruMapel;
use App\Models\Akademik\AbsensiHeader;

class RencanaPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'rencana_pembelajaran';

    protected $fillable = [
        'guru_mapel_id',
        'jumlah_pertemuan',
        'jumlah_bab',
    ];
    
    public function guruMapel()
    {
        return $this->BelongsTo(GuruMapel::class, 'guru_mapel_id');
    }

    public function absensiHeaders()
        {
            return $this->HasMany(AbsensiHeader::class, 'rencana_pembelajaran_id');
        }
}

