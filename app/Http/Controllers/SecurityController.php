<?php

namespace App\Http\Controllers;

use App\Models\Watchlist;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SecurityController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function index(Request $request): View
    {
        $user = $request->user();

        $watchlists = Watchlist::with('komoditas.latestRiwayat')
            ->where('user_id', $user->id)
            ->get();

        $totalPantauan = $watchlists->count();
        $totalTercapai = $watchlists->filter(function ($w) {
            $hrg = $w->komoditas?->latestRiwayat?->harga;
            return $hrg !== null && $hrg <= $w->target_harga;
        })->count();

        return view('security.index', compact('user', 'totalPantauan', 'totalTercapai'));
    }

    /**
     * Logika Edit Profil Akun (Nama & Email) dengan validasi unik & Audit Log.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'min:2', 'max:100'],
            'email' => [
                'required',
                'string',
                'email',
                'max:150',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'name.required'  => 'Nama akun wajib diisi.',
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format alamat email tidak valid.',
            'email.unique'   => 'Email ini sudah digunakan oleh akun lain.',
        ]);

        $emailLama = $user->email;
        $namaLama  = $user->name;

        $user->forceFill([
            'name'  => strip_tags(trim($validated['name'])),
            'email' => strtolower(trim($validated['email'])),
        ])->save();

        $this->audit->log('account.profile.updated', 'User memperbarui informasi profil akun', [
            'old_name'  => $namaLama,
            'new_name'  => $user->name,
            'old_email' => $emailLama,
            'new_email' => $user->email,
        ], $user->id);

        return back()->with('status', 'Informasi profil akun berhasil diperbarui.');
    }

    /**
     * Logika Ganti Kata Sandi dengan verifikasi password lama.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.confirmed'                => 'Konfirmasi password baru tidak cocok.',
            'password.min'                      => 'Password baru minimal 6 karakter.',
        ]);

        $user = $request->user();
        $user->forceFill([
            'password' => Hash::make($request->input('password')),
        ])->save();

        $this->audit->log('security.password.changed', 'User memperbarui password akun', [], $user->id);

        return back()->with('status', 'Password akun berhasil diperbarui.');
    }

    /**
     * Logika Hapus Akun Mandiri (menghapus seluruh Watchlist terkait & mencatat Audit Log).
     */
    public function destroyAccount(Request $request): RedirectResponse
    {
        $request->validate([
            'delete_password' => ['required', 'current_password'],
        ], [
            'delete_password.required'         => 'Masukkan password Anda untuk mengonfirmasi penghapusan akun.',
            'delete_password.current_password' => 'Password konfirmasi salah. Penghapusan akun dibatalkan.',
        ]);

        $user = $request->user();
        $userId = $user->id;
        $userEmail = $user->email;

        // Bersihkan data Watchlist milik user
        Watchlist::where('user_id', $userId)->delete();

        // Catat ke Audit Log sebelum akun dihapus
        $this->audit->log('account.deleted', "User menghapus akunnya secara mandiri ({$userEmail})", [
            'deleted_user_id' => $userId,
            'email'           => $userEmail,
        ], null);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $user->delete();

        return redirect()->route('landing')->with('status', 'Akun Anda beserta seluruh daftar pantauan telah berhasil dihapus.');
    }

    public static function sendAutoAlerts(): int
    {
        return 0;
    }
}