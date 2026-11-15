@extends('layouts.app')
@section('title', 'Pantauan Saya')

@section('content')
@php
    $semuaWatchlist  = $watchlists ?? $items ?? collect();
    $daftarKomoditas = $komoditas ?? $komoditasList ?? \App\Models\Komoditas::with('latestRiwayat')->orderBy('nama')->get();

    $totalHargaSekarang = 0;
    $totalTargetHarga   = 0;
    $jumlahTercapai     = 0;

    $komoditasIds = $semuaWatchlist->pluck('komoditas_id')->filter()->unique()->values();
    $rataRata30Hari = [];
    if ($komoditasIds->isNotEmpty()) {
        $rataRata30Hari = \App\Models\RiwayatHarga::whereIn('komoditas_id', $komoditasIds)
            ->where('tanggal', '>=', now('Asia/Jakarta')->subDays(30)->toDateString())
            ->selectRaw('komoditas_id, AVG(harga) as rata_harga')
            ->groupBy('komoditas_id')
            ->pluck('rata_harga', 'komoditas_id')
            ->toArray();
    }

    foreach ($semuaWatchlist as $item) {
        $hrg = $item->komoditas?->latestRiwayat?->harga;
        if ($hrg !== null) {
            $totalHargaSekarang += $hrg;
            $totalTargetHarga   += $item->target_harga;
            if ($hrg <= $item->target_harga) {
                $jumlahTercapai++;
            }
        }
    }

    $selisihTotal = $totalHargaSekarang - $totalTargetHarga;

    $statusFilter = request('status', 'all');
    $daftarWatchlist = $semuaWatchlist->filter(function ($w) use ($statusFilter) {
        $hrg = $w->komoditas?->latestRiwayat?->harga;
        $hit = $hrg !== null && $hrg <= $w->target_harga;
        if ($statusFilter === 'hit') {
            return $hit;
        }
        if ($statusFilter === 'wait') {
            return !$hit;
        }
        return true;
    });

    // Tangkap parameter pintasan "+ Pantau" dari halaman Dashboard Harga
    $pilihNama   = request('komoditas');
    $saranTarget = request('target');
@endphp

<div class="page-header">
    <h1>Watchlist & Kalkulator Anggaran Sembako</h1>
    <p class="muted">Pantau komoditas pilihan Anda, atur jumlah kebutuhan (kuantitas), dan evaluasi rata-rata harga 30 hari.</p>
</div>

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

@if($semuaWatchlist->isNotEmpty())
<div class="account-summary-grid" id="watchlistSummaryCards">
    <div class="card stat-card-info">
        <div class="muted">Total Harga Pasar (Sesuai Kuantitas)</div>
        <div class="summary-val" id="sumMarketVal">Rp {{ number_format($totalHargaSekarang, 0, ',', '.') }}</div>
        <div class="muted" id="sumMarketSub">Akumulasi {{ $semuaWatchlist->count() }} komoditas pantauan</div>
    </div>

    <div class="card stat-card-info">
        <div class="muted">Total Target Anggaran Anda</div>
        <div class="summary-val" id="sumTargetVal">Rp {{ number_format($totalTargetHarga, 0, ',', '.') }}</div>
        <div id="sumDiffBadgeWrap">
            @if($selisihTotal <= 0)
                <span class="status-hit">Hemat Rp {{ number_format(abs($selisihTotal), 0, ',', '.') }} dari target</span>
            @else
                <span class="status-wait">Selisih +Rp {{ number_format($selisihTotal, 0, ',', '.') }} di atas target</span>
            @endif
        </div>
    </div>

    <div class="card stat-card-info">
        <div class="muted">Progres Pencapaian Target</div>
        <div class="summary-val">{{ $jumlahTercapai }} / {{ $semuaWatchlist->count() }} Komoditas</div>
        <div class="muted">Siap dibeli sesuai target harga Anda</div>
    </div>
</div>
@endif

<div class="card section-gap" id="formWatchlistCard">
    <h2>Tambah Komoditas Pantauan</h2>
    <form method="POST" action="{{ route('watchlist.store') }}" class="watchlist-form-row">
        @csrf
        <div class="wf-field wf-komoditas">
            <label for="komoditas_id">Komoditas</label>
            <select name="komoditas_id" id="komoditas_id" required>
                <option value="">-- Pilih Komoditas --</option>
                @foreach($daftarKomoditas as $k)
                    @php
                        $isSelected = old('komoditas_id') == $k->id || ($pilihNama && strcasecmp(trim($pilihNama), trim($k->nama)) === 0);
                    @endphp
                    <option value="{{ $k->id }}" @selected($isSelected)>
                        {{ $k->nama }} ({{ $k->satuan }})
                        @if($k->latestRiwayat)
                            — Rp {{ number_format($k->latestRiwayat->harga, 0, ',', '.') }}
                        @endif
                    </option>
                @endforeach
            </select>
        </div>

        <div class="wf-field wf-target">
            <label for="target_harga">Target Harga / Satuan (Rp)</label>
            <input type="number" name="target_harga" id="target_harga" value="{{ old('target_harga', $saranTarget) }}" min="100" max="10000000" step="1" placeholder="Contoh: 15000" required>
        </div>

        <div class="wf-field wf-catatan">
            <label for="catatan">Catatan (Opsional)</label>
            <input type="text" name="catatan" id="catatan" value="{{ old('catatan') }}" maxlength="150" placeholder="Beli jika turun...">
        </div>

        <div class="wf-action">
            <button type="submit" class="btn">Tambah ke Watchlist</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="watchlist-table-header">
        <h2>Daftar Pantauan Saya</h2>
        <div class="filter-pills">
            <a href="{{ route('watchlist.index', ['status' => 'all']) }}" class="filter-pill {{ $statusFilter === 'all' ? 'active' : '' }}">
                Semua ({{ $semuaWatchlist->count() }})
            </a>
            <a href="{{ route('watchlist.index', ['status' => 'hit']) }}" class="filter-pill {{ $statusFilter === 'hit' ? 'active' : '' }}">
                Tercapai ({{ $jumlahTercapai }})
            </a>
            <a href="{{ route('watchlist.index', ['status' => 'wait']) }}" class="filter-pill {{ $statusFilter === 'wait' ? 'active' : '' }}">
                Di Atas Target ({{ $semuaWatchlist->count() - $jumlahTercapai }})
            </a>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Komoditas</th>
                <th>Kuantitas</th>
                <th>Harga Saat Ini</th>
                <th>Rata-Rata 30 Hari</th>
                <th>Target Harga</th>
                <th>Status</th>
                <th>Catatan</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
        @forelse($daftarWatchlist as $w)
            @php
                $hargaSekarang = $w->komoditas?->latestRiwayat?->harga;
                $tercapai      = $hargaSekarang !== null && $hargaSekarang <= $w->target_harga;
                $avg30         = isset($rataRata30Hari[$w->komoditas_id]) ? round($rataRata30Hari[$w->komoditas_id]) : null;
                $satuanItem    = $w->komoditas?->satuan ?? 'kg';
            @endphp
            <tr class="watchlist-item-row"
                data-watch-id="{{ $w->id }}"
                data-harga="{{ $hargaSekarang ?? 0 }}"
                data-target="{{ $w->target_harga }}">
                <td>
                    <strong>{{ $w->komoditas?->nama ?? '-' }}</strong>
                    <span class="muted">/{{ $satuanItem }}</span>
                </td>
                <td>
                    <div class="qty-control-wrap">
                        <input type="number"
                               class="qty-input"
                               value="1"
                               min="1"
                               max="500"
                               step="1"
                               aria-label="Kuantitas {{ $w->komoditas?->nama }}">
                        <span class="muted">{{ $satuanItem }}</span>
                    </div>
                </td>
                <td>
                    @if($hargaSekarang !== null)
                        <strong class="row-market-display">Rp {{ number_format($hargaSekarang, 0, ',', '.') }}</strong>
                    @else
                        <span class="muted">-</span>
                    @endif
                </td>
                <td>
                    @if($avg30 && $hargaSekarang !== null)
                        <div>Rp {{ number_format($avg30, 0, ',', '.') }}</div>
                        @if($hargaSekarang < $avg30 * 0.99)
                            <span class="trend-down">Di bawah rata-rata (Murah)</span>
                        @elseif($hargaSekarang > $avg30 * 1.01)
                            <span class="trend-up">Di atas rata-rata (Tinggi)</span>
                        @else
                            <span class="muted">Harga stabil</span>
                        @endif
                    @else
                        <span class="muted">-</span>
                    @endif
                </td>
                <td>
                    <span class="row-target-display">Rp {{ number_format($w->target_harga, 0, ',', '.') }}</span>
                </td>
                <td>
                    @if($hargaSekarang === null)
                        <span class="muted">Menunggu data</span>
                    @elseif($tercapai)
                        <span class="status-hit">Target Tercapai</span>
                    @else
                        <span class="status-wait">Di Atas Target</span>
                    @endif
                </td>
                <td class="muted">{{ $w->catatan ?: '-' }}</td>
                <td class="action-cell">
                    <div class="action-buttons">
                        <a href="{{ route('watchlist.edit', $w->id) }}" class="btn btn-sm btn-outline">Edit</a>
                        <form method="POST" action="{{ route('watchlist.destroy', $w->id) }}" class="inline-form">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="8" class="muted">
                    Belum ada komoditas yang sesuai pada daftar pantauan ini.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection