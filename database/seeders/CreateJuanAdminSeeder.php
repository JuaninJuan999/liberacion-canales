<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CreateJuanAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener o crear rol ADMINISTRADOR
        $rolAdmin = Rol::firstOrCreate(
            ['nombre' => 'ADMINISTRADOR'],
            ['nombre' => 'ADMINISTRADOR']
        );

        // Crear usuario
        $usuario = User::firstOrCreate(
            ['email' => 'kall.su999@gmail.com'],
            [
                'name' => 'Juan Carreño',
                'email' => 'kall.su999@gmail.com',
                'password' => Hash::make('SIRT123'),
                'rol_id' => $rolAdmin->id,
                'activo' => true,
            ]
        );

        echo "\n✅ Usuario creado/actualizado:\n";
        echo "   Nombre: {$usuario->name}\n";
        echo "   Email: {$usuario->email}\n";
        echo "   Rol: ADMINISTRADOR\n";
        echo "   Estado: ACTIVO\n\n";
    }
}
