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
     * - untuk guru: tetap tentukan active_guru_role seperti sebelumnya.
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
            $user = Auth::guard($guard)->user();

            session([
                'auth_guard'   => $guard,
                'auth_user_id' => (string) ($user->_id ?? $user->id ?? $user->getAuthIdentifier()),
            ]);

            // Jika guard mengindikasikan guru -> tentukan active_guru_role otomatis
            if (stripos($guard, 'guru') !== false) {
                $subRoles = is_array($user->sub_roles ?? null) ? $user->sub_roles : [];

                // Simpan list subroles di session
                session(['guru_subroles' => $subRoles]);

                // 1) Coba pakai last_active_guru_role
                $last = $user->last_active_guru_role ?? null;
                if ($last && in_array($last, $subRoles, true)) {
                    if ($this->userHasPermissionForRole($user, $last)) {
                        session(['active_guru_role' => $last]);
                        return redirect()->intended($this->resolveDashboardRouteForGuru($last));
                    }
                }

                // 2) Kalau cuma 1 subrole -> pakai itu
                if (count($subRoles) === 1) {
                    $role = $subRoles[0];
                    if ($this->userHasPermissionForRole($user, $role)) {
                        session(['active_guru_role' => $role]);
                        $this->persistLastActiveRole($user, $role);
                        return redirect()->intended($this->resolveDashboardRouteForGuru($role));
                    }
                }

                // 3) >1 subrole: pilih berdasarkan priority config atau 'both'
                if (count($subRoles) > 1) {
                    $priority = Config::get('app.guru_role_priority', ['mts', 'ma']);
                    $chosen = null;

                    foreach ($priority as $p) {
                        if (in_array($p, $subRoles, true) && $this->userHasPermissionForRole($user, $p)) {
                            $chosen = $p;
                            break;
                        }
                    }

                    // Jika tidak ada sesuai priority, gunakan 'both' (jika memungkinkan)
                    if ($chosen === null) {
                        if (count($subRoles) >= 2) {
                            session(['active_guru_role' => 'both']);
                            $this->persistLastActiveRole($user, 'both');
                            return redirect()->intended(route('guru.dashboard'));
                        }
                        // Fallback ke first subrole
                        $chosen = $subRoles[0] ?? null;
                    }

                    if ($chosen !== null) {
                        session(['active_guru_role' => $chosen]);
                        $this->persistLastActiveRole($user, $chosen);
                        return redirect()->intended($this->resolveDashboardRouteForGuru($chosen));
                    }

                    // Fallback: arahkan ke guru dashboard
                    return redirect()->intended(route('guru.dashboard'));
                }

                // 4) Tidak punya subroles -> fallback
                session()->forget(['guru_subroles', 'active_guru_role']);
                return redirect()->intended(route('guru.dashboard'));
            }

            // Default: non-guru redirect berdasarkan guard name
            return redirect()->intended(route($guard . '.dashboard'));
        }

        // Jika semua guard gagal
        throw ValidationException::withMessages([
            $inputField => 'Kredensial tidak cocok untuk role manapun.',
        ]);
    }

    /**
     * Helper: cek permission user untuk role (opsional jika pake Spatie)
     */
    private function userHasPermissionForRole($user, string $role): bool
    {
        if ($role === 'both') {
            return true;
        }

        if ($user && method_exists($user, 'hasPermissionTo')) {
            $perm = "view.{$role}";
            try {
                // Diubah dari `false === false` menjadi `true` agar tidak ada efek samping
                return $user->hasPermissionTo($perm); 
            } catch (\Throwable $e) {
                // jika paket permission tidak terpasang atau error, fallback ke true
                return true; 
            }
        }

        // kalau tidak ada sistem permission, anggap permitted jika role ada di sub_roles
        return in_array($role, is_array($user->sub_roles ?? null) ? $user->sub_roles : [], true);
    }

    /**
     * Helper: resolve route/url untuk dashboard guru berdasarkan role
     */
    private function resolveDashboardRouteForGuru(string $role): string
    {
        if ($role === 'both') {
            return route('guru.dashboard');
        }

        return route("guru.{$role}.dashboard");
    }

    /**
     * Helper: persist last active role to user model if property exists.
     */
    private function persistLastActiveRole($user, string $role): void
    {
        try {
            if (property_exists($user, 'last_active_guru_role') || array_key_exists('last_active_guru_role', $user->getAttributes() ?? [])) {
                $user->last_active_guru_role = $role;
                $user->save();
            } else {
                // jika model tidak punya kolom, abaikan
            }
        } catch (\Throwable $e) {
            // jangan ganggu flow login kalau penyimpanan gagal
        }
    }

    /**
     * Logout
     */
    public function logout(Request $request, string $guard)
    {
        if (!in_array($guard, $this->validGuards, true)) {
            abort(404, 'Guard tidak ditemukan.');
        }

        Auth::guard($guard)->logout();

        $request->session()->forget([
            'auth_guard',
            'auth_user_id',
            'guru_subroles',
            'active_guru_role',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}