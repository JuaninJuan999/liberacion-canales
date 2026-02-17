<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroHallazgo extends Model
{
    protected $table = 'registros_hallazgos';

    protected $fillable = [
        'fecha_registro',
        'fecha_operacion',
        'codigo',
        'producto_id',
        'tipo_hallazgo_id',
        'ubicacion_id',
        'lado_id',
        'cantidad',
        'evidencia_path',
        'operario_id',
        'usuario_id',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'fecha_operacion' => 'date',
        'cantidad' => 'integer',
    ];

    // Relaciones
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function tipoHallazgo(): BelongsTo
    {
        return $this->belongsTo(TipoHallazgo::class, 'tipo_hallazgo_id');
    }

    public function ubicacion(): BelongsTo
    {
        return $this->belongsTo(Ubicacion::class, 'ubicacion_id');
    }

    public function lado(): BelongsTo
    {
        return $this->belongsTo(Lado::class, 'lado_id');
    }

    public function operario(): BelongsTo
    {
        return $this->belongsTo(Operario::class, 'operario_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Scopes para filtros
    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_operacion', $fecha);
    }

    public function scopePorRangoFechas($query, $fechaInicio, $fechaFin)
    {
        return $query->whereBetween('fecha_operacion', [$fechaInicio, $fechaFin]);
    }
}
