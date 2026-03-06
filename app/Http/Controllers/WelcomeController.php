<?php

namespace App\Http\Controllers;

use App\Models\MenuModulo;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    private function normalizarRol(?string $rol): string
    {
        $rolNormalizado = strtoupper(trim((string) $rol));
        return $rolNormalizado === 'ADMIN' ? 'ADMINISTRADOR' : $rolNormalizado;
    }

    public function index()
    {
        // Si no está autenticado, redirige al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $rolUsuario = $this->normalizarRol(auth()->user()?->rol?->nombre);

        // Mostrar solo módulos permitidos para el rol autenticado
        $modulos = MenuModulo::ordenado()->get()->filter(function ($modulo) use ($rolUsuario) {
            $rolesPermitidos = array_map(function ($rol) {
                return $this->normalizarRol($rol);
            }, $modulo->roles ?? []);

            return in_array($rolUsuario, $rolesPermitidos, true);
        })->values();

        return view('welcome-dashboard', compact('modulos'));
    }
}
