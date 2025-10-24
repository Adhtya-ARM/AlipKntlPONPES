<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use illuminate\Database\Eloquent\Relations\BelongsTo;

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

    // 4. Define Inverse Relationship
    public function santri()
    {
        return $this->belongsTo(Santri::class);
    }
}
