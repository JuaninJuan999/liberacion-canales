<?php

namespace App\Livewire\Concerns;

use App\Models\MenuModulo;

/**
 * Autorización alineada con Gestión de roles / menu_modulos (misma regla que menú lateral).
 */
trait AuthorizaPorMenuModulo
{
    protected function autorizarVistaMenu(string $nombreRuta): void
    {
        if (! auth()->check()) {
            abort(401, 'Debes estar autenticado.');
        }

        $rolUsuario = auth()->user()->rolNormalizado();

        $modulo = MenuModulo::where('vista', $nombreRuta)->first();

        if (! $modulo || ! $modulo->visibleParaRol($rolUsuario)) {
            abort(403, 'No tienes permiso para acceder a este módulo.');
        }
    }
}
