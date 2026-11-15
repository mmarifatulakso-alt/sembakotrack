@extends('layouts.app')
@section('title', 'Selamat Datang di SembakoTrack')

@section('content')
{{-- 1. HERO SECTION --}}
<div class="hero-banner">
    <div>
        <span class="hero-badge">
            SISTEM PEMANTAUAN PANGAN NASIONAL
        </span>
        <h1>
            Pantau Harga Sembako Harian & Target Anggaran Secara Terukur
        </h1>
        <p>
            <strong>SembakoTrack</strong> menyajikan data harga komoditas pangan yang terintegrasi dengan <strong>Panel Harga Bapanas</strong>. Dilengkapi fitur daftar pantauan pribadi (<em>Watchlist</em>), perbandingan rata-rata 30 hari, notifikasi web otomatis, serta perlindungan keamanan akun berstandar <strong>OWASP</strong>.
        </p>

        <div class="hero-actions">
            @guest
                <a href="{{ route('login') }}" class="btn btn-hero-primary">
                    Masuk ke Akun
                </a>
                <a href="{{ route('register') }}" class="btn btn-hero-secondary">
                    Daftar Akun Baru
                </a>
            @else
                <a href="{{ route('dashboard') }}" class="btn btn-hero-primary">
                    Buka Dashboard Harga
                </a>
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-hero-secondary">
                        Buka Panel Admin
                    </a>
                @else
                    <a href="{{ route('watchlist.index') }}" class="btn btn-hero-secondary">
                        Buka Pantauan Saya
                    </a>
                @endif
            @endguest
        </div>
    </div>

    <div class="hero-side-card">
        <div class="hero-side-title">Ringkasan Sistem</div>
        <div class="hero-stat-list">
            <div class="hero-stat-item">
                <span>Sumber Data</span>
                <strong>Panel Bapanas RI</strong>
            </div>
            <div class="hero-stat-item">
                <span>Cakupan Komoditas</span>
                <strong>{{ $totalKomoditas }} Bahan Pokok</strong>
            </div>
            <div class="hero-stat-item">
                <span>Peringatan Target</span>
                <strong>Notifikasi Web</strong>
            </div>
            <div class="hero-stat-item">
                <span>Keamanan Sistem</span>
                <strong>OWASP & Audit Log</strong>
            </div>
        </div>
    </div>
</div>

{{-- 2. TENTANG WEBSITE --}}
<div class="card">
    <h2>Mengapa Menggunakan SembakoTrack?</h2>
    <p class="muted">
        Perubahan harga bahan pokok yang fluktuatif sering menyulitkan rumah tangga maupun pelaku usaha kuliner dalam menentukan waktu belanja yang tepat. <strong>SembakoTrack</strong> merangkum riwayat harga harian menjadi indikator tren yang mudah dibaca serta memberikan pemberitahuan otomatis saat harga turun mencapai batas anggaran Anda.
    </p>

    <div class="landing-stats-grid">
        <div class="stat-highlight">
            <div class="stat-highlight-num">{{ $totalKomoditas }} Komoditas</div>
            <div class="muted">Dipantau langsung dari Panel Harga Bapanas</div>
        </div>
        <div class="stat-highlight">
            <div class="stat-highlight-num">365 Hari</div>
            <div class="muted">Visualisasi grafik tren & rata-rata bulanan</div>
        </div>
        <div class="stat-highlight">
            <div class="stat-highlight-num">Real-Time</div>
            <div class="muted">Peringatan langsung di browser saat harga turun</div>
        </div>
        <div class="stat-highlight">
            <div class="stat-highlight-num">Terlindungi</div>
            <div class="muted">Dilengkapi Anti-IDOR, CSP, & Audit Trail</div>
        </div>
    </div>
</div>

{{-- 3. EMPAT FITUR UTAMA --}}
<div class="page-header">
    <h2>Fitur Utama Platform</h2>
    <p class="muted">Dirancang terstruktur untuk kebutuhan pemantauan harga pengguna dan pengawasan administrator.</p>
</div>

<div class="feature-grid">
    <div class="feature-card">
        <span class="feature-tag">01 / Data Harian</span>
        <h3>Dashboard & Grafik Tren</h3>
        <p class="muted">
            Tabel harga rata-rata nasional harian lengkap dengan selisih perubahan harga serta grafik riwayat fluktuasi hingga 12 bulan.
        </p>
    </div>

    <div class="feature-card">
        <span class="feature-tag">02 / Target Anggaran</span>
        <h3>Watchlist & Analisis 30 Hari</h3>
        <p class="muted">
            Simpan komoditas yang sering Anda beli, tentukan target harga, dan bandingkan langsung dengan rata-rata harga sebulan terakhir.
        </p>
    </div>

    <div class="feature-card">
        <span class="feature-tag">03 / Peringatan</span>
        <h3>Notifikasi Web Otomatis</h3>
        <p class="muted">
            Panel notifikasi di bagian atas halaman yang memberikan peringatan pop-up dan suara begitu harga komoditas menyentuh target Anda.
        </p>
    </div>

    <div class="feature-card">
        <span class="feature-tag">04 / Privasi & Log</span>
        <h3>Pengaturan Akun & Audit Log</h3>
        <p class="muted">
            Pengelolaan profil dan kata sandi mandiri bagi pengguna, serta pencatatan aktivitas keamanan (Audit Log) dan ekspor laporan bagi Admin.
        </p>
    </div>
</div>

{{-- 4. ALUR PENGGUNAAN --}}
<div class="card">
    <h2>Cara Menggunakan SembakoTrack</h2>
    <div class="steps-grid">
        <div class="step-box">
            <div class="step-badge">Langkah 01</div>
            <div class="step-title">Masuk atau Buat Akun</div>
            <p class="muted">
                Daftarkan akun pengguna untuk membuka akses ke tabel harga harian, grafik fluktuasi, dan daftar pantauan pribadi.
            </p>
        </div>

        <div class="step-box">
            <div class="step-badge">Langkah 02</div>
            <div class="step-title">Atur Target di Watchlist</div>
            <p class="muted">
                Pilih komoditas sembako yang ingin dipantau lalu masukkan nominal batas harga beli yang sesuai dengan rencana anggaran Anda.
            </p>
        </div>

        <div class="step-box">
            <div class="step-badge">Langkah 03</div>
            <div class="step-title">Pantau Status & Notifikasi</div>
            <p class="muted">
                Sistem akan mengevaluasi harga terbaru terhadap target Anda dan menampilkan pemberitahuan saat target harga tercapai.
            </p>
        </div>
    </div>
</div>
@endsection