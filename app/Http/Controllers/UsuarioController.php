    /**
     * Mostrar perfil del usuario autenticado
     */
    public function profile()
    {
        $usuario = auth()->user();
        $usuario->load('rol');
        return view('profile.show', compact('usuario'));
    }
    
    /**
     * Mostrar formulario para editar perfil del usuario autenticado
     */
    public function editProfile()
    {
        $usuario = auth()->user();
        return view('profile.edit', compact('usuario'));
    }
    
    /**
     * Actualizar perfil del usuario autenticado
     */
    public function updateProfile(Request $request)
    {
        $usuario = auth()->user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' + usuario->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'current_password' => ['required_with:password', 'current_password']
        ]);
        
        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];
        
        // Solo actualizar contraseña si se proporcionó una nueva
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $usuario->update($data);
        
        return redirect()->route('profile')
            ->with('success', 'Perfil actualizado exitosamente');
    }
}