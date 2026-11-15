@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<div class="card auth-card">
    <h1>Masuk ke Akun</h1>
    <p class="muted">Masuk untuk memantau harga sembako harian dan mengelola target anggaran Anda.</p>

    @if (session('status'))
        <div class="card alert-success">
            {{ session('status') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="card alert-danger" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="pw-input-wrap">
                <input type="password" id="password" name="password" autocomplete="current-password" required>
                <button type="button" class="pw-toggle-btn" data-target="password" aria-label="Tampilkan password" aria-pressed="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
        </div>

        <div class="auth-options-row">
            <label class="checkbox-label">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                <span>Ingat saya</span>
            </label>

            <details class="forgot-pw-details">
                <summary>Lupa password?</summary>
                <div class="forgot-pw-box">
                    Untuk mereset password akun pada server lokal, hubungi Administrator sistem atau gunakan fitur <strong>Pengaturan Akun</strong> saat sesi aktif.
                </div>
            </details>
        </div>

        <button type="submit" class="btn auth-submit-btn">Masuk</button>
    </form>

    <p class="auth-switch-text muted">
        Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
    </p>
</div>
@endsection