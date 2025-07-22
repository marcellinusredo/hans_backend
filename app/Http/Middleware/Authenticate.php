<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Override untuk mendukung token dari query (?token=xxx)
     */
    protected function authenticate($request, array $guards)
    {
        // Ambil token dari query jika Authorization header kosong
        if (!$request->bearerToken() && $request->has('token')) {
            $request->headers->set('Authorization', 'Bearer ' . $request->get('token'));
        }

        parent::authenticate($request, $guards);
    }

    /**
     * Override untuk respon default saat tidak login
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            abort(401, 'Unauthenticated');
        }
    }
}
