<?php

namespace App\Models\User;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Traits\HasRoles;

class Santri extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    protected $table = "santris";

    protected $fillable = ["username", "password", "nis"];

    protected $hidden = ["password", "remember_token"];

    public function getAuthIdentifierName(): string
    {
        return "nis";
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->nis;
    }

    protected function casts(): array
    {
        return [
            "password" => "hashed",
        ];
    }

    public function SantriProfile(): HasOne
    {
        return $this->hasOne(SantriProfile::class);
    }
}
