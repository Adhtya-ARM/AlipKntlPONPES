<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RencanaPembelajaran extends Model
{
    use HasFactory;

    protected $table = 'rencana_pembelajaran';

    protected $fillable = [
        'from_date',
        'to_date',
        'jenis',
        'judul',
        'catatan',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    // scope untuk bulan tertentu (YYYY-MM)
    public function scopeForMonth($query, string $yearMonth)
    {
        // yearMonth format "YYYY-MM"
        [$y, $m] = explode('-', $yearMonth);
        $start = \Carbon\Carbon::createFromDate($y, $m, 1)->startOfDay();
        $end = $start->copy()->endOfMonth();

        // ambil entri yang intersect bulan tersebut
        return $query->where(function($q) use ($start, $end) {
            $q->whereBetween('from_date', [$start, $end])
              ->orWhereBetween('to_date', [$start, $end])
              ->orWhere(function($qq) use ($start, $end) {
                  $qq->where('from_date', '<=', $start)
                     ->where('to_date', '>=', $end);
              });
        });
    }
}