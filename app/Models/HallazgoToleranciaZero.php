<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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
     * Scope para filtrar por fecha considerando el turno de 12 PM a 7 AM.
     */
    public function scopePorFechaConTurno($query, $fecha)
    {
        $fechaCarbon = Carbon::parse($fecha);
        $fechaSiguiente = $fechaCarbon->copy()->addDay();

        return $query->where(function ($q) use ($fechaCarbon, $fechaSiguiente) {
            $q->whereDate('hallazgos_tolerancia_cero.fecha_operacion', $fechaCarbon)
                ->where(function ($subQ) {
                    $subQ->whereRaw('HOUR(hallazgos_tolerancia_cero.fecha_registro) >= 12')
                        ->orWhereRaw('HOUR(hallazgos_tolerancia_cero.fecha_registro) < 7');
                })
                ->orWhere(function ($subQ) use ($fechaSiguiente) {
                    $subQ->whereDate('hallazgos_tolerancia_cero.fecha_operacion', $fechaSiguiente)
                        ->whereRaw('HOUR(hallazgos_tolerancia_cero.fecha_registro) < 7');
                });
        });
    }

    /**
     * Scope para filtrar por rango de fechas considerando el turno de 12 PM a 7 AM.
     */
    public function scopePorRangoFechasConTurno($query, $fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);

        return $query->where(function ($q) use ($inicio, $fin) {
            $q->whereBetween('hallazgos_tolerancia_cero.fecha_operacion', [$inicio, $fin])
                ->where(function ($subQ) {
                    $subQ->whereRaw('HOUR(hallazgos_tolerancia_cero.fecha_registro) >= 12')
                        ->orWhereRaw('HOUR(hallazgos_tolerancia_cero.fecha_registro) < 7');
                })
                ->orWhere(function ($subQ) use ($inicio, $fin) {
                    $subQ->whereBetween('hallazgos_tolerancia_cero.fecha_operacion', [$inicio->copy()->addDay(), $fin->copy()->addDay()])
                        ->whereRaw('HOUR(hallazgos_tolerancia_cero.fecha_registro) < 7');
                });
        });
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
