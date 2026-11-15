<?php

namespace App\Console\Commands;

use App\Models\Komoditas;
use App\Models\RiwayatHarga;
use App\Services\AuditLogger;
use App\Services\SembakoApiService;
use Illuminate\Console\Command;
use Throwable;

class FetchSembako extends Command
{
    protected $signature = 'fetch:sembako {--days= : Jumlah hari ke belakang yang ditarik (default dari SEMBAKO_DAYS_BACK)}';

    protected $description = 'Tarik harga sembako REAL dari API publik lalu simpan ke MySQL';

    public function handle(SembakoApiService $api, AuditLogger $audit): int
    {
        /*
         * >>> GANTI SUMBER DATA DI SINI (tanpa ubah kode):
         * >>>  - URL API          : SEMBAKO_API_URL di .env
         * >>>  - Parameter query  : config/sembako.php -> 'query'
         * >>>  - Field response   : config/sembako.php -> 'response'
         * >>>  - Daftar komoditas : config/sembako.php -> 'komoditas'
         * Contoh sumber resmi: Panel Harga Badan Pangan Nasional (panelharga.badanpangan.go.id),
         * PIHPS Bank Indonesia (hargapangan.id), atau dataset data.go.id.
         */
        $days = max(1, min((int) ($this->option('days') ?: config('sembako.days_back')), 1095));
        $to   = now()->startOfDay();
        $from = $to->copy()->subDays($days);

        $saved = 0;
        $failed = 0;

        foreach (config('sembako.komoditas') as $item) {
            try {
                $rows = $api->fetchHistory($item, $from, $to);

                if ($rows === []) {
                    $this->warn("[{$item['kode']}] API tidak mengembalikan data valid, dilewati.");
                    continue;
                }

                $komoditas = Komoditas::updateOrCreate(
                    ['kode' => $item['kode']],
                    ['nama' => $item['nama'], 'satuan' => $item['satuan'], 'kategori' => $item['kategori'] ?? null]
                );

                $payload = array_map(fn ($r) => [
                    'komoditas_id' => $komoditas->id,
                    'tanggal'      => $r['tanggal'],
                    'harga'        => $r['harga'],
                    'sumber'       => config('sembako.sumber'),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ], $rows);

                // Eloquent upsert (parameter ter-bind, tanpa raw SQL)
                foreach (array_chunk($payload, 500) as $chunk) {
                    RiwayatHarga::upsert($chunk, ['komoditas_id', 'tanggal'], ['harga', 'sumber', 'updated_at']);
                }

                $saved += count($rows);
                $this->info("[{$item['kode']}] ".count($rows).' data tersimpan.');
            } catch (Throwable $e) {
                $failed++;
                $this->error("[{$item['kode']}] GAGAL: ".$e->getMessage());
            }
        }

        $audit->log('fetch.sembako', "Fetch API selesai: {$saved} baris, {$failed} komoditas gagal", [
            'days' => $days, 'saved' => $saved, 'failed' => $failed,
        ]);

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}