<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnimalProcesado extends Model
{
    protected $table = 'animales_procesados';

    protected $fillable = [
        'fecha_operacion',
        'cantidad_animales',
        'usuario_id',
    ];

    protected $casts = [
        'fecha_operacion' => 'date',
        'cantidad_animales' => 'integer',
    ];

    // Relación: Pertenece a un usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
