<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Models\Akademik\Mapel;
use App\Models\User\GuruProfile;
/**
 * Model untuk tabel pivot 'guru_mapel'.
 * Menghubungkan GuruProfile dengan Mapel yang dia ajar.
 */
class GuruMapel extends Model
{
    use HasFactory;
    
    // Nama tabel di database Anda
    protected $table = 'guru_mapel';

    // Kolom-kolom yang boleh diisi (mass assignable)
    protected $fillable = [
        'guru_profile_id',
        'mapel_id',
    ];

    // Karena ini adalah tabel pivot, secara default ia tidak memiliki timestamps.
    // Jika tabel Anda memiliki kolom created_at dan updated_at, atur ini menjadi true.
    public $timestamps = false; // Ubah menjadi true jika tabel Anda memiliki timestamps

    // Relasi (Relationships)

    /**
     * Relasi ke GuruProfile (Banyak GuruMapel dimiliki oleh satu GuruProfile).
     */
     public function mapel()
     {
         return $this->belongsTo(Mapel::class, 'mapel_id');
     }
     
     public function guruProfile()
     {
         return $this->belongsTo(GuruProfile::class, 'guru_profile_id');
     }
}