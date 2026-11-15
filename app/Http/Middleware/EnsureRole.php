<?php

namespace App\Http\Middleware;

use App\Services\AuditLogger;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /** Pemakaian: ->middleware('role:admin') atau 'role:user,admin' */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active || ! in_array($user->role, $roles, true)) {
            app(AuditLogger::class)->log('rbac.denied', 'Akses ditolak ke /'.$request->path(), [
                'required_roles' => $roles,
                'method'         => $request->method(),
            ]);

            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}
