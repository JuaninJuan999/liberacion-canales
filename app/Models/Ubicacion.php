<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $fillable = [
        'nombre',
    ];

    // Relación: Una ubicación tiene muchos registros de hallazgos
    public function registrosHallazgos(): HasMany
    {
        return $this->hasMany(RegistroHallazgo::class, 'ubicacion_id');
    }
}
