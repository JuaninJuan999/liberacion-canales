<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UsuarioController extends Controller
{
    /**
     * Mostrar perfil del usuario autenticado.
     */
    public function profile()
    {
        $user = auth()->user();
        // La vista 'profile.show' espera una variable llamada $user.
        return view('profile.show', compact('user'));
    }

    /**
     * Mostrar formulario para editar el perfil del usuario autenticado.
     */
    public function editProfile()
    {
        $usuario = auth()->user();
        return view('profile.edit', compact('usuario'));
    }

    /**
     * Actualizar el perfil del usuario autenticado.
     */
    public function updateProfile(Request $request)
    {
        $usuario = auth()->user();

        // Validar los datos del formulario.
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            // Se corrige la concatenación para la regla 'unique'.
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $usuario->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        // Actualizar la contraseña solo si se proporciona una nueva.
        if ($request->filled('password')) {
            // En una aplicación real, aquí se debería validar también la contraseña actual.
            $data['password'] = Hash::make($request->password);
        }

        $usuario->update($data);

        return redirect()->route('profile')
            ->with('success', '✅ Perfil actualizado exitosamente');
    }
    
    // NOTA: Este controlador puede tener otros métodos para la gestión de usuarios 
    // (index, create, store, etc.) que no se incluyen aquí para no sobreescribirlos 
    // si ya existen y funcionan correctamente. Esta corrección se centra en los
    // métodos del perfil de usuario.
}
