<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Operario extends Model
{
    protected $fillable = [
        'nombre',
        'documento',
        'activo',
    ];

    protected $casts = [
        'activo' => 'boolean',
    ];

    // Relación: Un operario tiene muchas asignaciones diarias
    public function operariosPorDia(): HasMany
    {
        return $this->hasMany(OperarioPorDia::class, 'operario_id');
    }

    // Relación: Un operario tiene muchos registros de hallazgos
    public function registrosHallazgos(): HasMany
    {
        return $this->hasMany(RegistroHallazgo::class, 'operario_id');
    }

    // Scope para operarios activos
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }
}
