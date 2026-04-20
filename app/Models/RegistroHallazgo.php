<?php

namespace App\Models;

use Carbon\Carbon;
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
        'observacion', // Added to prevent future errors
        'puesto_trabajo_id', // Added to fix relationship error
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

    public function puestoTrabajo(): BelongsTo
    {
        return $this->belongsTo(PuestoTrabajo::class, 'puesto_trabajo_id');
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

    /**
     * Calcula la fecha efectiva de operación considerando el turno de 12 PM a 7 AM
     * Si se registra entre 00:00-06:59, cuenta como del día anterior
     */
    public function getFechaOperacionEfectiva(): Carbon
    {
        $horaCreacion = Carbon::parse($this->created_at);
        $horaEnMinutos = $horaCreacion->hour * 60 + $horaCreacion->minute;

        // 7 AM = 420 minutos, 12 PM = 720 minutos
        // Si la hora está entre 00:00 (0 mins) y 06:59 (419 mins), resta un día
        if ($horaEnMinutos < 420) { // Antes de las 7:00 AM
            return Carbon::parse($this->fecha_operacion)->subDay();
        }

        return Carbon::parse($this->fecha_operacion);
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

    /**
     * Filtra por fecha operativa (columna fecha_operacion).
     *
     * La hora del turno ya se refleja al guardar: en el registro de hallazgos, si la hora es
     * antes de las 7:00 se usa el día anterior como fecha_operacion. Filtrar además por hora
     * de created_at excluía por error los registros entre 7:00 y 11:59 (gráficos e historial vacíos).
     */
    public function scopePorFechaConTurno($query, $fecha)
    {
        $dia = Carbon::parse($fecha)->toDateString();

        return $query->whereDate('registros_hallazgos.fecha_operacion', $dia);
    }

    /**
     * Filtra por rango de fechas operativas (columna fecha_operacion).
     *
     * @see scopePorFechaConTurno
     */
    public function scopePorRangoFechasConTurno($query, $fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio)->toDateString();
        $fin = Carbon::parse($fechaFin)->toDateString();

        return $query->whereBetween('registros_hallazgos.fecha_operacion', [$inicio, $fin]);
    }
}
