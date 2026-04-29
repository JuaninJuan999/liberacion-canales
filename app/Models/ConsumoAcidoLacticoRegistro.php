<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConsumoAcidoLacticoRegistro extends Model
{
    protected $table = 'consumo_acido_lactico_registros';

    protected $fillable = [
        'fecha',
        'hora',
        'litros_preparados',
        'cantidad_acido_lactico_ml',
        'observacion',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'litros_preparados' => 'decimal:3',
            'cantidad_acido_lactico_ml' => 'decimal:3',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
