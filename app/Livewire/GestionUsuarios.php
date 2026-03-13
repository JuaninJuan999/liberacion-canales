<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
    public $username = '';
    public $password = '';
    public $rol_id;
    public $activo = true;

    // Búsqueda y filtrado
    public $buscar = '';
    public $filtro_rol = '';

    // Mensajes
    public $mensaje = '';
    public $tipoMensaje = 'success';

    // Modal contraseña temporal
    public $passwordTemporal = '';
    public $mostrarPasswordModal = false;
    public $usuarioPasswordNombre = '';

    // Edición inline
    public $editandoUsuarioId = null;
    public $editandoNombre = '';
    public $editandoEmail = '';
    public $editandoUsername = '';
    public $editandoRolId = '';

    protected $messages = [
        'nombre.required' => 'El nombre es obligatorio.',
        'email.required' => 'El email es obligatorio.',
        'email.email' => 'El email debe ser válido.',
        'username.required' => 'El nombre de usuario es obligatorio.',
        'username.unique' => 'Este nombre de usuario ya está registrado.',
        'username.min' => 'El nombre de usuario debe tener mínimo 3 caracteres.',
        'rol_id.required' => 'Debe seleccionar un rol.',
    ];

    protected function rules()
    {
        $rules = [
            'nombre' => 'required|string|min:3|max:255',
            'email' => 'required|email',
            'username' => 'required|string|min:3|max:255|regex:/^[a-z0-9._-]+$/',
            'rol_id' => 'required|exists:roles,id',
            'activo' => 'boolean',
        ];

        // Si estamos creando un usuario, la contraseña es obligatoria y username debe ser único
        if ($this->modo === 'crear') {
            $rules['username'] .= '|unique:users,username';
            $rules['email'] .= '|unique:users,email';
            $rules['password'] = 'required|string|min:8';
        }
        // Si estamos editando, validar username y email único pero excluyendo el usuario actual
        elseif ($this->modo === 'editar') {
            $rules['username'] .= '|unique:users,username,' . $this->usuario_id_editar;
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
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->buscar . '%')
                  ->orWhere('email', 'like', '%' . $this->buscar . '%')
                  ->orWhere('username', 'like', '%' . $this->buscar . '%');
            });
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

    public function updatedNombre()
    {
        // Generar sugerencia de username cuando el modo es crear
        if ($this->modo === 'crear' && !empty($this->nombre)) {
            $parts = explode(' ', trim($this->nombre));
            
            if (count($parts) >= 2) {
                $suggested = strtolower($parts[0] . '.' . $parts[1]);
            } else {
                $suggested = strtolower($parts[0]);
            }

            // Solo actualizar si el usuario aún no ha escrito manualmente
            if (empty($this->username)) {
                $this->username = $suggested;
            }
        }
    }

    public function mostrarFormularioCrear()
    {
        $this->reset(['nombre', 'email', 'username', 'password', 'rol_id', 'activo', 'usuario_id_editar']);
        $this->activo = true;
        $this->modo = 'crear';
        $this->dispatch('formulario-abierto');
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
        $this->username = $usuario->username;
        $this->rol_id = $usuario->rol_id;
        $this->activo = (bool) $usuario->activo;
        $this->password = '';
        $this->modo = 'editar';
    }

    public function guardar()
    {
        try {
            // Preparar campos antes de validar
            $this->nombre = trim($this->nombre);
            $this->email = strtolower(trim($this->email));
            $this->username = strtolower(trim($this->username));

            Log::info('GestionUsuarios guardar - Iniciando', ['modo' => $this->modo, 'usuario_id' => $this->usuario_id_editar ?? null]);

            // Validar
            $this->validate();

            if ($this->modo === 'crear') {
                Log::info('Creando nuevo usuario', ['nombre' => $this->nombre, 'email' => $this->email]);
                
                $usuario = User::create([
                    'name' => $this->nombre,
                    'email' => $this->email,
                    'username' => $this->username,
                    'password' => Hash::make($this->password),
                    'rol_id' => $this->rol_id,
                    'activo' => $this->activo,
                ]);
                
                if ($usuario) {
                    Log::info('Usuario creado exitosamente', ['id' => $usuario->id]);
                    $this->mostrarMensaje('Usuario creado exitosamente', 'success');
                    $this->cancelar();
                    $this->cargarDatos();
                } else {
                    Log::error('Error al crear usuario - create retornó null');
                    $this->mostrarMensaje('Error: No se pudo crear el usuario', 'error');
                }
            } elseif ($this->modo === 'editar') {
                // Verificar que el usuario existe
                $usuario = User::find($this->usuario_id_editar);
                if (!$usuario) {
                    Log::error('Usuario no encontrado para editar', ['id' => $this->usuario_id_editar]);
                    $this->mostrarMensaje('Usuario no encontrado', 'error');
                    return;
                }

                Log::info('Editando usuario', ['id' => $usuario->id, 'nombre' => $this->nombre, 'email' => $this->email]);

                // Actualizar usuario con los datos nuevos
                $datosActualizacion = [
                    'name' => $this->nombre,
                    'email' => $this->email,
                    'username' => $this->username,
                    'rol_id' => $this->rol_id,
                    'activo' => $this->activo,
                ];

                // Solo agregar contraseña si se proporciona una nueva
                if (!empty($this->password)) {
                    $datosActualizacion['password'] = Hash::make($this->password);
                }

                $filasActualizadas = $usuario->update($datosActualizacion);
                Log::info('Update ejecutado', ['filas' => $filasActualizadas, 'id' => $usuario->id]);

                // Verificar que realmente se guardó
                $usuarioVerificado = User::find($this->usuario_id_editar);
                Log::info('Usuario después del update', ['nombre_bd' => $usuarioVerificado->name, 'nombre_env' => $this->nombre]);

                $this->mostrarMensaje('Usuario actualizado exitosamente', 'success');
                $this->cancelar();
                $this->cargarDatos();
            }
        } catch (\Exception $e) {
            Log::error('Error en GestionUsuarios guardar:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'modo' => $this->modo,
                'usuario_id' => $this->usuario_id_editar ?? null
            ]);
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
            
            // Recargar datos después de la acción
            $this->cargarDatos();
            $this->dispatch('accion-completada');
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
            
            // Recargar datos después de la acción
            $this->cargarDatos();
            $this->dispatch('accion-completada');
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

            $this->passwordTemporal = $contrasenaTemporal;
            $this->usuarioPasswordNombre = $usuario->name;
            $this->mostrarPasswordModal = true;
            
            $this->dispatch('contrasena-restablecida');
        } catch (\Exception $e) {
            $this->mostrarMensaje('Error: ' . $e->getMessage(), 'error');
        }
    }

    public function cancelar()
    {
        $this->modo = 'vista';
        $this->reset(['nombre', 'email', 'username', 'password', 'rol_id', 'activo', 'usuario_id_editar']);
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

    public function cerrarPasswordModal()
    {
        $this->mostrarPasswordModal = false;
        $this->passwordTemporal = '';
        $this->usuarioPasswordNombre = '';
    }

    public function iniciarEdicionInline($userId)
    {
        $usuario = User::find($userId);
        if (!$usuario) {
            $this->mostrarMensaje('Usuario no encontrado', 'error');
            return;
        }

        $this->editandoUsuarioId = $userId;
        $this->editandoNombre = $usuario->name;
        $this->editandoEmail = $usuario->email;
        $this->editandoUsername = $usuario->username;
        $this->editandoRolId = $usuario->rol_id;
    }

    public function cancelarEdicionInline()
    {
        $this->reset(['editandoUsuarioId', 'editandoNombre', 'editandoEmail', 'editandoUsername', 'editandoRolId']);
        $this->dispatch('edicion-cancelada');
    }

    public function guardarEdicionInline()
    {
        try {
            Log::info('Iniciando guardarEdicionInline', [
                'usuario_id' => $this->editandoUsuarioId,
                'nombre' => $this->editandoNombre,
                'email' => $this->editandoEmail,
                'username' => $this->editandoUsername,
                'rol_id' => $this->editandoRolId
            ]);

            // Preparar datos
            $this->editandoNombre = trim($this->editandoNombre);
            $this->editandoEmail = strtolower(trim($this->editandoEmail));
            $this->editandoUsername = strtolower(trim($this->editandoUsername));

            // Validar datos
            $this->validate([
                'editandoNombre' => 'required|string|min:3|max:255',
                'editandoEmail' => 'required|email|unique:users,email,' . $this->editandoUsuarioId,
                'editandoUsername' => 'required|string|min:3|max:255|regex:/^[a-z0-9._-]+$/|unique:users,username,' . $this->editandoUsuarioId,
                'editandoRolId' => 'required|exists:roles,id',
            ]);

            Log::info('Validación pasada');

            $usuario = User::find($this->editandoUsuarioId);
            if (!$usuario) {
                Log::error('Usuario no encontrado', ['id' => $this->editandoUsuarioId]);
                $this->mostrarMensaje('Usuario no encontrado', 'error');
                return;
            }

            Log::info('Actualizando usuario', ['id' => $usuario->id]);

            $resultado = $usuario->update([
                'name' => $this->editandoNombre,
                'email' => $this->editandoEmail,
                'username' => $this->editandoUsername,
                'rol_id' => (int)$this->editandoRolId,
            ]);

            Log::info('Resultado del update', ['resultado' => $resultado]);

            // Verificar que se guardó
            $usuarioActualizado = User::find($this->editandoUsuarioId);
            Log::info('Usuario después del update', [
                'nombre_bd' => $usuarioActualizado->name,
                'nombre_env' => $this->editandoNombre,
                'email_bd' => $usuarioActualizado->email
            ]);

            $this->mostrarMensaje('Usuario actualizado exitosamente', 'success');
            
            // Reset completo del estado de edición
            $this->reset(['editandoUsuarioId', 'editandoNombre', 'editandoEmail', 'editandoUsername', 'editandoRolId']);
            $this->cargarDatos();
            $this->dispatch('usuario-guardado');
            
        } catch (\Exception $e) {
            Log::error('Error en edición inline', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'usuario_id' => $this->editandoUsuarioId
            ]);
            $this->mostrarMensaje('Error al guardar: ' . $e->getMessage(), 'error');
        }
    }

    public function render()
    {
        return view('livewire.gestion-usuarios')
            ->layout('layouts.app');
    }
}
