<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipoHallazgo extends Model
{
    protected $table = 'tipos_hallazgo';

    protected $fillable = [
        'nombre',
    ];

    // Relación: Un tipo de hallazgo tiene muchos registros
    public function registrosHallazgos(): HasMany
    {
        return $this->hasMany(RegistroHallazgo::class, 'tipo_hallazgo_id');
    }
}
