<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->user()?->rol?->nombre === 'ADMINISTRADOR') {
            return $next($request);
        }

        abort(403, 'Acceso no autorizado');
    }
}
