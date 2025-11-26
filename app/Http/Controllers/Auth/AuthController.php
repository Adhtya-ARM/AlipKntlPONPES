<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\RedirectResponse;

class AuthController extends Controller
{
    private array $validGuards;

    public function __construct()
    {
        // Hanya ambil guards yang menggunakan driver 'session'
        $allGuards = Config::get('auth.guards', []);
        $this->validGuards = array_keys(
            array_filter(
                $allGuards,
                fn($g) => isset($g['driver']) && $g['driver'] === 'session'
            )
        );
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Login handler:
     * - authenticate across configured session guards.
     * - **SEMUA LOGIKA PENGECEKAN STATUS AKUN DINONAKTIFKAN.**
     * - Tidak ada penentuan active_guru_role (semua logika sub-role dihapus).
     */
    public function login(Request $request)
    {
        $inputField = 'username';
        $data = $request->validate([
            $inputField => 'required|string',
            'password'  => 'required|string',
        ]);

        $remember = $request->filled('remember');

        // Prioritaskan cek Santri dulu (jika ada di validGuards)
        // Sesuai request: "jika user memiliki nisn meskipun username sama akan diarahkan ke santri"
        $orderedGuards = $this->validGuards;
        usort($orderedGuards, function ($a, $b) {
            if ($a === 'santri') return -1;
            if ($b === 'santri') return 1;
            return 0;
        });

        foreach ($orderedGuards as $guard) {
            // dd(Auth::guard($guard)->getProvider()->getModel());

 

            // Tentukan field login DB berdasarkan guard
            $dbLoginKey = stripos($guard, 'santri') !== false ? 'nisn' : 'username';

            $credentials = [
                $dbLoginKey => $data[$inputField],
                'password'  => $data['password'],
            ];

            if (!Auth::guard($guard)->attempt($credentials, $remember)) {
                continue; // Coba guard berikutnya
            }

            // Berhasil login
            $request->session()->regenerate();
            // User sudah di-attach ke session oleh Auth::attempt

            // Default: redirect berdasarkan guard name
            // Di sini, kita asumsikan semua guard memiliki route dashboard yang terdefinisi
            return redirect()->intended(route($guard . '.dashboard'));
        }

        // Jika semua guard gagal
        throw ValidationException::withMessages([
            $inputField => 'Kredensial tidak cocok untuk role manapun.',
        ]);
    }

    /**
     * Logout
     * Menghapus guard dan session terkait.
     */
    public function logout(Request $request, string $guard)
    {
        if (!in_array($guard, $this->validGuards, true)) {
            abort(404, 'Guard tidak ditemukan.');
        }

        Auth::guard($guard)->logout();

        // Menghapus semua session terkait otentikasi (sesuai permintaan, menghilangkan guru_subroles dan active_guru_role, serta auth_guard dan auth_user_id)
        $request->session()->forget([
            'auth_guard',
            'active_guru_role',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // Semua fungsi helper terkait sub-role (userHasPermissionForRole, resolveDashboardRouteForGuru, persistLastActiveRole) dihapus.
    // Jika Anda ingin mengembalikan helper tersebut, tambahkan kembali di sini.
}