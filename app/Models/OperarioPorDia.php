<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OperarioPorDia extends Model
{
    protected $table = 'operarios_por_dia';

    protected $fillable = [
        'fecha_operacion',
        'puesto_trabajo_id',
        'operario_id',
    ];

    protected $casts = [
        'fecha_operacion' => 'date',
    ];

    // Relación: Pertenece a un puesto de trabajo
    public function puestoTrabajo(): BelongsTo
    {
        return $this->belongsTo(PuestoTrabajo::class, 'puesto_trabajo_id');
    }

    // Relación: Pertenece a un operario
    public function operario(): BelongsTo
    {
        return $this->belongsTo(Operario::class, 'operario_id');
    }
}
