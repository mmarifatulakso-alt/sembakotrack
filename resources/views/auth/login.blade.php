@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<div class="card" style="max-width:420px;margin:auto">
    <h1>Masuk</h1>
    <form method="POST" action="{{ url('/login') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="username">
        @error('email')<div class="err">{{ $message }}</div>@enderror

        <label for="password">Password</label>
        <input id="password" type="password" name="password" required maxlength="255" autocomplete="current-password">
        @error('password')<div class="err">{{ $message }}</div>@enderror

        <p><button class="btn" type="submit">Masuk</button></p>
    </form>
    <p class="muted">Belum punya akun? <a href="{{ route('register') }}">Daftar</a></p>
</div>
@endsection