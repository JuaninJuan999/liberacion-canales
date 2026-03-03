<?php

namespace App\Http\Controllers;

use App\Models\MenuModulo;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index()
    {
        // Si no está autenticado, redirige al login
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Obtener todos los módulos ordenados
        $modulos = MenuModulo::ordenado()->get();

        return view('welcome-dashboard', compact('modulos'));
    }
}
