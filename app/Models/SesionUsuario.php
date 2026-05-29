<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class SesionUsuario extends Model
{
    protected $table = 'sesiones_usuario';

    protected $fillable = [
        'user_id',
        'login_at',
        'ultima_actividad',
        'logout_at',
        'duracion_minutos',
        'ip_address',
    ];

    protected $casts = [
        'login_at' => 'datetime',
        'ultima_actividad' => 'datetime',
        'logout_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function minutosInactividad(): int
    {
        return max(1, (int) config('usabilidad.inactividad_minutos', 120));
    }

    public function instanteUltimaActividad(): Carbon
    {
        return Carbon::parse($this->ultima_actividad ?? $this->login_at);
    }

    /** Sesión abierta en BD y dentro del umbral de inactividad. */
    public function estaAbierta(): bool
    {
        if ($this->logout_at !== null) {
            return false;
        }

        return $this->instanteUltimaActividad()->gte(
            now()->subMinutes(self::minutosInactividad())
        );
    }

    public function cerrar(?CarbonInterface $fin = null): void
    {
        if ($this->logout_at !== null) {
            return;
        }

        $fin = Carbon::parse($fin ?? now());

        $this->update([
            'logout_at' => $fin,
            'ultima_actividad' => $fin,
            'duracion_minutos' => round($this->login_at->diffInSeconds($fin) / 60, 2),
        ]);
    }

    /** Cierra usando la última actividad registrada (cierre por abandono / inactividad). */
    public function cerrarPorInactividad(): void
    {
        if ($this->logout_at !== null) {
            return;
        }

        $fin = $this->instanteUltimaActividad();

        $this->update([
            'logout_at' => $fin,
            'duracion_minutos' => round($this->login_at->diffInSeconds($fin) / 60, 2),
        ]);
    }

    /**
     * Marca como finalizadas las sesiones sin logout cuyo último uso superó el umbral.
     *
     * @return int Cantidad de sesiones cerradas
     */
    public static function cerrarSesionesInactivas(?int $minutosInactividad = null): int
    {
        $minutos = max(1, $minutosInactividad ?? self::minutosInactividad());
        $limite = now()->subMinutes($minutos);

        $sesiones = static::query()
            ->whereNull('logout_at')
            ->where(function ($q) use ($limite) {
                $q->where('ultima_actividad', '<', $limite)
                    ->orWhere(function ($q2) use ($limite) {
                        $q2->whereNull('ultima_actividad')
                            ->where('login_at', '<', $limite);
                    });
            })
            ->get();

        foreach ($sesiones as $sesion) {
            $sesion->cerrarPorInactividad();
        }

        return $sesiones->count();
    }
}
