<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

class MakeAdmin extends Command
{
    protected $signature = 'user:create-admin {name} {email} {password}';
    protected $description = 'Crear un nuevo usuario con rol de administrador';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');

        // Verificar si rol existe, si no crearlo
        $rolAdmin = Rol::where('nombre', 'ADMINISTRADOR')->first();
        if (!$rolAdmin) {
            $rolAdmin = Rol::create(['nombre' => 'ADMINISTRADOR']);
            $this->info("✅ Rol ADMINISTRADOR creado");
        }

        // Verificar si usuario ya existe
        $usuarioExistente = User::where('email', $email)->first();
        if ($usuarioExistente) {
            $this->warn("⚠️ Usuario con email '{$email}' ya existe");
            if ($this->confirm('¿Deseas eliminarlo y crear uno nuevo?')) {
                $usuarioExistente->delete();
                $this->info("🗑️ Usuario anterior eliminado");
            } else {
                return 1;
            }
        }

        // Crear usuario
        try {
            $usuario = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'rol_id' => $rolAdmin->id,
                'activo' => true,
            ]);

            $this->info("\n✅ Usuario ADMINISTRADOR creado exitosamente");
            $this->line("   Nombre: {$usuario->name}");
            $this->line("   Email: {$usuario->email}");
            $this->line("   Rol: ADMINISTRADOR");
            $this->line("   Estado: ACTIVO\n");
            
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Error al crear usuario: " . $e->getMessage());
            return 1;
        }
    }
}
