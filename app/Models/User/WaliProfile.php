<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Model;
use App\Models\User\SantriProfile;
use App\Modes\User\Wali;

class WaliProfile extends Model
{
    protected $table = "wali_profile";
    protected $fillable = ["wali_id", "nama", "no_hp", "alamat", "foto"];

    public function santri()
    {
        return $this->BelongsTo(Wali::class, "wali_id");
    }
    
    public function santriProfiles()
    {
        return $this->hasMany(SantriProfile::class, 'wali_profile_id');
    }
}