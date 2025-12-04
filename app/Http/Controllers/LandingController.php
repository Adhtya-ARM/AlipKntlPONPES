<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LandingController extends Controller
{
    public function index()
    {
        $struktur = \App\Models\User\GuruProfile::where('tampilkan_di_landing', true)
            ->whereRaw('LOWER(jabatan) != ?', ['guru'])
            ->whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->orderBy('nama')
            ->get();

        $sekolah = \App\Models\Akademik\SekolahProfile::first();

        return view('Landing Page.index', compact('struktur', 'sekolah'));
    }
}
