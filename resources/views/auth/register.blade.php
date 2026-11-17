@extends('layouts.app')
@section('title', 'Daftar Akun')

@section('content')
<div class="card auth-card">
    <h1>Daftar Akun Baru</h1>
    <p class="muted">Buat akun untuk memantau harga sembako harian dan mengelola target anggaran Anda.</p>

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

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="form-group">
            <label for="name">Nama Lengkap</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" autocomplete="name" required autofocus>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" autocomplete="email" required>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <div class="pw-input-wrap">
                <input type="password" id="password" name="password" autocomplete="new-password" required>
                <button type="button" class="pw-toggle-btn" data-target="password" aria-label="Tampilkan password" aria-pressed="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
        </div>

        <div class="form-group">
            <label for="password_confirmation">Konfirmasi Password</label>
            <div class="pw-input-wrap">
                <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" required>
                <button type="button" class="pw-toggle-btn" data-target="password_confirmation" aria-label="Tampilkan konfirmasi password" aria-pressed="false">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="btn auth-submit-btn">Daftar</button>
    </form>

    <p class="auth-switch-text muted">
        Sudah punya akun? <a href="{{ route('login') }}">Masuk di sini</a>
    </p>
</div>
@endsection