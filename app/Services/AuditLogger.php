<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuditLogger
{
    /**
     * Catat aktivitas keamanan. JANGAN pernah memasukkan password/token ke $metadata.
     */
    public function log(string $event, string $description, array $metadata = [], ?int $userId = null): void
    {
        try {
            $console = app()->runningInConsole();
            $request = request();

            AuditLog::create([
                'user_id'     => $userId ?? Auth::id(),
                'event'       => $event,
                'description' => Str::limit($description, 255, ''),
                'ip_address'  => $console ? null : $request->ip(),
                'user_agent'  => $console ? 'artisan' : Str::limit((string) $request->userAgent(), 255, ''),
                'metadata'    => $metadata ?: null,
            ]);
        } catch (\Throwable $e) {
            report($e); // kegagalan logging tidak boleh menjatuhkan request
        }
    }
}