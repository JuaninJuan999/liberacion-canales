<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionApp extends Model
{
    protected $table = 'configuracion_app';

    protected $fillable = [
        'nombre',
        'logo',
        'mensaje_bienvenida',
    ];

    // Método estático para obtener la configuración
    public static function obtener()
    {
        return self::first();
    }
}
