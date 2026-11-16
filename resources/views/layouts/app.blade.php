<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SembakoTrack') - SembakoTrack</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
<header class="nav-pill-shell">
    <div class="nav-pill-bar">
        {{-- ZONA KIRI: Identitas Brand --}}
        <a class="brand" href="{{ route('landing') }}" aria-label="Beranda SembakoTrack">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24">
                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                    <polyline points="16 7 22 7 22 13"></polyline>
                </svg>
            </span>
            <span class="brand-name">SembakoTrack</span>
            <span class="brand-tag">ID</span>
        </a>

        {{-- ZONA TENGAH: Menu Navigasi Utama --}}
        @auth
        <nav class="nav-center-menu" aria-label="Menu Utama">
            <a href="{{ route('dashboard') }}"
               class="nav-pill-link {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               @if(request()->routeIs('dashboard')) aria-current="page" @endif>
                <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                    <rect x="3" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="3" width="7" height="7" rx="1"></rect>
                    <rect x="14" y="14" width="7" height="7" rx="1"></rect>
                    <rect x="3" y="14" width="7" height="7" rx="1"></rect>
                </svg>
                <span>Dashboard Harga</span>
            </a>

            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-pill-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                   @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>Panel Admin</span>
                </a>
                <a href="{{ route('admin.audit') }}"
                   class="nav-pill-link {{ request()->routeIs('admin.audit') ? 'active' : '' }}"
                   @if(request()->routeIs('admin.audit')) aria-current="page" @endif>
                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    <span>Audit Log</span>
                </a>
            @else
                <a href="{{ route('watchlist.index') }}"
                   class="nav-pill-link {{ request()->routeIs('watchlist.*') ? 'active' : '' }}"
                   @if(request()->routeIs('watchlist.*')) aria-current="page" @endif>
                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path>
                    </svg>
                    <span>Pantauan Saya</span>
                </a>
                <a href="{{ route('security.index') }}"
                   class="nav-pill-link {{ request()->routeIs('security.*') ? 'active' : '' }}"
                   @if(request()->routeIs('security.*')) aria-current="page" @endif>
                    <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                        <circle cx="12" cy="8" r="4"></circle>
                        <path d="M6 20v-2a4 4 0 0 1 4-4h4a4 4 0 0 1 4 4v2"></path>
                    </svg>
                    <span>Pengaturan Akun</span>
                </a>
            @endif
        </nav>
        @endauth

        {{-- ZONA KANAN: Notifikasi, Profil, & Keluar --}}
        <div class="nav-right-zone">
            @auth
                @if(!auth()->user()->isAdmin())
                    @php
                        $notifItems = $webNotifTercapai ?? collect();
                        $payloadStr = $notifJson ?? '[]';
                    @endphp
                    <details id="webNotifRoot" class="notif-wrapper" data-user-id="{{ auth()->id() }}" data-notif="{{ $payloadStr }}">
                        <summary class="notif-pill-btn" aria-label="Notifikasi Target Harga">
                            <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            </svg>
                            <span>Notifikasi</span>
                            @if($notifItems->count() > 0)
                                <span class="notif-badge" aria-live="polite">{{ $notifItems->count() }}</span>
                            @endif
                        </summary>

                        <div class="notif-dropdown" role="region" aria-label="Daftar Notifikasi Harga">
                            <div class="notif-header">
                                <strong>Notifikasi Harga</strong>
                                <span class="muted">{{ now('Asia/Jakarta')->format('d/m/Y') }}</span>
                            </div>

                            @if($notifItems->isNotEmpty())
                                <div class="notif-list">
                                    @foreach($notifItems as $wn)
                                        <div class="notif-card-item">
                                            <div class="notif-card-title">Target Harga Tercapai</div>
                                            <div class="notif-card-body">
                                                <strong>{{ $wn->komoditas?->nama }}</strong> sekarang
                                                <strong>Rp {{ number_format($wn->komoditas->latestRiwayat->harga, 0, ',', '.') }}</strong>/{{ $wn->komoditas?->satuan }}
                                            </div>
                                            <div class="notif-card-meta">
                                                Target Anda: Rp {{ number_format($wn->target_harga, 0, ',', '.') }}
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="notif-empty">
                                    Belum ada komoditas di Watchlist yang turun mencapai target harga Anda hari ini.
                                </div>
                            @endif

                            <button type="button" id="btnTestWebNotif" class="btn btn-sm notif-test-btn">
                                Tes Pop-up & Suara Notifikasi
                            </button>
                        </div>
                    </details>
                @endif

                <span class="nav-divider" aria-hidden="true"></span>

                <span class="nav-user-chip" title="Akun Aktif">
                    <span class="nav-user-dot" aria-hidden="true"></span>
                    <span>{{ auth()->user()->name }}</span>
                </span>

                <form method="POST" action="{{ route('logout') }}" class="nav-logout-form">
                    @csrf
                    <button type="submit" class="nav-logout-btn">
                        <svg class="nav-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                            <polyline points="16 17 21 12 16 7"></polyline>
                            <line x1="21" y1="12" x2="9" y2="12"></line>
                        </svg>
                        <span>Keluar</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class="nav-pill-link {{ request()->routeIs('login') ? 'active' : '' }}"
                   @if(request()->routeIs('login')) aria-current="page" @endif>
                    Masuk
                </a>
                <a href="{{ route('register') }}"
                   class="nav-pill-link {{ request()->routeIs('register') ? 'active' : '' }}"
                   @if(request()->routeIs('register')) aria-current="page" @endif>
                    Daftar
                </a>
            @endauth
        </div>
    </div>
</header>

<main class="container">
    @yield('content')
</main>

<footer class="site-footer">
    &copy; {{ date('Y') }} <strong>SembakoTrack</strong> &mdash; Sistem Pemantauan Harga Pangan Nasional.
</footer>

<script src="{{ asset('js/app.js') }}?v={{ time() }}"></script>
@yield('scripts')
@stack('scripts')
</body>
</html>

</body>
</html>