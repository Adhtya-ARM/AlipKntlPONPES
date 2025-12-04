<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SekolahProfile extends Model
{
    use HasFactory;

    protected $table = 'sekolah_profiles';

    protected $fillable = [
        'nama_sekolah',
        'logo',
        'visi',
        'misi',
        'alamat',
        'email',
        'telepon',
        'website',
        'maps_embed_url',
    ];
}
