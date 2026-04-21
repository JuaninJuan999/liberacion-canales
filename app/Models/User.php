<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'rol_id',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación: Pertenece a un rol
    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class, 'rol_id');
    }

    /** Nombre de rol normalizado para permisos (coincide con menú lateral y módulos del welcome). */
    public function rolNormalizado(): string
    {
        return Rol::normalizarNombre($this->rol?->nombre);
    }

    // Relación: Tiene muchos registros de hallazgos
    public function registrosHallazgos(): HasMany
    {
        return $this->hasMany(RegistroHallazgo::class, 'usuario_id');
    }

    // Relación: Tiene muchos animales procesados registrados
    public function animalesProcesados(): HasMany
    {
        return $this->hasMany(AnimalProcesado::class, 'usuario_id');
    }

    // Relación: Tiene muchos filtros guardados
    public function filtros(): HasMany
    {
        return $this->hasMany(FiltroUsuario::class, 'usuario_id');
    }
}
