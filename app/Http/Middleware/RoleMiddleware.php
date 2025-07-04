<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user || !$user->role) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $userRole = $user->role->nama_role;

        Log::info('RoleMiddleware check', [
            'user_id' => $user->id_staff ?? null,
            'username' => $user->username ?? null,
            'user_role' => $userRole,
            'expected_roles' => $roles,
        ]);

        // Jika Super Admin, izinkan akses ke semua route
        if ($userRole === 'Super Admin') {
            return $next($request);
        }

        // Hanya lanjut jika role user sesuai yang diminta di route
        if (!in_array($userRole, $roles)) {
            return response()->json(['message' => 'Forbidden: akses ditolak'], 403);
        }

        return $next($request);
    }
}
