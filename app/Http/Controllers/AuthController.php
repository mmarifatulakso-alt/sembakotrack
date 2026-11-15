<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Rate limit 'login' sudah dipasang di route (5x/menit per email+IP).
        // Akun nonaktif ditolak lewat kondisi is_active. Tanpa "remember me" (sengaja).
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password'], 'is_active' => true])) {
            $request->session()->regenerate(); // anti session fixation

            $user = $request->user();
            $user->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
            $this->audit->log('auth.login.success', 'Login berhasil', [], $user->id);

            return redirect()->intended(
                $user->isAdmin() ? route('admin.dashboard') : route('watchlist.index')
            );
        }

        $this->audit->log('auth.login.failed', 'Login gagal', [
            'email' => Str::limit(Str::lower($data['email']), 100, ''),
        ]);

        // Pesan generik: tidak membocorkan apakah email terdaftar (anti user enumeration)
        return back()->withInput($request->only('email'))
            ->withErrors(['email' => 'Email atau password salah.']);
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request): RedirectResponse
    {
        // role TIDAK dari input user: selalu default 'user' dari database.
        $user = User::create($request->validated());

        $this->audit->log('auth.register', 'Registrasi akun baru', [], $user->id);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('watchlist.index')->with('status', 'Registrasi berhasil. Selamat datang!');
    }

    public function logout(Request $request): RedirectResponse
    {
        $this->audit->log('auth.logout', 'Logout');

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }
}