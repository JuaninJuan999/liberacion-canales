<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rol extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nombre',
    ];

    /**
     * Normaliza el nombre de rol para comparaciones (p. ej. Admin → ADMINISTRADOR).
     */
    public static function normalizarNombre(?string $nombre): string
    {
        $n = strtoupper(trim((string) $nombre));

        return $n === 'ADMIN' ? 'ADMINISTRADOR' : $n;
    }

    // Relación: Un rol tiene muchos usuarios
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }
}
