<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener rol de Admin
        $adminRol = Rol::where('nombre', 'Admin')->first();

        // Usuario Admin principal
        User::create([
            'name' => 'TIC',
            'email' => 'tecnologia@colbeef.com',
            'password' => Hash::make('SIR123'), 
            'rol_id' => $adminRol->id,
        ]);
    }
}
