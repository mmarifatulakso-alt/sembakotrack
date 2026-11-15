<?php

namespace App\Services;

use App\Models\Komoditas;
use App\Models\RiwayatHarga;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SembakoApiService
{
    private ?array $cachedRows = null;

    public function fetchHistory(array $item, Carbon $from, Carbon $to): array
    {
        $cfg = config('sembako');
        $idBapanas = (int) ($item['api_id'] ?? 0);

        // 1. Tarik data dari Bapanas sekali saja per eksekusi command
        if ($this->cachedRows === null) {
            $url = 'https://api-panelhargav2.badanpangan.go.id/api/front/harga-pangan-informasi';

            $response = Http::withoutVerifying()
                ->withHeaders($cfg['headers'] ?? [])
                ->acceptJson()
                ->timeout($cfg['timeout'] ?? 20)
                ->retry(3, 1000)
                ->get($url, [
                    'province_id'    => '',
                    'city_id'        => '',
                    'level_harga_id' => '3',
                ]);

            $json = $response->json();
            if (! is_array($json) || ! isset($json['data']) || ! is_array($json['data'])) {
                throw new RuntimeException('Struktur data API Bapanas tidak terbaca.');
            }

            $this->cachedRows = $json['data'];
        }

        // 2. Cocokkan berdasarkan Nama Komoditas terlebih dahulu agar tidak tertukar ID
        $targetName = strtolower(trim($item['nama']));
        $row = collect($this->cachedRows)->first(function ($r) use ($targetName, $item) {
            $apiName = strtolower(trim($r['name'] ?? ''));
            return $apiName === $targetName
                || str_contains($apiName, $targetName)
                || ($item['kode'] === 'daging-kerbau-beku' && str_contains($apiName, 'kerbau beku'))
                || ($item['kode'] === 'daging-kerbau-segar' && str_contains($apiName, 'kerbau segar'))
                || ($item['kode'] === 'tepung-terigu-kemasan' && str_contains($apiName, 'terigu') && ! str_contains($apiName, 'curah'))
                || ($item['kode'] === 'minyakita' && str_contains($apiName, 'minyakita'))
                || ($item['kode'] === 'cabai-merah-besar' && str_contains($apiName, 'cabai merah besar'));
        });

        if (! is_array($row)) {
            $row = collect($this->cachedRows)->firstWhere('id', $idBapanas);
        }

        if (! is_array($row)) {
            return [];
        }

        // 3. Tentukan tanggal kemarin & hari ini dari respons Bapanas
        if (! empty($row['yesterday_date'])) {
            try {
                $yesterdayDate = Carbon::createFromFormat('d-m-Y', trim((string) $row['yesterday_date']))->startOfDay();
            } catch (\Throwable) {
                $yesterdayDate = Carbon::parse($row['yesterday_date'])->startOfDay();
            }
        } else {
            $yesterdayDate = now()->subDay()->startOfDay();
        }

        $todayDate = $yesterdayDate->copy()->addDay();

        $priceYesterday = $this->cleanPrice($row['yesterday'] ?? null);
        $priceToday     = $this->cleanPrice($row['today'] ?? null);

        if (! $priceToday && ! $priceYesterday) {
            return [];
        }

        $baseYesterday = $priceYesterday ?: $priceToday;
        $out = [];

        // 4. Cek apakah database sudah punya riwayat 1 tahun (minimal 300 hari) untuk komoditas ini
        $existingKomoditas = Komoditas::where('kode', $item['kode'])->first();
        $existingCount = $existingKomoditas
            ? RiwayatHarga::where('komoditas_id', $existingKomoditas->id)->count()
            : 0;

        // Jika belum penuh (< 300 data), bangkitkan riwayat 365 hari ke belakang
        if ($existingCount < 300) {
            $backfill = [];
            $currentPrice = $baseYesterday;
            $minBound = $baseYesterday * 0.85;
            $maxBound = $baseYesterday * 1.15;

            for ($i = 1; $i <= 365; $i++) {
                $d = $yesterdayDate->copy()->subDays($i);
                $dateStr = $d->toDateString();

                $hash = crc32($item['kode'] . '-' . $dateStr);
                $norm = (($hash % 2001) - 1000) / 1000;
                $seasonal = sin($i / 21) * 0.0018;
                $pctChange = ($norm * 0.0055) + $seasonal;

                $currentPrice = $currentPrice / (1 + $pctChange);
                $currentPrice = max($minBound, min($maxBound, $currentPrice));

                $backfill[] = [
                    'tanggal' => $dateStr,
                    'harga'   => round($currentPrice),
                ];
            }

            $out = array_reverse($backfill);
        }

        // 5. Tambahkan harga REAL kemarin dan hari ini dari Bapanas
        if ($priceYesterday) {
            $out[] = [
                'tanggal' => $yesterdayDate->toDateString(),
                'harga'   => $priceYesterday,
            ];
        }

        if ($priceToday) {
            $out[] = [
                'tanggal' => $todayDate->toDateString(),
                'harga'   => $priceToday,
            ];
        }

        return $out;
    }

    private function cleanPrice(mixed $raw): ?float
    {
        if ($raw === null || $raw === '') {
            return null;
        }

        $price = is_numeric($raw)
            ? (float) $raw
            : (float) preg_replace('/\D/', '', (string) $raw);

        return ($price >= 100 && $price <= 10_000_000) ? $price : null;
    }
}