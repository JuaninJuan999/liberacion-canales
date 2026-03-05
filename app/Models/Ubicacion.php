<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ubicacion extends Model
{
    protected $table = 'ubicaciones';

    protected $fillable = [
        'nombre',
        'puesto_trabajo_id',
    ];

    // Relación: Una ubicación tiene muchos registros de hallazgos
    public function registrosHallazgos(): HasMany
    {
        return $this->hasMany(RegistroHallazgo::class, 'ubicacion_id');
    }

    // Relación: Una ubicación pertenece a un puesto de trabajo
    public function puestoTrabajo(): BelongsTo
    {
        return $this->belongsTo(PuestoTrabajo::class, 'puesto_trabajo_id');
    }
}
