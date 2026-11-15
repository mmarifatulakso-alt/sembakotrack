<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Komoditas;
use App\Models\User;
use App\Models\Watchlist;
use App\Services\AuditLogger;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin = pemantau keamanan & lalu lintas. TIDAK ada fitur mengubah harga di sini.
 */
class AdminController extends Controller
{
    public function __construct(private AuditLogger $audit) {}

    public function dashboard(): View
    {
        $since = now()->subDay();
        $count = fn (string $event) => AuditLog::where('event', $event)->where('created_at', '>=', $since)->count();

        $stats = [
            'total_user'      => User::where('role', 'user')->count(),
            'total_watchlist' => Watchlist::count(),
            'login_gagal'     => $count('auth.login.failed'),
            'lockout'         => $count('auth.lockout'),
            'idor_diblokir'   => $count('idor.blocked'),
            'akses_ditolak'   => $count('rbac.denied'),
        ];

        $statistik = $this->statistikPantauan();
        $logs = AuditLog::with('user:id,name,email')->latest('id')->limit(15)->get();

        return view('admin.dashboard', compact('stats', 'statistik', 'logs'));
    }

    public function audit(Request $request): View
    {
        $filter = $request->validate(['event' => ['nullable', 'string', 'max:60']]);

        $logs = AuditLog::with('user:id,name,email')
            ->when($filter['event'] ?? null, fn ($q, $e) => $q->where('event', $e))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $events = AuditLog::select('event')->distinct()->orderBy('event')->pluck('event');

        return view('admin.audit', compact('logs', 'events'));
    }

   public function exportCsv(): StreamedResponse
    {
        $rows = $this->statistikPantauan();
        $this->audit->log('admin.export', 'Ekspor CSV Statistik Pantauan User');

        $filename = 'statistik-pantauan-'.now()->timezone('Asia/Jakarta')->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM agar Excel/WPS membaca UTF-8
            fputcsv($out, ['Komoditas', 'Satuan', 'Jumlah Pemantau', 'Rata-rata Target Harga', 'Harga Terakhir', 'Tanggal Harga Terakhir'], ';', '"', '\\');

            foreach ($rows as $k) {
                fputcsv($out, array_map([$this, 'csvSafe'], [
                    $k->nama,
                    $k->satuan,
                    $k->watchlists_count,
                    round((float) $k->watchlists_avg_target_harga),
                    $k->latestRiwayat?->harga ?? 0,
                    $k->latestRiwayat?->tanggal?->format('Y-m-d') ?? '-',
                ]), ';', '"', '\\');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportPdf()
    {
        $rows = $this->statistikPantauan();
        $this->audit->log('admin.export', 'Ekspor PDF Statistik Pantauan User');

        $pdf = Pdf::loadView('admin.export_pdf', [
            'rows'    => $rows,
            'dibuat'  => now()->translatedFormat('d F Y H:i'),
            'totalU'  => User::where('role', 'user')->count(),
            'totalW'  => Watchlist::count(),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('statistik-pantauan-'.now()->format('Ymd-His').'.pdf');
    }

    /** Agregat per komoditas via Eloquent (tanpa data pribadi per user). */
    private function statistikPantauan(): Collection
    {
        return Komoditas::query()
            ->withCount('watchlists')
            ->withAvg('watchlists', 'target_harga')
            ->with('latestRiwayat')
            ->orderByDesc('watchlists_count')
            ->orderBy('nama')
            ->get();
    }

    /** Cegah CSV/formula injection: sel berawalan = + - @ diberi tanda kutip. */
    private function csvSafe(mixed $value): string
    {
        $v = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $v) ? "'".$v : $v;
    }
}