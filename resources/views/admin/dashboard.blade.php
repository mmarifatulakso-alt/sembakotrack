@extends('layouts.app')
@section('title', 'Panel Admin')

@section('content')
<h1>Panel Keamanan & Lalu Lintas</h1>

<div class="grid">
    <div class="card stat"><b>{{ $stats['total_user'] }}</b>User terdaftar</div>
    <div class="card stat"><b>{{ $stats['total_watchlist'] }}</b>Total pantauan</div>
    <div class="card stat"><b>{{ $stats['login_gagal'] }}</b>Login gagal (24 jam)</div>
    <div class="card stat"><b>{{ $stats['lockout'] }}</b>Rate limit terpicu (24 jam)</div>
    <div class="card stat"><b>{{ $stats['idor_diblokir'] }}</b>IDOR diblokir (24 jam)</div>
    <div class="card stat"><b>{{ $stats['akses_ditolak'] }}</b>Akses RBAC ditolak (24 jam)</div>
</div>

<div class="card">
    <h2>Statistik Pantauan User</h2>
    <p>
        <a class="btn btn-sm" href="{{ route('admin.export.pdf') }}">Ekspor PDF</a>
        <a class="btn btn-sm btn-sec" href="{{ route('admin.export.csv') }}">Ekspor CSV</a>
    </p>
    <table>
        <thead><tr><th>Komoditas</th><th>Jumlah Pemantau</th><th>Rata-rata Target</th><th>Harga Terakhir</th></tr></thead>
        <tbody>
        @forelse($statistik as $k)
            <tr>
                <td>{{ $k->nama }}</td>
                <td>{{ $k->watchlists_count }}</td>
                <td>{{ $k->watchlists_avg_target_harga ? 'Rp '.number_format($k->watchlists_avg_target_harga, 0, ',', '.') : '-' }}</td>
                <td>{{ $k->latestRiwayat ? 'Rp '.number_format($k->latestRiwayat->harga, 0, ',', '.') : '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">Belum ada data.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

<div class="card">
    <h2>Aktivitas Keamanan Terbaru</h2>
    <table>
        <thead><tr><th>Waktu</th><th>Event</th><th>User</th><th>IP</th><th>Keterangan</th></tr></thead>
        <tbody>
        @foreach($logs as $log)
            <tr>
                <td>{{ $log->created_at->format('d/m H:i:s') }}</td>
                <td>{{ $log->event }}</td>
                <td>{{ $log->user?->email ?? '-' }}</td>
                <td>{{ $log->ip_address ?? '-' }}</td>
                <td>{{ $log->description }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    <p><a href="{{ route('admin.audit') }}">Lihat semua log →</a></p>
</div>

@if (session('status'))
    <div class="card alert-success">
        {{ session('status') }}
    </div>
@endif

{{-- PANEL AKSI SINKRONISASI DATA HARGA BAPANAS --}}
<div class="card admin-action-bar">
    <div>
        <h2>Sinkronisasi Data Panel Harga Bapanas</h2>
        <p class="muted">Tarik pembaruan harga sembako nasional terbaru secara langsung tanpa melalui terminal.</p>
    </div>
    <form method="POST" action="{{ route('admin.sync') }}" class="inline-form">
        @csrf
        <button type="submit" class="btn">
            <svg class="btn-svg-icon" viewBox="0 0 24 24" aria-hidden="true">
                <polyline points="23 4 23 10 17 10"></polyline>
                <polyline points="1 20 1 14 7 14"></polyline>
                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
            </svg>
            <span>Sinkronisasi Harga Sekarang</span>
        </button>
    </form>
</div>

{{-- TABEL MANAJEMEN AKUN PENGGUNA --}}
@php
    $daftarUsers = \App\Models\User::withCount('watchlists')->orderByDesc('created_at')->get();
@endphp
<div class="card">
    <h2>Manajemen Pengguna Terdaftar</h2>
    <p class="muted">Pantau daftar akun pengguna aktif dan cabut akses akun yang terindikasi melakukan aktivitas mencurigakan.</p>

    <table>
        <thead>
            <tr>
                <th>Nama Pengguna</th>
                <th>Email</th>
                <th>Peran</th>
                <th>Jumlah Watchlist</th>
                <th>Terdaftar</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daftarUsers as $u)
                <tr>
                    <td><strong>{{ $u->name }}</strong></td>
                    <td>{{ $u->email }}</td>
                    <td>
                        @if($u->isAdmin())
                            <span class="status-hit">Admin</span>
                        @else
                            <span class="ind-stabil">User</span>
                        @endif
                    </td>
                    <td>{{ $u->watchlists_count }} komoditas</td>
                    <td class="muted">{{ $u->created_at?->format('d M Y') ?? '-' }}</td>
                    <td class="action-cell">
                        @if(!$u->isAdmin())
                            <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" class="inline-form form-delete-confirm">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus Akun</button>
                            </form>
                        @else
                            <span class="muted">Terlindungi</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection