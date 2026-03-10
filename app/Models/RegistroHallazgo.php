<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

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
        'puesto_trabajo_id' // Added to fix relationship error
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
     * 
     * @return Carbon
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
     * Scope para filtrar por fecha considerando el turno de 12 PM a 7 AM
     * Los registros hechos entre 00:00-06:59 se consideran del día anterior
     */
    public function scopePorFechaConTurno($query, $fecha)
    {
        $fechaCarbon = Carbon::parse($fecha);
        $fechaSiguiente = $fechaCarbon->clone()->addDay();
        
        // Registros hechos entre 12 PM y 23:59 un día
        // O registros hechos entre 00:00 y 06:59 del siguiente día (que cuentan para el día anterior)
        return $query->where(function($q) use ($fechaCarbon, $fechaSiguiente) {
            $q->whereDate('registros_hallazgos.fecha_operacion', $fechaCarbon)
              ->where(function($subQ) {
                  $subQ->whereRaw('EXTRACT(HOUR FROM registros_hallazgos.created_at) >= 12')
                        ->orWhereRaw('EXTRACT(HOUR FROM registros_hallazgos.created_at) < 7');
              })
              ->orWhere(function($subQ) use ($fechaSiguiente) {
                  $subQ->whereDate('registros_hallazgos.fecha_operacion', $fechaSiguiente)
                        ->whereRaw('EXTRACT(HOUR FROM registros_hallazgos.created_at) < 7');
              });
        });
    }

    /**
     * Scope para filtrar por rango de fechas considerando el turno de 12 PM a 7 AM
     */
    public function scopePorRangoFechasConTurno($query, $fechaInicio, $fechaFin)
    {
        $inicio = Carbon::parse($fechaInicio);
        $fin = Carbon::parse($fechaFin);
        
        return $query->where(function($q) use ($inicio, $fin) {
            // Registros hechos entre 12 PM y 23:59 en el rango de fechas
            $q->whereBetween('registros_hallazgos.fecha_operacion', [$inicio, $fin])
              ->where(function($subQ) {
                  $subQ->whereRaw('EXTRACT(HOUR FROM registros_hallazgos.created_at) >= 12')
                        ->orWhereRaw('EXTRACT(HOUR FROM registros_hallazgos.created_at) < 7');
              })
              // O registros hechos entre 00:00 y 06:59 del siguiente día
              ->orWhere(function($subQ) use ($inicio, $fin) {
                  $subQ->whereBetween('registros_hallazgos.fecha_operacion', [$inicio->clone()->addDay(), $fin->clone()->addDay()])
                        ->whereRaw('EXTRACT(HOUR FROM registros_hallazgos.created_at) < 7');
              });
        });
    }
}
