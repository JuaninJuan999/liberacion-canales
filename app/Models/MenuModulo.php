<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuModulo extends Model
{
    protected $table = 'menu_modulos';

    protected $fillable = [
        'nombre',
        'vista',
        'icono',
        'orden',
        'roles',
    ];

    protected $casts = [
        'roles' => 'array',
        'orden' => 'integer',
    ];

    // Scope para ordenar por orden
    public function scopeOrdenado($query)
    {
        return $query->orderBy('orden', 'asc');
    }
}
