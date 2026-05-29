<?php

namespace App\Http\Middleware;

use App\Models\SesionUsuario;
use Closure;
use Illuminate\Http\Request;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        SesionUsuario::cerrarSesionesInactivas();

        if ($request->user()) {
            $sesionId = session('sesion_usuario_id');
            $sesion = $sesionId ? SesionUsuario::find($sesionId) : null;

            if ($sesion && $sesion->estaAbierta()) {
                $sesion->update(['ultima_actividad' => now()]);
            } else {
                $nueva = SesionUsuario::create([
                    'user_id' => $request->user()->id,
                    'login_at' => now(),
                    'ultima_actividad' => now(),
                    'ip_address' => $request->ip(),
                ]);
                session(['sesion_usuario_id' => $nueva->id]);
            }
        }

        return $next($request);
    }
}
