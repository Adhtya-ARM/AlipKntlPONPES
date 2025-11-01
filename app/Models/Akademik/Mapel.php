<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\User\GuruProfile;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Penilaian;

class Mapel extends Model
{
    use HasFactory;

    protected $table = "mapel";

    protected $fillable = ["nama_mapel"];

     public function guruMapels()
     {
         return $this->HasMany(GuruMapel::class, 'mapel_id');
     }
     
     public function guruProfiles()
         {
             return $this->BelongsToMany(GuruProfile::class,  'guru_mapel', 'mapel_id', 'guru_profile_id' )->withTimestamps();
         }
     
     public function penilaians()
     {
         return $this->HasMany(Penilaian::class, 'mapel_id');
     }
}
