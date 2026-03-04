<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class SetupAdminSeeder extends Seeder
{
    public function run(): void
    {
        // Crear rol ADMINISTRADOR
        $rolAdmin = Rol::firstOrCreate(
            ['nombre' => 'ADMINISTRADOR'],
            ['nombre' => 'ADMINISTRADOR']
        );

        echo "\n✅ Rol ADMINISTRADOR creado/verificado\n";

        // Actualizar usuario
        $usuario = User::where('email', 'tecnologia@colbeef.com')->first();

        if ($usuario) {
            $usuario->rol_id = $rolAdmin->id;
            $usuario->save();
            echo "✅ Usuario '{$usuario->name}' ahora es ADMINISTRADOR\n";
        } else {
            echo "❌ Usuario con email tecnologia@colbeef.com no encontrado\n";
        }
    }
}
