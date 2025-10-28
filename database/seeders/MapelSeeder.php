<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Akademik\Mapel;

class MapelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mapels = [
            [
                'nama_mapel' => 'Matematika',
                'semester' => 'ganjil',
                'tahun_ajaran' => 2024,
                'kelas' => 7,
            ],
            [
                'nama_mapel' => 'Bahasa Indonesia',
                'semester' => 'ganjil',
                'tahun_ajaran' => 2024,
                'kelas' => 7,
            ],
            [
                'nama_mapel' => 'IPA',
                'semester' => 'ganjil',
                'tahun_ajaran' => 2024,
                'kelas' => 7,
            ],
            [
                'nama_mapel' => 'IPS',
                'semester' => 'ganjil',
                'tahun_ajaran' => 2024,
                'kelas' => 7,
            ],
        ];

        foreach ($mapels as $mapel) {
            Mapel::create($mapel);
        }
    }
}