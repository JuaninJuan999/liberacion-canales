<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TitulacionAcidoLacticoRegistro extends Model
{
    protected $table = 'titulacion_acido_lactico_registros';

    protected $fillable = [
        'fecha',
        'hora',
        'volumen_naoh_ml',
        'concentracion_sol_pct',
        'cumple',
        'correccion',
        'actividad',
        'user_id',
        'verificado_user_id',
        'verificado_nombre',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'volumen_naoh_ml' => 'decimal:2',
            'concentracion_sol_pct' => 'decimal:2',
            'cumple' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function verificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verificado_user_id');
    }

    /** @return array<string, string> */
    public static function actividadesOpciones(): array
    {
        return [
            'operativo' => 'Operativo',
            'preoperativo' => 'Preoperativo',
            'monitoreo_pcc' => 'Monitoreo PCC',
        ];
    }
}
