<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndicadorDiario extends Model
{
    protected $table = 'indicadores_diarios';

    protected $fillable = [
        'fecha_operacion',
        'animales_procesados',
        'medias_canales_total',
        'medias_canal_1',
        'medias_canal_2',
        'total_hallazgos',
        'cobertura_grasa',
        'hematomas',
        'cortes_piernas',
        'sobrebarriga_rota',
        'participacion_total',
        'desglose_hallazgos',
        'mes',
        'año',
    ];

    protected $casts = [
        'fecha_operacion' => 'date',
        'animales_procesados' => 'integer',
        'medias_canales_total' => 'integer',
        'medias_canal_1' => 'integer',
        'medias_canal_2' => 'integer',
        'total_hallazgos' => 'integer',
        'cobertura_grasa' => 'integer',
        'hematomas' => 'integer',
        'cortes_piernas' => 'integer',
        'sobrebarriga_rota' => 'integer',
        'participacion_total' => 'decimal:2',
        'año' => 'integer',
    ];

    // Scope para filtrar por mes y año
    public function scopePorMesAño($query, $mes, $año)
    {
        return $query->where('mes', $mes)->where('año', $año);
    }

    /**
     * Obtener hematomas desde desglose_hallazgos (cuando las columnas fueron migradas a JSON).
     */
    public function getHematomasAttribute($value): int
    {
        if ($value !== null && $value !== '') {
            return (int) $value;
        }
        return (int) ($this->getDesgloseValue('HEMATOMAS') ?? 0);
    }

    /**
     * Obtener cobertura_grasa desde desglose_hallazgos.
     */
    public function getCoberturaGrasaAttribute($value): int
    {
        if ($value !== null && $value !== '') {
            return (int) $value;
        }
        return (int) ($this->getDesgloseValue('COBERTURA DE GRASA') ?? 0);
    }

    /**
     * Obtener cortes_piernas desde desglose_hallazgos.
     */
    public function getCortesPiernasAttribute($value): int
    {
        if ($value !== null && $value !== '') {
            return (int) $value;
        }
        return (int) ($this->getDesgloseValue('CORTES EN LA PIERNA') ?? 0);
    }

    /**
     * Obtener sobrebarriga_rota desde desglose_hallazgos.
     */
    public function getSobrebarrigaRotaAttribute($value): int
    {
        if ($value !== null && $value !== '') {
            return (int) $value;
        }
        return (int) ($this->getDesgloseValue('SOBREBARRIGA ROTA') ?? 0);
    }

    protected function getDesgloseValue(string $key)
    {
        $desglose = $this->attributes['desglose_hallazgos'] ?? null;
        if ($desglose === null) {
            return 0;
        }
        if (is_string($desglose)) {
            $desglose = json_decode($desglose, true);
        }
        return is_array($desglose) ? ($desglose[$key] ?? 0) : 0;
    }
}
