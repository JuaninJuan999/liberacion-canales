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

    public function visibleParaRol(string $rolNormalizado): bool
    {
        $rolesPermitidos = array_map(
            fn ($r) => Rol::normalizarNombre(is_string($r) ? $r : (string) $r),
            $this->roles ?? []
        );

        return $rolNormalizado !== '' && in_array($rolNormalizado, $rolesPermitidos, true);
    }
}
