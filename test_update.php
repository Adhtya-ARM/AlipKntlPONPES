<?php

use App\Models\Akademik\Kelas;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$kelas = Kelas::first();
echo "Old Name: " . $kelas->nama_unik . "\n";
$kelas->update(['nama_unik' => 'TEST_UPDATE_' . rand(1, 100)]);
echo "New Name: " . $kelas->fresh()->nama_unik . "\n";
