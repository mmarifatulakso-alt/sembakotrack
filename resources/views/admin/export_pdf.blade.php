<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Statistik Pantauan User</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 16px; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 5px 6px; text-align: left; }
        th { background: #e5e7eb; }
        .muted { color: #555; }
    </style>
</head>
<body>
    <h1>Laporan Statistik Pantauan User - SembakoTrack</h1>
    <div class="muted">Dibuat: {{ $dibuat }} · Total user: {{ $totalU }} · Total pantauan: {{ $totalW }}</div>

    <table>
        <thead>
            <tr><th>Komoditas</th><th>Satuan</th><th>Jml Pemantau</th><th>Rata-rata Target (Rp)</th><th>Harga Terakhir (Rp)</th><th>Tanggal</th></tr>
        </thead>
        <tbody>
        @foreach($rows as $k)
            <tr>
                <td>{{ $k->nama }}</td>
                <td>{{ $k->satuan }}</td>
                <td>{{ $k->watchlists_count }}</td>
                <td>{{ $k->watchlists_avg_target_harga ? number_format($k->watchlists_avg_target_harga, 0, ',', '.') : '-' }}</td>
                <td>{{ $k->latestRiwayat ? number_format($k->latestRiwayat->harga, 0, ',', '.') : '-' }}</td>
                <td>{{ $k->latestRiwayat?->tanggal?->format('d/m/Y') ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</body>
</html>