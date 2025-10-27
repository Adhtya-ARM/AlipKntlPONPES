<?php

// HANYA BOLEH ADA SATU NAMESPACE PER FILE.
// Pilih salah satu namespace yang sesuai dengan lokasi file Anda.

// Jika file Anda berada di app/Models/User/SantriProfile.php
namespace App\Models\User; 

// ATAU Jika file Anda berada di app/Models/SantriProfile.php
// namespace App\Models; 

use illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Import kelas-kelas yang direlasikan
use App\Models\User\Santri; // Sesuaikan jika Model Santri ada di namespace yang berbeda
use App\Models\Akademik\Penilaian; // Sesuaikan path ini dengan lokasi Model Penilaian Anda

class SantriProfile extends Model
{
    use HasFactory;

    protected $table = 'santri_profile';
    protected $fillable = [
        "santri_id",
        "nama",
        "asrama",
        "alamat",
        "wali",
        "kelas",
        "kamar",
        "status",
    ];
    
    // Matikan timestamps jika Anda tidak menggunakan kolom created_at dan updated_at
    // public $timestamps = false; 

    // 1. Relasi Inverse ONE-TO-ONE ke Model Autentikasi Santri
    // SantriProfile dimiliki oleh satu Santri (Auth User).
    public function santri()
    {
        // Asumsi foreign key di tabel 'santri_profile' adalah 'santri_id'.
        return $this->belongsTo(Santri::class, 'santri_id'); 
    }
    
    // 2. Relasi ONE-TO-MANY ke Penilaian
    // SATU Santri Profile memiliki BANYAK Penilaian.
    // Jika Penilaian memiliki foreign key 'santri_profile_id', gunakan hasMany.
    public function penilaian() // PENTING: Gunakan bentuk jamak (plural)
    {
        // Asumsi foreign key di tabel 'penilaian' adalah 'santri_profile_id'.
        return $this->hasMany(Penilaian::class, 'santri_profile_id');
    }
}