<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lado extends Model
{
    protected $fillable = [
        'nombre',
    ];

    // Relación: Un lado tiene muchos registros de hallazgos
    public function registrosHallazgos(): HasMany
    {
        return $this->hasMany(RegistroHallazgo::class, 'lado_id');
    }
}
