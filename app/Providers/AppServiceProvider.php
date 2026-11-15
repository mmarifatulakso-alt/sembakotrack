<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Watchlist;
use App\Policies\WatchlistPolicy;
use App\Services\AuditLogger;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // 0. Paksa penggunaan HTTPS saat di Hosting (Render)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // 1. Kebijakan Otorisasi (Policy & RBAC Gate Admin)
        Gate::policy(Watchlist::class, WatchlistPolicy::class);
        Gate::define('admin', function (User $user) {
            return $user->role === 'admin';
        });

        // 2. Rate Limiter untuk Login
        RateLimiter::for('login', function (Request $request) {
            $key = strtolower((string) $request->input('email')) . '|' . $request->ip();

            return Limit::perMinute(5)->by($key)->response(function (Request $request, array $headers) {
                if (class_exists(AuditLogger::class)) {
                    app(AuditLogger::class)->log('rate_limit.login', 'Rate limit login terpicu dari IP: ' . $request->ip());
                }
                return back()
                    ->withErrors(['email' => 'Terlalu banyak percobaan masuk. Silakan tunggu 1 menit lagi.'])
                    ->withInput($request->only('email'));
            });
        });

        // 3. Rate Limiter untuk API Grafik, Dashboard, Register, Watchlist, & Umum
        $standardlimiters = ['api', 'chart', 'chart-data', 'harga', 'dashboard', 'public', 'global', 'watchlist', 'security', 'password', 'export', 'admin'];
        foreach ($standardlimiters as $limiterName) {
            RateLimiter::for($limiterName, function (Request $request) {
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });
        }

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // 4. View Composer: Data Notifikasi Target Harga untuk Layout Utama
        View::composer('layouts.app', function ($view) {
            $webNotifTercapai = collect();
            $notifJson = '[]';

            if (Auth::check() && Auth::user()->role !== 'admin') {
                $webNotifItems = Watchlist::with('komoditas.latestRiwayat')
                    ->where('user_id', Auth::id())
                    ->get();

                $webNotifTercapai = $webNotifItems->filter(function ($w) {
                    $hrg = $w->komoditas?->latestRiwayat?->harga;
                    return $hrg !== null && $hrg <= $w->target_harga;
                })->values();

                $notifPayload = [];
                foreach ($webNotifTercapai as $w) {
                    $notifPayload[] = [
                        'nama'   => $w->komoditas?->nama ?? 'Komoditas',
                        'harga'  => number_format($w->komoditas?->latestRiwayat?->harga ?? 0, 0, ',', '.'),
                        'satuan' => $w->komoditas?->satuan ?? 'kg',
                        'target' => number_format($w->target_harga, 0, ',', '.'),
                    ];
                }
                $notifJson = json_encode($notifPayload);
            }

            $view->with([
                'webNotifTercapai' => $webNotifTercapai,
                'notifJson'        => $notifJson,
            ]);
        });
    }
}