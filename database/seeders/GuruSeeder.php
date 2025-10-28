<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User\Guru;
use App\Models\User\GuruProfile;
use App\Models\Akademik\GuruMapel;

class GuruSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Guru
        $guru = Guru::create([
            'username' => 'guru1',
            'password' => bcrypt('password'),
        ]);

        // Create GuruProfile
        $guruProfile = GuruProfile::create([
            'guru_id' => $guru->id,
            'nama' => 'Guru Matematika',
            'jabatan' => 'Guru',
            'alamat' => 'Jl. Guru No. 1',
            'no_hp' => '081234567890',
        ]);

        // Assign mapels to guru (first 2 mapels)
        $mapelIds = [1, 2]; // Matematika and Bahasa Indonesia
        foreach ($mapelIds as $mapelId) {
            GuruMapel::create([
                'guru_profile_id' => $guruProfile->id,
                'mapel_id' => $mapelId,
            ]);
        }
    }
}