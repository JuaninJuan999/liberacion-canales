<?php

use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Http\Kernel::class);

// Crear o obtener rol ADMINISTRADOR
$rolAdmin = Rol::where('nombre', 'ADMINISTRADOR')->first();
if (!$rolAdmin) {
    $rolAdmin = Rol::create(['nombre' => 'ADMINISTRADOR']);
    echo "✅ Rol ADMINISTRADOR creado\n";
}

// Verificar si el usuario ya existe
$usuarioExistente = User::where('email', 'kall.su999@gmail.com')->first();
if ($usuarioExistente) {
    $usuarioExistente->delete();
    echo "🗑️ Usuario anterior eliminado\n";
}

// Crear usuario
$usuario = User::create([
    'name' => 'Juan Carreño',
    'email' => 'kall.su999@gmail.com',
    'password' => Hash::make('SIRT123'),
    'rol_id' => $rolAdmin->id,
    'activo' => true,
]);

echo "\n✅ Usuario creado exitosamente:\n";
echo "   Nombre: {$usuario->name}\n";
echo "   Email: {$usuario->email}\n";
echo "   Contraseña: SIRT123\n";
echo "   Rol: ADMINISTRADOR\n\n";
