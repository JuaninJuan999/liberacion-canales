<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FiltroUsuario extends Model
{
    protected $table = 'filtros_usuario';

    protected $fillable = [
        'usuario_id',
        'tipo',
        'configuracion',
    ];

    protected $casts = [
        'configuracion' => 'array',
    ];

    // Relación: Pertenece a un usuario
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
