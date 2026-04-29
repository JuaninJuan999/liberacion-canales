<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;

class PermisosVerificadores extends Component
{
    public string $mensaje = '';

    public string $tipoMensaje = 'success';

    public function mount(): void
    {
        $usuario = auth()->user();
        if (! $usuario || ! $usuario->rol || $usuario->rol->nombre !== 'ADMINISTRADOR') {
            abort(403, 'Se requiere rol ADMINISTRADOR.');
        }
    }

    public function toggleVerificador(int $userId): void
    {
        $target = User::query()->findOrFail($userId);
        $target->puede_verificar_titulacion = ! $target->puede_verificar_titulacion;
        $target->save();

        $this->mensaje = $target->puede_verificar_titulacion
            ? "{$target->name} puede aparecer como verificador en titulación."
            : "Se quitó a {$target->name} de la lista de verificadores.";
        $this->tipoMensaje = 'success';
    }

    public function render()
    {
        $usuarios = User::with('rol')->orderBy('name')->get();

        return view('livewire.permisos-verificadores', [
            'usuarios' => $usuarios,
        ])->layout('layouts.app');
    }
}
