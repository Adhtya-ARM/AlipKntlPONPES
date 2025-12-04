<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    use HasFactory;

    protected $table = 'tahun_ajarans';

    protected $fillable = [
        'nama',
        'semester',
        'jenjang',
        'is_active',
        'status',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Scope to get active year
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope to get only non-archived semesters
    public function scopeNotArchived($query)
    {
        return $query->where('status', '!=', 'Terarsip');
    }

    // Scope to get only archived semesters
    public function scopeArchived($query)
    {
        return $query->where('status', 'Terarsip');
    }

    // Check if semester is archived (read-only)
    public function isArchived(): bool
    {
        return $this->status === 'Terarsip';
    }

    // Relationship: Semester has many GuruMapel assignments
    public function guruMapels()
    {
        return $this->hasMany(\App\Models\Akademik\GuruMapel::class, 'tahun_ajaran_id');
    }
}
