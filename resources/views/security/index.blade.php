@extends('layouts.app')
@section('title', 'Pengaturan Akun & Profil')

@section('content')
<div class="page-header">
    <h1>Pengaturan Akun & Profil</h1>
    <p class="muted">Kelola informasi profil akun, kata sandi, dan pengaturan privasi Anda.</p>
</div>

@if (session('status'))
    <div class="card alert-success">
        {{ session('status') }}
    </div>
@endif

@if ($errors->any())
    <div class="card alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="account-summary-grid">
    <div class="card">
        <div class="muted">Nama Akun</div>
        <div class="summary-val">{{ $user->name }}</div>
        <div class="muted">{{ $user->email }}</div>
    </div>

    <div class="card">
        <div class="muted">Status & Peran Akun</div>
        <div class="summary-val">{{ ucfirst($user->role) }} Aktif</div>
        <div class="muted">Terdaftar sejak {{ $user->created_at?->format('d M Y') ?? '-' }}</div>
    </div>

    <div class="card">
        <div class="muted">Ringkasan Pantauan Saya</div>
        <div class="summary-val">{{ $totalPantauan }} Komoditas</div>
        <div class="muted">{{ $totalTercapai }} mencapai target harga</div>
    </div>
</div>

<div class="account-forms-grid">
    <div class="card">
        <h2>Informasi Profil</h2>
        <p class="muted">Perbarui nama tampilan dan alamat email yang terhubung dengan akun Anda.</p>

        <form method="POST" action="{{ route('security.profile.update') }}">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="name">Nama Lengkap / Nama Akun</label>
                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="email">Alamat Email</label>
                <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" maxlength="150" required>
            </div>

            <button type="submit" class="btn">Simpan Perubahan Profil</button>
        </form>
    </div>

    <div class="card">
        <h2>Ubah Kata Sandi</h2>
        <p class="muted">Pastikan Anda menggunakan kombinasi kata sandi yang kuat (minimal 6 karakter).</p>

        <form method="POST" action="{{ route('security.password.update') }}">
            @csrf

            <div class="form-group">
                <label for="current_password">Password Saat Ini</label>
                <div class="pw-input-wrap">
                    <input type="password" name="current_password" id="current_password" required>
                    <button type="button" class="pw-toggle-btn" data-target="current_password">Lihat</button>
                </div>
            </div>

            <div class="form-group">
                <label for="password">Password Baru</label>
                <div class="pw-input-wrap">
                    <input type="password" name="password" id="password" minlength="6" required>
                    <button type="button" class="pw-toggle-btn" data-target="password">Lihat</button>
                </div>
            </div>

            <div class="form-group">
                <label for="password_confirmation">Ulangi Password Baru</label>
                <div class="pw-input-wrap">
                    <input type="password" name="password_confirmation" id="password_confirmation" minlength="6" required>
                    <button type="button" class="pw-toggle-btn" data-target="password_confirmation">Lihat</button>
                </div>
            </div>

            <button type="submit" class="btn">Perbarui Password</button>
        </form>
    </div>
</div>

<div class="card danger-zone-card">
    <h2>Hapus Akun Permanen</h2>
    <p class="muted">
        Menghapus akun akan menghapus seluruh daftar komoditas di Watchlist Anda secara permanen. Masukkan password Anda untuk mengonfirmasi.
    </p>

    <form method="POST" action="{{ route('security.account.destroy') }}" class="delete-account-form">
        @csrf
        @method('DELETE')
        <div class="form-group">
            <label for="delete_password">Konfirmasi Password Anda</label>
            <div class="pw-input-wrap">
                <input type="password" name="delete_password" id="delete_password" placeholder="Masukkan password saat ini..." required>
                <button type="button" class="pw-toggle-btn" data-target="delete_password">Lihat</button>
            </div>
        </div>
        <button type="submit" class="btn btn-danger">Hapus Akun Saya</button>
    </form>
</div>
@endsection