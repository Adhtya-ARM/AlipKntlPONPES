<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    private $validGuards;

    public function __construct()
    {
        // Ambil semua guard berbasis sesi: 'web', 'santri', 'wali', 'guru'
        $allGuards = Config::get('auth.guards');
        $this->validGuards = array_keys(array_filter($allGuards, fn($g) => $g['driver'] === 'session'));
    }

    public function showLoginForm()
    {
        // View login sederhana, tanpa perlu input guard
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $loginField = 'username';
        
        // 1. Validasi Input
        $credentials = $request->validate([
            $loginField => 'required|string',
            'password' => 'required|string',
        ]);
        
        $remember = $request->filled('remember');
        
        // 2. Iterasi dan Deteksi Role Otomatis
        foreach ($this->validGuards as $guard) {
            // Coba login menggunakan guard saat ini
            if (Auth::guard($guard)->attempt($credentials, $remember)) {
                $request->session()->regenerate();

                // Jika berhasil, redirect ke dashboard guard tersebut
                // Contoh: Jika login berhasil di guard 'guru', redirect ke /guru/dashboard
                return redirect("/{$guard}/dashboard");
            }
        }
        
        // 3. Gagal Login (setelah mencoba SEMUA Guard)
        return back()->withErrors([
            $loginField => 'Username atau Password tidak cocok untuk role manapun.',
        ])->onlyInput($loginField);
    }

    // Metode Logout tetap memerlukan parameter untuk mengakhiri sesi yang benar
    public function logout(Request $request, $guard)
    {
        if (!in_array($guard, $this->validGuards)) {
            abort(404, 'Guard tidak ditemukan.');
        }
        
        Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}