<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;

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

    /** Resalta el ítem del menú lateral según la ruta actual (coincide con prefijos típicos). */
    public function esRutaActiva(): bool
    {
        $name = (string) $this->vista;
        if ($name === '' || ! Route::has($name)) {
            return false;
        }

        if (request()->routeIs($name)) {
            return true;
        }

        if (str_starts_with($name, 'usuarios.')) {
            return request()->routeIs('usuarios.*');
        }

        if (str_starts_with($name, 'operarios-dia.')) {
            return request()->routeIs('operarios-dia.*') || request()->routeIs('operarios.gestion-dia');
        }

        $parts = explode('.', $name);
        if (count($parts) === 2 && ($parts[1] ?? '') === 'index') {
            return request()->routeIs($parts[0].'.*');
        }

        return false;
    }

    /** IDs de filas de menú donde el rol tiene visibilidad (coincide con pantalla de bienvenida). */
    public static function idsModulosParaRol(Rol $rol): array
    {
        $norm = Rol::normalizarNombre($rol->nombre);

        return static::query()->ordenado()->get()
            ->filter(fn (self $m) => $m->visibleParaRol($norm))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * Marca qué módulos ve un rol en la tabla menu_modulos (JSON roles por fila).
     *
     * @param  array<int|string>  $moduloIdsSeleccionados
     */
    public static function sincronizarModulosParaRol(Rol $rol, array $moduloIdsSeleccionados): void
    {
        $norm = Rol::normalizarNombre($rol->nombre);
        $idsSet = collect($moduloIdsSeleccionados)->map(fn ($id) => (int) $id)->flip()->all();

        foreach (static::query()->ordenado()->get() as $modulo) {
            $roles = collect($modulo->roles ?? [])
                ->map(fn ($r) => Rol::normalizarNombre(is_string($r) ? $r : (string) $r))
                ->unique()
                ->values()
                ->all();

            $debe = isset($idsSet[$modulo->id]);
            $tiene = in_array($norm, $roles, true);

            if ($debe && ! $tiene) {
                $roles[] = $norm;
            } elseif (! $debe && $tiene) {
                $roles = array_values(array_filter($roles, fn ($r) => $r !== $norm));
            } else {
                continue;
            }

            $modulo->roles = array_values(array_unique($roles));
            $modulo->save();
        }
    }

    /** Al renombrar un rol en BD: actualiza los arrays roles en cada fila del menú. */
    public static function renombrarRolEnMenus(string $normAntes, string $normDespues): void
    {
        if ($normAntes === '' || $normAntes === $normDespues) {
            return;
        }

        foreach (static::query()->get() as $modulo) {
            $roles = collect($modulo->roles ?? [])
                ->map(fn ($r) => Rol::normalizarNombre(is_string($r) ? $r : (string) $r))
                ->values()
                ->all();

            $reemplazados = array_map(fn ($r) => $r === $normAntes ? $normDespues : $r, $roles);
            $reemplazados = array_values(array_unique($reemplazados));

            if ($roles !== $reemplazados) {
                $modulo->roles = $reemplazados;
                $modulo->save();
            }
        }
    }

    /** Eliminar todas las referencias a un rol normalizado en los menús (al borrar rol). */
    public static function quitarRolDeTodosLosMenus(string $norm): void
    {
        if ($norm === '') {
            return;
        }

        foreach (static::query()->get() as $modulo) {
            $filtrados = collect($modulo->roles ?? [])
                ->filter(fn ($r) => Rol::normalizarNombre(is_string($r) ? $r : (string) $r) !== $norm)
                ->values()
                ->all();

            if ($filtrados !== ($modulo->roles ?? [])) {
                $modulo->roles = $filtrados;
                $modulo->save();
            }
        }
    }
}
