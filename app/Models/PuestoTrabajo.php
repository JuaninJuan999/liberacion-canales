<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PuestoTrabajo extends Model
{
    use HasFactory;

    protected $table = 'puestos_trabajo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'orden',
    ];

    /**
     * Orden por defecto
     */
    protected static function booted()
    {
        static::addGlobalScope('ordered', function ($builder) {
            $builder->orderBy('orden', 'asc');
        });
    }

    public function operariosPorDia()
    {
        return $this->hasMany(OperarioPorDia::class, 'puesto_trabajo_id');
    }
}
