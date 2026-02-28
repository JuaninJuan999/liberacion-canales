<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Productos según especificación: Media Canal 1 Lengua, Media Canal 2 Cola.
     */
    public function run(): void
    {
        $productos = [
            'Media Canal 1 Lengua',
            'Media Canal 2 Cola',
        ];

        foreach ($productos as $nombre) {
            Producto::firstOrCreate(['nombre' => $nombre]);
        }
    }
}
