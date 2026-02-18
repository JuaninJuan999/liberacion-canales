<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UsuarioController extends Controller
{
    /**
     * Mostrar listado de usuarios
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $rol = $request->get('rol');
        
        $usuarios = User::with('rol')
            ->when($search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($rol, function($query, $rol) {
                $query->where('rol_id', $rol);
            })
            ->orderBy('name', 'asc')
            ->paginate(20);
        
        $roles = Rol::all();
        
        return view('usuarios.index', compact('usuarios', 'roles'));
    }
    
    /**
     * Mostrar formulario para crear usuario
     */
    public function create()
    {
        $roles = Rol::all();
        return view('usuarios.create', compact('roles'));
    }
    
    /**
     * Guardar nuevo usuario
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'rol_id' => ['required', 'exists:roles,id'],
            'activo' => ['boolean']
        ]);
        
        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol_id' => $request->rol_id,
            'activo' => $request->has('activo')
        ]);
        
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario creado exitosamente');
    }
    
    /**
     * Mostrar detalles de un usuario
     */
    public function show(User $usuario)
    {
        $usuario->load('rol');
        return view('usuarios.show', compact('usuario'));
    }
    
    /**
     * Mostrar formulario para editar usuario
     */
    public function edit(User $usuario)
    {
        $roles = Rol::all();
        return view('usuarios.edit', compact('usuario', 'roles'));
    }
    
    /**
     * Actualizar usuario
     */
    public function update(Request $request, User $usuario)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'rol_id' => ['required', 'exists:roles,id'],
            'activo' => ['boolean']
        ]);
        
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'rol_id' => $request->rol_id,
            'activo' => $request->has('activo')
        ];
        
        // Solo actualizar contraseña si se proporcionó una nueva
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $usuario->update($data);
        
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario actualizado exitosamente');
    }
    
    /**
     * Eliminar usuario
     */
    public function destroy(User $usuario)
    {
        // Prevenir eliminación del propio usuario
        if ($usuario->id === auth()->id()) {
            return redirect()->route('usuarios.index')
                ->with('error', 'No puedes eliminar tu propio usuario');
        }
        
        $usuario->delete();
        
        return redirect()->route('usuarios.index')
            ->with('success', 'Usuario eliminado exitosamente');
    }
    
    /**
     * Activar/Desactivar usuario
     */
    public function toggleActivo(User $usuario)
    {
        $usuario->update([
            'activo' => !$usuario->activo
        ]);
        
        $estado = $usuario->activo ? 'activado' : 'desactivado';
        
        return response()->json([
            'success' => true,
            'message' => "Usuario {$estado} exitosamente",
            'activo' => $usuario->activo
        ]);
    }
    
    /**
     * Resetear contraseña
     */
    public function resetPassword(Request $request, User $usuario)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()]
        ]);
        
        $usuario->update([
            'password' => Hash::make($request->password)
        ]);
        
        return redirect()->route('usuarios.show', $usuario)
            ->with('success', 'Contraseña actualizada exitosamente');
    }
    
    /**
     * Obtener usuarios activos (API)
     */
    public function activos()
    {
        $usuarios = User::where('activo', true)
            ->select('id', 'name', 'email', 'rol_id')
            ->with('rol:id,nombre')
            ->orderBy('name', 'asc')
            ->get();
        
        return response()->json($usuarios);
    }
}