<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException; // Penting untuk penanganan error

class AuthController extends Controller
{
    private $validGuards;

    public function __construct()
    {
        // Mengambil daftar semua guard yang menggunakan driver 'session' (web, santri, guru, wali)
        $allGuards = Config::get('auth.guards');
        $this->validGuards = array_keys(array_filter($allGuards, fn($g) => $g['driver'] === 'session'));
    }

    // [GET] Menampilkan Form Login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // [POST] Memproses Login Multi-Guard
    public function login(Request $request)
    {
        $inputField = 'username'; // Nama input field di form Anda (diisi NIS/Username)
        
        // 1. Validasi Input
        $credentials = $request->validate([
            $inputField => 'required|string',
            'password' => 'required|string',
        ]);
        
        $remember = $request->filled('remember');
        
        // 2. Iterasi dan Deteksi Role Otomatis
        foreach ($this->validGuards as $guard) {
            $dbLoginKey = match ($guard) {
                'santris' => 'nis',      // Guard 'santri' harus mencari kolom 'nis'
                default => 'username', // Guard lain (web, guru, wali) mencari kolom 'username'
            };

            // Membuat array kredensial yang disesuaikan
            $authCredentials = [
                $dbLoginKey => $credentials[$inputField], 
                'password' => $credentials['password'],
            ];

            // Coba otentikasi.
            if (Auth::guard($guard)->attempt($authCredentials, $remember)) {
                $request->session()->regenerate();

                return redirect()->intended(route($guard . '.dashboard'));
            }
        }
        
        // 3. Gagal Login (setelah mencoba SEMUA Guard)
        // Melempar exception agar error ditampilkan di form login
        throw ValidationException::withMessages([
            $inputField => 'Kredensial tidak cocok untuk role manapun.',
        ])->onlyInput($inputField);
    }

    // [POST] Memproses Logout Multi-Guard
    // Memerlukan parameter {guard} dari route untuk logout yang benar
    public function logout(Request $request, $guard)
    {
        if (!in_array($guard, $this->validGuards)) {
            // Seharusnya tidak pernah terjadi jika route didefinisikan dengan benar
            abort(404, 'Guard tidak ditemukan.'); 
        }
        
        Auth::guard($guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}