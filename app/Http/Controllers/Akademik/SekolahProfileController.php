<?php

namespace App\Http\Controllers\Akademik;

use App\Http\Controllers\Controller;
use App\Models\Akademik\SekolahProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SekolahProfileController extends Controller
{
    public function index()
    {
        $sekolah = SekolahProfile::first();
        if (!$sekolah) {
            $sekolah = new SekolahProfile();
        }
        return view('Akademik.Sekolah.index', compact('sekolah'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'visi' => 'nullable|string',
            'misi' => 'nullable|string',
            'alamat' => 'nullable|string',
            'email' => 'nullable|email',
            'telepon' => 'nullable|string',
            'website' => 'nullable|url',
            'maps_embed_url' => 'nullable|string',
        ]);

        $sekolah = SekolahProfile::first();
        if (!$sekolah) {
            $sekolah = new SekolahProfile();
        }

        $data = $request->except('logo');

        // If user pasted a full iframe tag in maps_embed_url, try to extract the src attribute
        if (!empty($data['maps_embed_url'])) {
            $maps = $data['maps_embed_url'];
            // If iframe tag present, extract src
            if (stripos($maps, '<iframe') !== false) {
                if (preg_match('/src=["\']([^"\']+)["\']/', $maps, $m)) {
                    $data['maps_embed_url'] = $m[1];
                } else {
                    // fallback: keep original
                    $data['maps_embed_url'] = $maps;
                }
            } else {
                $data['maps_embed_url'] = $maps;
            }
        }

        if ($request->hasFile('logo')) {
            if ($sekolah->logo) {
                Storage::disk('public')->delete($sekolah->logo);
            }
            $data['logo'] = $request->file('logo')->store('sekolah', 'public');
        }

        $sekolah->fill($data);
        $sekolah->save();

        return redirect()->back()->with('success', 'Profil sekolah berhasil diperbarui.');
    }
}
