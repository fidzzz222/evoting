<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // form login admin
    public function showLoginForm()
    {
        return view('auth.admin-login');
    }

    // proses login admin
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        // autentikasi akun admin / petugas
        if (Auth::guard('web')->attempt($credentials, $remember)) {
            // reset session id agar aman
            $request->session()->regenerate();
            $user = Auth::guard('web')->user();

            AuditLog::log('LOGIN_PANITIA', "Login berhasil sebagai {$user->role} ({$user->nama})", [
                'role' => $user->role,
                'email' => $user->email,
            ], $user);

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', "Berhasil masuk sebagai {$user->nama}.");
        }

        return back()->withErrors([
            'email' => 'Email atau kata sandi tidak valid.',
        ])->onlyInput('email');
    }

    // logout admin
    public function logout(Request $request)
    {
        $user = Auth::guard('web')->user();
        if ($user) {
            AuditLog::log('LOGOUT_PANITIA', "Petugas {$user->nama} ({$user->role}) keluar dari sistem", null, $user);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('success', 'Berhasil keluar dari sistem.');
    }
}
