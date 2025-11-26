<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use App\Models\User\GuruProfile;
use App\Models\User\SantriProfile;
use App\Models\User\WaliProfile;

class ProfileController extends Controller
{
    public function index()
    {
        $user = null;
        $guard = '';
        $profile = null;

        if (Auth::guard('guru')->check()) {
            $user = Auth::guard('guru')->user();
            $guard = 'guru';
            $user->load('guruProfile'); // Force fresh load
            $profile = $user->guruProfile;
        } elseif (Auth::guard('santri')->check()) {
            $user = Auth::guard('santri')->user();
            $guard = 'santri';
            $user->load('santriProfile'); // Force fresh load
            $profile = $user->santriProfile;
        } elseif (Auth::guard('wali')->check()) {
            $user = Auth::guard('wali')->user();
            $guard = 'wali';
            $user->load('waliProfile'); // Force fresh load
            $profile = $user->waliProfile;
        }

        return view('User.Profile.index', compact('user', 'guard', 'profile'));
    }

    public function update(Request $request)
    {
        $user = null;
        $guard = '';
        $profile = null;

        if (Auth::guard('guru')->check()) {
            $user = Auth::guard('guru')->user();
            $guard = 'guru';
            $profile = $user->guruProfile;
        } elseif (Auth::guard('santri')->check()) {
            $user = Auth::guard('santri')->user();
            $guard = 'santri';
            $profile = $user->santriProfile;
        } elseif (Auth::guard('wali')->check()) {
            $user = Auth::guard('wali')->user();
            $guard = 'wali';
            $profile = $user->waliProfile;
        }

        if (!$user) {
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $rules = [
            'password' => 'nullable|min:6|confirmed',
            'foto' => 'nullable|image|max:2048', // 2MB Max
        ];

        if ($guard === 'guru') {
             $rules['nama'] = 'required|string|max:255';
             $rules['alamat'] = 'nullable|string';
             $rules['no_hp'] = 'nullable|string|max:20';
        } elseif ($guard === 'santri') {
             $rules['nama'] = 'required|string|max:255';
             $rules['alamat'] = 'nullable|string';
             $rules['no_hp'] = 'nullable|string|max:20';
        } elseif ($guard === 'wali') {
             $rules['nama'] = 'required|string|max:255';
             $rules['alamat'] = 'nullable|string';
             $rules['no_hp'] = 'nullable|string|max:20';
        }

        $validated = $request->validate($rules);

        // Update Password
        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
            $user->save();
        }

        // Update Profile Data
        if ($profile) {
            // Handle Photo Upload First
            if ($request->hasFile('foto')) {
                // Delete old photo if exists
                if ($profile->foto && Storage::exists('public/' . $profile->foto)) {
                    Storage::delete('public/' . $profile->foto);
                }
                
                $path = $request->file('foto')->store('profile-photos', 'public');
                $profile->foto = $path;
            }

            // Update other fields
            $profile->nama = $validated['nama'];
            $profile->alamat = $validated['alamat'];
            $profile->no_hp = $validated['no_hp'];
            $profile->save();
        }

        return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui.');
    }
}
