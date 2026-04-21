<?php

namespace App\Http\Controllers;

use App\Models\MenuModulo;

class WelcomeController extends Controller
{
    public function index()
    {
        // Si no está autenticado, redirige al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $usuario = auth()->user();
        $rolUsuario = $usuario->rolNormalizado();

        $modulos = MenuModulo::ordenado()
            ->get()
            ->filter(fn (MenuModulo $modulo) => $modulo->visibleParaRol($rolUsuario))
            ->values();

        return view('welcome-dashboard', compact('modulos', 'rolUsuario'));
    }
}
