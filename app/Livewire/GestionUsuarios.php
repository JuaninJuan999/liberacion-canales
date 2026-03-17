<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class GestionUsuarios extends Component
{
    // Vista actual: lista | crear | editar
    public string $vista = 'lista';
    public ?int $editandoId = null;

    // Campos del formulario
    public string $nombre = '';
    public string $apellido = '';
    public string $email = '';
    public string $username = '';
    public string $password = '';
    public string $password_confirmation = '';
    public $rol_id = '';
    public bool $activo = true;

    // Contraseña en edición (sección separada)
    public string $nuevaContrasena = '';

    // Búsqueda
    public string $buscar = '';

    // Feedback
    public string $mensaje = '';
    public string $tipoMensaje = 'success';

    public function mount()
    {
        if (!auth()->check()) {
            abort(401);
        }

        $usuario = auth()->user();
        if (!$usuario->rol || $usuario->rol->nombre !== 'ADMINISTRADOR') {
            abort(403, 'Se requiere rol ADMINISTRADOR.');
        }
    }

    // ─── Computed Properties ─────────────────────────────

    public function getRolesProperty()
    {
        return Rol::orderBy('nombre')->get();
    }

    public function getUsuariosProperty()
    {
        $query = User::with('rol');

        if ($this->buscar !== '') {
            $buscar = $this->buscar;
            $query->where(function ($q) use ($buscar) {
                $q->where('name', 'ilike', "%{$buscar}%")
                  ->orWhere('email', 'ilike', "%{$buscar}%")
                  ->orWhere('username', 'ilike', "%{$buscar}%");
            });
        }

        return $query->orderBy('name')->get();
    }

    public function getUsuarioEditandoProperty()
    {
        if ($this->editandoId) {
            return User::with('rol')->find($this->editandoId);
        }
        return null;
    }

    // ─── Helpers ─────────────────────────────────────────

    private function flash(string $msg, string $tipo = 'success'): void
    {
        $this->mensaje = $msg;
        $this->tipoMensaje = $tipo;
    }

    private function resetFormulario(): void
    {
        $this->reset(['nombre', 'apellido', 'email', 'username', 'password', 'password_confirmation', 'rol_id', 'activo', 'editandoId', 'nuevaContrasena']);
        $this->activo = true;
        $this->resetValidation();
    }

    private function generarUsername(): string
    {
        $n = preg_replace('/[^a-z0-9]/', '', Str::ascii(strtolower(trim($this->nombre))));
        $a = preg_replace('/[^a-z0-9]/', '', Str::ascii(strtolower(trim($this->apellido))));
        if ($n !== '' && $a !== '') {
            return $n . '.' . $a;
        }
        return $n ?: $a;
    }

    // ─── Navegación ──────────────────────────────────────

    public function abrirCrear(): void
    {
        $this->resetFormulario();
        $this->vista = 'crear';
    }

    public function abrirEditar(int $id): void
    {
        $usuario = User::find($id);
        if (!$usuario) {
            $this->flash('Usuario no encontrado.', 'error');
            return;
        }

        $this->resetFormulario();
        $this->vista = 'editar';
        $this->editandoId = $usuario->id;

        // Separar name en nombre y apellido
        $partes = explode(' ', $usuario->name, 2);
        $this->nombre = $partes[0] ?? '';
        $this->apellido = $partes[1] ?? '';

        $this->email = $usuario->email ?? '';
        $this->username = $usuario->username ?? '';
        $this->rol_id = (string) $usuario->rol_id;
        $this->activo = (bool) $usuario->activo;
    }

    public function volver(): void
    {
        $this->resetFormulario();
        $this->vista = 'lista';
    }

    // ─── Auto-generar username ───────────────────────────

    public function updatedNombre(): void
    {
        if ($this->vista === 'crear') {
            $this->username = $this->generarUsername();
        }
    }

    public function updatedApellido(): void
    {
        if ($this->vista === 'crear') {
            $this->username = $this->generarUsername();
        }
    }

    // ─── Guardar (crear / editar) ────────────────────────

    public function guardar(): void
    {
        $this->nombre = trim($this->nombre);
        $this->apellido = trim($this->apellido);
        $this->username = strtolower(trim($this->username));

        // Auto-generar email en modo crear
        if ($this->vista === 'crear') {
            $this->email = $this->username !== '' ? $this->username . '@sistema.local' : '';
        } else {
            $this->email = strtolower(trim($this->email));
        }

        $rules = [
            'nombre'   => 'required|string|min:2|max:255',
            'apellido' => 'required|string|min:2|max:255',
            'rol_id'   => 'required|exists:roles,id',
        ];

        if ($this->vista === 'crear') {
            $rules['username'] = ['required', 'string', 'min:3', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')];
            $rules['password'] = 'required|string|min:6|confirmed';
        } else {
            $rules['email'] = ['required', 'email', Rule::unique('users', 'email')->ignore($this->editandoId)];
            $rules['username'] = ['required', 'string', 'min:3', 'max:255', 'regex:/^[a-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($this->editandoId)];
        }

        $this->validate($rules, [
            'nombre.required'   => 'El nombre es obligatorio.',
            'apellido.required' => 'El apellido es obligatorio.',
            'email.required'    => 'El email es obligatorio.',
            'email.email'       => 'Email inválido.',
            'email.unique'      => 'Este email ya está registrado.',
            'username.required' => 'El usuario es obligatorio.',
            'username.unique'   => 'Este usuario ya existe.',
            'username.regex'    => 'Solo minúsculas, números, puntos y guiones.',
            'rol_id.required'   => 'Seleccione un rol.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min'      => 'Mínimo 6 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $fullName = $this->nombre . ' ' . $this->apellido;

        if ($this->vista === 'crear') {
            User::create([
                'name'     => $fullName,
                'email'    => $this->email,
                'username' => $this->username,
                'password' => Hash::make($this->password),
                'rol_id'   => (int) $this->rol_id,
                'activo'   => $this->activo,
            ]);
            $this->flash('Usuario creado exitosamente.');
            $this->resetFormulario();
            $this->vista = 'lista';
        } else {
            $usuario = User::find($this->editandoId);
            if (!$usuario) {
                $this->flash('Usuario no encontrado.', 'error');
                return;
            }

            $usuario->update([
                'name'     => $fullName,
                'email'    => $this->email,
                'username' => $this->username,
                'rol_id'   => (int) $this->rol_id,
                'activo'   => $this->activo,
            ]);

            $this->flash('Usuario actualizado exitosamente.');
        }
    }

    // ─── Restablecer contraseña (sección en editar) ──────

    public function guardarContrasena(): void
    {
        $this->validate([
            'nuevaContrasena' => 'required|string|min:6',
        ], [
            'nuevaContrasena.required' => 'La contraseña es obligatoria.',
            'nuevaContrasena.min'      => 'Mínimo 6 caracteres.',
        ]);

        $usuario = User::find($this->editandoId);
        if (!$usuario) {
            $this->flash('Usuario no encontrado.', 'error');
            return;
        }

        $usuario->password = Hash::make($this->nuevaContrasena);
        $usuario->save();

        $this->nuevaContrasena = '';
        $this->flash('Contraseña actualizada exitosamente.');
    }

    // ─── Acciones de tabla ───────────────────────────────

    public function toggleActivo(int $id): void
    {
        $usuario = User::find($id);
        if (!$usuario) {
            $this->flash('Usuario no encontrado.', 'error');
            return;
        }

        $usuario->activo = !$usuario->activo;
        $usuario->save();

        $this->flash($usuario->activo ? 'Usuario activado.' : 'Usuario desactivado.');
    }

    public function limpiarMensaje(): void
    {
        $this->mensaje = '';
    }

    // ─── Render ──────────────────────────────────────────

    public function render()
    {
        return view('livewire.gestion-usuarios')->layout('layouts.app');
    }
}
