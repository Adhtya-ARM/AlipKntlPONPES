<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class Guru extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $table = "guru";
    protected $fillable = ["username", "password"];

    protected $hidden = ["password", "remember_token"];

    public function findForAuth($username)
    {
        return $this->where("username", $username)->first();
    }

    /**
     * Tentukan field username untuk autentikasi.
     */
    public function getAuthUsername()
    {
        return "username";
    }

    protected function casts(): array
    {
        return [
            "password" => "hashed",
        ];
    }

    public function GuruProfile()
    {
        return $this->hasOne(GuruProfile::class);
    }
}
