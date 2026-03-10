<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UserSeeder::class,
            MenuModuloSeeder::class,
            PuestoTrabajoSeeder::class,
            ProductoSeeder::class,
            LadoSeeder::class,
            TipoHallazgoSeeder::class,
            UbicacionSeeder::class,
            UbicacionesToleranciaZeroSeeder::class,
            CrearTransferenciaSeeder::class,
            RelacionarUbicacionesPuestosSeeder::class,
        ]);
    }
}
