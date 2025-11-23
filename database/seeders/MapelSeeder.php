<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Akademik\Mapel;

class MapelSeeder extends Seeder
{
    public function run(): void
    {
        // Mapel SMP
        Mapel::create([
            'nama_mapel' => 'Bahasa Indonesia',
            'jjm' => 4,
            'tingkat' => ['7', '8', '9']
        ]);

        Mapel::create([
            'nama_mapel' => 'Matematika',
            'jjm' => 4,
            'tingkat' => ['7', '8', '9']
        ]);

        // Mapel SMA
        Mapel::create([
            'nama_mapel' => 'Bahasa Indonesia',
            'jjm' => 4,
            'tingkat' => ['10', '11', '12']
        ]);

        Mapel::create([
            'nama_mapel' => 'Matematika',
            'jjm' => 4,
            'tingkat' => ['10', '11', '12']
        ]);
    }
}
