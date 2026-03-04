<?php

use App\Models\Rol;
use App\Models\User;

require_once 'bootstrap/app.php';

$app = app();
$app->make(\Illuminate\Contracts\Http\Kernel::class);

// Crear rol ADMINISTRADOR
$rolAdmin = Rol::firstOrCreate(
    ['nombre' => 'ADMINISTRADOR'],
    ['nombre' => 'ADMINISTRADOR']
);

echo "✅ Rol ADMINISTRADOR creado/verificado\n";

// Actualizar usuario
$usuario = User::where('email', 'tecnologia@colbeef.com')->first();

if ($usuario) {
    $usuario->rol_id = $rolAdmin->id;
    $usuario->save();
    echo "✅ Usuario '{$usuario->name}' ahora es ADMINISTRADOR\n";
} else {
    echo "❌ Usuario con email tecnologia@colbeef.com no encontrado\n";
}
