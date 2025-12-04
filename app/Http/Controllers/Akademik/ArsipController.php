<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\TahunAjaran;
use Illuminate\Http\Request;

/**
 * ArsipController
 * 
 * NOTE: This controller handles the OLD archive system (separate arsip table for class snapshots).
 * The NEW semester archive system is handled in TahunAjaranController.
 * This controller is kept for backward compatibility with existing arsip views.
 */
class ArsipController extends Controller
{
    /**
     * Display archived classes (old system - separate table snapshots)
     */
    public function index()
    {
        // This view shows archived CLASS snapshots (from arsip table if it exists)
        // Different from archived SEMESTERS (tahun_ajarans with status='Terarsip')
        
        // For now, return empty data as this is the old system
        $arsipData = collect([]);
        
        return view('Akademik.Arsip.index', compact('arsipData'));
    }

    /**
     * Show archived class details
     */
    public function show($arsipId)
    {
        // Placeholder for old archive system
        return back()->with('info', 'Fitur arsip kelas (snapshot) belum diimplementasikan.');
    }

    /**
     * Create archive snapshot (old system - called by TahunAjaranController)
     * This is kept for backward compatibility but does nothing
     */
    public function createArchive($kelasId, $tahunAjaranId)
    {
        // Old archive system - creating class snapshots
        // Not used in new implementation
        \Log::info("createArchive called but skipped (old system) - kelas: {$kelasId}, tahun: {$tahunAjaranId}");
        return true;
    }

    /**
     * Create archive from form (old system)
     */
    public function createFromForm(Request $request)
    {
        // Placeholder - redirect to new archive system
        return redirect()->route('manajemen-sekolah.tahun-ajaran.index')
            ->with('info', 'Untuk mengarsipkan semester, gunakan tombol "Arsipkan" pada daftar Tahun Ajaran.');
    }
}
