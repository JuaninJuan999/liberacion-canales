<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SesionUsuario;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $sesionId = session('sesion_usuario_id');

            if ($sesionId) {
                SesionUsuario::where('id', $sesionId)
                    ->whereNull('logout_at')
                    ->update(['ultima_actividad' => now()]);
            }
        }

        return $next($request);
    }
}
