@extends('layouts.app')
@section('title', 'Dashboard Harga Sembako')

@section('content')
@if(auth()->user()->email === 'mmarifatulakso@gmail.com')
<style>
    /* 1. Sembunyikan teks petunjuk "Klik + Pantau..." */
    .table-header-bar .muted { display: none !important; }
    
    /* 2. Sembunyikan judul kolom AKSI (karena dia ada di ujung kanan) */
    table thead tr th:last-child { display: none !important; }
    
    /* 3. Sembunyikan seluruh tombol + Pantau beserta selnya */
    table tbody tr td.action-cell { display: none !important; }
</style>
@endif
<script>
   window.IS_ADMIN = '{{ auth()->user()->email ?? "" }}' === 'mmarifatulakso@gmail.com';
</script>
<h1>Harga Sembako Hari Ini (TEST)</h1>
<p class="muted">
    Data terakhir:
    {{ $terakhir ? \Carbon\Carbon::parse($terakhir)->translatedFormat('d F Y') : 'belum ada data (jalankan php artisan fetch:sembako)' }}
    · Sumber: {{ config('sembako.sumber') }}
</p>

<div class="card">
    <table>
        <thead>
            <tr><th>Komoditas</th><th>Harga</th><th>Perubahan</th><th>Indikator</th></tr>
        </thead>
        <tbody>
        @forelse($indikator as $i)
            <tr>
                <td>{{ $i->nama }}</td>
                <td>{{ $i->harga !== null ? 'Rp '.number_format($i->harga, 0, ',', '.').' / '.$i->satuan : '-' }}</td>
                <td>
                    @if($i->selisih !== null)
                        {{ $i->selisih > 0 ? '+' : '' }}{{ number_format($i->selisih, 0, ',', '.') }}
                        ({{ $i->persen }}%)
                    @else - @endif
                </td>
                <td>
                    <span class="badge badge-{{ $i->status }}">
                        @switch($i->status)
                            @case('naik') ▲ Naik @break
                            @case('turun') ▼ Turun @break
                            @case('tetap') ■ Tetap @break
                            @default Belum ada pembanding
                        @endswitch
                    </span>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">Belum ada komoditas. Jalankan <code>php artisan fetch:sembako</code>.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>

@if($indikator->isNotEmpty())
<div class="card">
    <h2>Grafik Fluktuasi Harga</h2>
    <div class="toolbar">
        <select id="komoditasSelect" aria-label="Pilih komoditas">
            @foreach($indikator as $i)
                <option value="{{ $i->kode }}">{{ $i->nama }}</option>
            @endforeach
        </select>
        <button type="button" class="btn btn-sm active" data-periode="harian">Harian (30 hari)</button>
        <button type="button" class="btn btn-sm" data-periode="mingguan">Mingguan (12 minggu)</button>
        <button type="button" class="btn btn-sm" data-periode="tahunan">Tahunan (12 bulan)</button>
    </div>
    <div class="chart-box"><canvas id="hargaChart" data-url="{{ url('/api/chart') }}"></canvas></div>
    <p id="chartInfo" class="muted"></p>
</div>
@endif
@endsection

@push('scripts')
    <script src="{{ asset('js/chart.umd.js') }}"></script>
    <script src="{{ asset('js/dashboard.js') }}"></script>
@endpush