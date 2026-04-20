<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallazgoToleranciaZero extends Model
{
    protected $table = 'hallazgos_tolerancia_cero';

    protected $fillable = [
        'fecha_registro',
        'fecha_operacion',
        'codigo',
        'producto_id',
        'tipo_hallazgo_id',
        'ubicacion_id',
        'usuario_id',
        'observacion',
    ];

    protected $casts = [
        'fecha_registro' => 'datetime',
        'fecha_operacion' => 'date',
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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Calcula la fecha efectiva de operacion considerando el turno de 12 PM a 7 AM.
     * Si se registra entre 00:00-06:59, cuenta como del dia anterior.
     */
    public function getFechaOperacionEfectiva(): Carbon
    {
        $horaRegistro = Carbon::parse($this->fecha_registro ?? $this->created_at);

        if ($horaRegistro->hour < 7) {
            return Carbon::parse($this->fecha_operacion)->subDay();
        }

        return Carbon::parse($this->fecha_operacion);
    }

    /**
     * Filtra por fecha operativa (columna fecha_operacion).
     *
     * @see RegistroHallazgo::scopePorFechaConTurno (misma convención: el turno se aplica al guardar)
     */
    public function scopePorFechaConTurno($query, $fecha)
    {
        $dia = Carbon::parse($fecha)->toDateString();

        return $query->whereDate('hallazgos_tolerancia_cero.fecha_operacion', $dia);
    }

    /**
     * Filtra por rango de fechas operativas (columna fecha_operacion).
     */
    public function scopePorRangoFechasConTurno($query, $fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->toDateString();
        $fin = Carbon::parse($fechaFin)->toDateString();

        return $query->whereBetween('hallazgos_tolerancia_cero.fecha_operacion', [$inicio, $fin]);
    }

    /**
     * Obtener el nombre del tipo de hallazgo
     */
    public function getNombreTipoHallazgoAttribute(): string
    {
        return $this->tipoHallazgo?->nombre ?? 'N/A';
    }

    /**
     * Obtener el nombre del producto
     */
    public function getNombreProductoAttribute(): string
    {
        return $this->producto?->nombre ?? 'N/A';
    }
}
