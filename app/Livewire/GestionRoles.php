<?php

namespace App\Livewire;

use App\Models\MenuModulo;
use App\Models\Rol;
use Illuminate\Validation\Rule;
use Livewire\Component;

class GestionRoles extends Component
{
    public string $vista = 'lista';

    public ?int $editandoId = null;

    public string $nombre = '';

    /** IDs de menu_modulos visibles para el rol en edición/creación */
    public array $modulosSeleccionados = [];

    public string $mensaje = '';

    public string $tipoMensaje = 'success';

    public function mount(): void
    {
        if (! auth()->check()) {
            abort(401);
        }

        $usuario = auth()->user();
        if (! $usuario->rol || $usuario->rol->nombre !== 'ADMINISTRADOR') {
            abort(403, 'Se requiere rol ADMINISTRADOR.');
        }
    }

    public function getRolesListProperty()
    {
        return Rol::withCount('users')->orderBy('nombre')->get();
    }

    public function getModulosMenuProperty()
    {
        return MenuModulo::ordenado()->get();
    }

    private function flash(string $msg, string $tipo = 'success'): void
    {
        $this->mensaje = $msg;
        $this->tipoMensaje = $tipo;
    }

    private function resetFormulario(): void
    {
        $this->reset(['nombre', 'editandoId', 'modulosSeleccionados']);
        $this->resetValidation();
    }

    public function abrirCrear(): void
    {
        $this->resetFormulario();
        $this->modulosSeleccionados = [];
        $this->vista = 'crear';
    }

    public function abrirEditar(int $id): void
    {
        $rol = Rol::find($id);
        if (! $rol) {
            $this->flash('Rol no encontrado.', 'error');

            return;
        }

        $this->resetFormulario();
        $this->vista = 'editar';
        $this->editandoId = $rol->id;
        $this->nombre = $rol->nombre;
        $this->modulosSeleccionados = MenuModulo::idsModulosParaRol($rol);
    }

    public function volver(): void
    {
        $this->resetFormulario();
        $this->vista = 'lista';
    }

    public function guardar(): void
    {
        $this->nombre = strtoupper(trim($this->nombre));

        $rules = [
            'nombre' => ['required', 'string', 'min:2', 'max:255'],
            'modulosSeleccionados' => 'array',
            'modulosSeleccionados.*' => 'integer|exists:menu_modulos,id',
        ];

        if ($this->vista === 'crear') {
            $rules['nombre'][] = Rule::unique('roles', 'nombre');
        } else {
            $rules['nombre'][] = Rule::unique('roles', 'nombre')->ignore($this->editandoId);
        }

        $this->validate($rules, [
            'nombre.required' => 'El nombre del rol es obligatorio.',
            'nombre.unique' => 'Ya existe un rol con ese nombre.',
        ]);

        if ($this->vista === 'crear') {
            $rol = Rol::create(['nombre' => $this->nombre]);
            MenuModulo::sincronizarModulosParaRol($rol, $this->modulosSeleccionados);
            $this->flash('Rol creado y módulos actualizados.');
            $this->resetFormulario();
            $this->vista = 'lista';

            return;
        }

        $rol = Rol::findOrFail($this->editandoId);
        $normAntes = Rol::normalizarNombre($rol->nombre);
        $normNuevo = Rol::normalizarNombre($this->nombre);

        if ($normAntes !== $normNuevo) {
            MenuModulo::renombrarRolEnMenus($normAntes, $normNuevo);
        }

        $rol->update(['nombre' => $this->nombre]);
        MenuModulo::sincronizarModulosParaRol($rol->fresh(), $this->modulosSeleccionados);

        $this->flash('Rol actualizado y permisos de módulos guardados.');
    }

    public function eliminar(int $id): void
    {
        $rol = Rol::find($id);
        if (! $rol) {
            $this->flash('Rol no encontrado.', 'error');

            return;
        }

        if (Rol::normalizarNombre($rol->nombre) === 'ADMINISTRADOR') {
            $this->flash('No se puede eliminar el rol ADMINISTRADOR.', 'error');

            return;
        }

        if ($rol->users()->exists()) {
            $this->flash('No se puede eliminar: hay usuarios asignados a este rol.', 'error');

            return;
        }

        MenuModulo::quitarRolDeTodosLosMenus(Rol::normalizarNombre($rol->nombre));
        $nombre = $rol->nombre;
        $rol->delete();

        $this->flash("Rol {$nombre} eliminado.");
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    public function render()
    {
        return view('livewire.gestion-roles')->layout('layouts.app');
    }
}
