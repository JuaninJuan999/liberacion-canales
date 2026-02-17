<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PuestoTrabajo extends Model
{
    protected $table = 'puestos_trabajo';

    protected $fillable = [
        'nombre',
    ];

    // Relación: Un puesto tiene muchas asignaciones diarias
    public function operariosPorDia(): HasMany
    {
        return $this->hasMany(OperarioPorDia::class, 'puesto_trabajo_id');
    }
}
