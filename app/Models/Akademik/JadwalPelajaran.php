<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Akademik\GuruMapel;
use App\Models\Akademik\Kelas;
use App\Models\Akademik\Mapel;
use App\Models\User\GuruProfile;

class JadwalPelajaran extends Model
{
    use HasFactory;

    protected $table = 'jadwal_pelajaran';

    protected $fillable = [
        'guru_mapel_id',
        'kelas_id',
        'mapel_id',
        'guru_profile_id',
        'hari',
        'jam_ke',
        'jam_mulai',
        'jam_selesai',
        'semester',
        'tahun_ajaran',
    ];

    protected $casts = [
        'jam_mulai' => 'datetime:H:i',
        'jam_selesai' => 'datetime:H:i',
    ];

    /**
     * Relasi ke GuruMapel
     */
    public function guruMapel(): BelongsTo
    {
        return $this->belongsTo(GuruMapel::class, 'guru_mapel_id');
    }

    /**
     * Relasi ke Kelas
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    /**
     * Relasi ke Mapel
     */
    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    /**
     * Relasi ke GuruProfile
     */
    public function guruProfile(): BelongsTo
    {
        return $this->belongsTo(GuruProfile::class, 'guru_profile_id');
    }

    /**
     * Scope untuk filter berdasarkan hari
     */
    public function scopeHari($query, $hari)
    {
        return $query->where('hari', $hari);
    }

    /**
     * Scope untuk filter berdasarkan guru
     */
    public function scopeGuru($query, $guruProfileId)
    {
        return $query->where('guru_profile_id', $guruProfileId);
    }

    /**
     * Scope untuk jadwal hari ini
     */
    public function scopeToday($query)
    {
        $hari = $this->getNamaHariIndonesia();
        return $query->where('hari', $hari);
    }

    /**
     * Helper: Convert day name ke Bahasa Indonesia
     */
    protected function getNamaHariIndonesia()
    {
        $days = [
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
            'Sunday' => 'Minggu'
        ];
        
        return $days[now()->format('l')] ?? 'Senin';
    }

    /**
     * Accessor: Format waktu untuk display
     */
    public function getWaktuAttribute()
    {
        return date('H:i', strtotime($this->jam_mulai)) . ' - ' . date('H:i', strtotime($this->jam_selesai));
    }
}
