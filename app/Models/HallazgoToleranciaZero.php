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

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
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
