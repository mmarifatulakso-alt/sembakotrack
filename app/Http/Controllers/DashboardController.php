<?php

namespace App\Http\Controllers;

use App\Models\Komoditas;
use App\Models\RiwayatHarga;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Semua data dari tabel hasil tarikan API (php artisan fetch:sembako). Tanpa data dummy.
        $indikator = Komoditas::orderBy('nama')->get()->map(function (Komoditas $k) {
            $rows = $k->riwayat()->orderByDesc('tanggal')->limit(2)->get(); // 2 harga terbaru
            $now  = $rows->first();
            $prev = $rows->get(1);

            $selisih = ($now && $prev) ? $now->harga - $prev->harga : null;
            $persen  = ($selisih !== null && $prev->harga > 0) ? round($selisih / $prev->harga * 100, 2) : null;

            $status = match (true) {
                $selisih === null => 'na',
                $selisih > 0      => 'naik',
                $selisih < 0      => 'turun',
                default           => 'tetap',
            };

            return (object) [
                'kode'    => $k->kode,
                'nama'    => $k->nama,
                'satuan'  => $k->satuan,
                'harga'   => $now?->harga,
                'tanggal' => $now?->tanggal,
                'selisih' => $selisih,
                'persen'  => $persen,
                'status'  => $status,
            ];
        });

        $terakhir = RiwayatHarga::max('tanggal');

        return view('dashboard', compact('indikator', 'terakhir'));
    }

    /**
     * JSON untuk Chart.js. Periode divalidasi whitelist.
     *
     * >>> Data berasal dari tabel riwayat_harga (diisi `php artisan fetch:sembako`).
     * >>> Untuk mengganti sumber API, ubah SEMBAKO_API_URL & config/sembako.php, bukan controller ini.
     */
    public function chart(Request $request, Komoditas $komoditas): JsonResponse
    {
        $periode = $request->validate([
            'periode' => ['required', Rule::in(['harian', 'mingguan', 'tahunan'])],
        ])['periode'];

        $days = match ($periode) {
            'harian'   => 30,   // 30 hari terakhir, per hari
            'mingguan' => 84,   // 12 minggu, rata-rata per minggu
            'tahunan'  => 365,  // 12 bulan, rata-rata per bulan
        };

        $rows = $komoditas->riwayat()
            ->where('tanggal', '>=', now()->subDays($days)->toDateString())
            ->orderBy('tanggal')
            ->get(['tanggal', 'harga']);

        $points = match ($periode) {
            'harian' => $rows->map(fn ($r) => [
                'label' => $r->tanggal->translatedFormat('d M'),
                'value' => (float) $r->harga,
            ]),
            'mingguan' => $rows
                ->groupBy(fn ($r) => $r->tanggal->copy()->startOfWeek()->format('Y-m-d'))
                ->map(fn ($g, $k) => [
                    'label' => Carbon::parse($k)->translatedFormat('d M'),
                    'value' => round($g->avg('harga')),
                ]),
            'tahunan' => $rows
                ->groupBy(fn ($r) => $r->tanggal->format('Y-m'))
                ->map(fn ($g, $k) => [
                    'label' => Carbon::createFromFormat('Y-m', $k)->translatedFormat('M Y'),
                    'value' => round($g->avg('harga')),
                ]),
        };
        $points = $points->values();

        return response()->json([
            'nama'   => $komoditas->nama,
            'satuan' => $komoditas->satuan,
            'labels' => $points->pluck('label'),
            'values' => $points->pluck('value'),
        ]);
    }
}