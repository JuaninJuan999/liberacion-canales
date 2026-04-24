<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroHallazgoEliminado extends Model
{
    protected $table = 'registros_hallazgos_eliminados';

    protected $fillable = [
        'registro_hallazgo_id',
        'payload',
        'eliminado_por_user_id',
        'eliminado_por_nombre',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function eliminadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'eliminado_por_user_id');
    }
}
