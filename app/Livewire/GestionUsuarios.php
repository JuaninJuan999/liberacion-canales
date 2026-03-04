<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GestionUsuarios extends Component
{
    public $usuarios = [];
    public $roles = [];
    
    // Form Data
    public $modo = 'vista'; // vista, crear, editar
    public $usuario_id_editar;
    public $nombre = '';
    public $email = '';
    public $password = '';
    public $rol_id;
    public $activo = true;

    // Búsqueda y filtrado
    public $buscar = '';
    public $filtro_rol = '';

    // Mensajes
    public $mensaje = '';
    public $tipoMensaje = 'success';

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'email.required' => 'El email es obligatorio.',
        'email.email' => 'El email debe ser válido.',
        'email.unique' => 'Este email ya está registrado.',
        'rol_id.required' => 'Debe seleccionar un rol.',
    ];

    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|min:3|max:255',
            'email' => 'required|email',
            'rol_id' => 'required|exists:roles,id',
            'activo' => 'boolean',
        ];

        // Si estamos creando un usuario, la contraseña es obligatoria
        if ($this->modo === 'crear') {
            $rules['email'] .= '|unique:users,email';
            $rules['password'] = 'required|string|min:8';
        }
        // Si estamos editando, validar email único pero no el del usuario actual
        elseif ($this->modo === 'editar') {
            $rules['email'] .= '|unique:users,email,' . $this->usuario_id_editar;
        }

        return $rules;
    }

    public function mount()
    {
        // Verificar que solo admin puede acceder
        if (!auth()->check()) {
            abort(401, 'Debes estar autenticado.');
        }

        $usuario = auth()->user();
        
        if (!$usuario->rol || $usuario->rol->nombre !== 'ADMINISTRADOR') {
            abort(403, 'No tienes permiso para acceder a este módulo. Se requiere rol ADMINISTRADOR.');
        }

        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $query = User::with('rol');

        if ($this->buscar) {
            $query->where('name', 'like', '%' . $this->buscar . '%')
                  ->orWhere('email', 'like', '%' . $this->buscar . '%');
        }

        if ($this->filtro_rol) {
            $query->where('rol_id', $this->filtro_rol);
        }

        $this->usuarios = $query->orderBy('name')->get();
        $this->roles = Rol::orderBy('nombre')->get();
    }

    public function updatedBuscar()
    {
        $this->cargarDatos();
    }

    public function updatedFiltroRol()
    {
        $this->cargarDatos();
    }

    public function mostrarFormularioCrear()
    {
        $this->reset(['nombre', 'email', 'password', 'rol_id', 'activo', 'usuario_id_editar']);
        $this->activo = true;
        $this->modo = 'crear';
    }

    public function mostrarFormularioEditar($userId)
    {
        $usuario = User::find($userId);
        if (!$usuario) {
            $this->mostrarMensaje('Usuario no encontrado', 'error');
            return;
        }

        $this->usuario_id_editar = $usuario->id;
        $this->nombre = $usuario->name;
        $this->email = $usuario->email;
        $this->rol_id = $usuario->rol_id;
        $this->activo = (bool) $usuario->activo;
        $this->password = '';
        $this->modo = 'editar';
    }

    public function guardar()
    {
        $this->validate();

        try {
            if ($this->modo === 'crear') {
                User::create([
                    'name' => $this->nombre,
                    'email' => $this->email,
                    'password' => Hash::make($this->password),
                    'rol_id' => $this->rol_id,
                    'activo' => $this->activo,
                ]);
                $this->mostrarMensaje('Usuario creado exitosamente', 'success');
            } elseif ($this->modo === 'editar') {
                $usuario = User::find($this->usuario_id_editar);
                $usuario->name = $this->nombre;
                $usuario->email = $this->email;
                $usuario->rol_id = $this->rol_id;
                $usuario->activo = $this->activo;

                // Solo actualizar contraseña si se proporciona una nueva
                if ($this->password) {
                    $usuario->password = Hash::make($this->password);
                }

                $usuario->save();
                $this->mostrarMensaje('Usuario actualizado exitosamente', 'success');
            }

            $this->cancelar();
            $this->cargarDatos();
        } catch (\Exception $e) {
            $this->mostrarMensaje('Error: ' . $e->getMessage(), 'error');
        }
    }

    public function restablecerContrasena($userId)
    {
        try {
            $usuario = User::find($userId);
            if (!$usuario) {
                $this->mostrarMensaje('Usuario no encontrado', 'error');
                return;
            }

            // Generar contraseña temporal
            $contrasenaTemporal = Str::random(12);
            $usuario->password = Hash::make($contrasenaTemporal);
            $usuario->save();

            $this->mostrarMensaje("Contraseña restablecida: $contrasenaTemporal (Cópiala y comparte con el usuario)", 'success');
        } catch (\Exception $e) {
            $this->mostrarMensaje('Error: ' . $e->getMessage(), 'error');
        }
    }

    public function desactivarActivar($userId)
    {
        try {
            $usuario = User::find($userId);
            if (!$usuario) {
                $this->mostrarMensaje('Usuario no encontrado', 'error');
                return;
            }

            $usuario->activo = !$usuario->activo;
            $usuario->save();

            $mensaje = $usuario->activo ? 'Usuario activado' : 'Usuario desactivado';
            $this->mostrarMensaje($mensaje, 'success');
            $this->cargarDatos();
        } catch (\Exception $e) {
            $this->mostrarMensaje('Error: ' . $e->getMessage(), 'error');
        }
    }

    public function eliminar($userId)
    {
        try {
            $usuario = User::find($userId);
            if (!$usuario) {
                $this->mostrarMensaje('Usuario no encontrado', 'error');
                return;
            }

            // No permitir eliminar al usuario actual
            if ($usuario->id === auth()->id()) {
                $this->mostrarMensaje('No puedes eliminar tu propia cuenta', 'error');
                return;
            }

            $usuario->delete();
            $this->mostrarMensaje('Usuario eliminado exitosamente', 'success');
            $this->cargarDatos();
        } catch (\Exception $e) {
            $this->mostrarMensaje('Error: ' . $e->getMessage(), 'error');
        }
    }

    public function cancelar()
    {
        $this->modo = 'vista';
        $this->reset(['nombre', 'email', 'password', 'rol_id', 'activo', 'usuario_id_editar']);
    }

    public function mostrarMensaje($msg, $tipo)
    {
        $this->mensaje = $msg;
        $this->tipoMensaje = $tipo;
    }

    public function limpiarMensaje()
    {
        $this->mensaje = '';
    }

    public function render()
    {
        return view('livewire.gestion-usuarios')
            ->layout('layouts.app');
    }
}
