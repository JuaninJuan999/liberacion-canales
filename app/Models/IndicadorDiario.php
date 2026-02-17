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
}
