<?php

namespace App\Support;

use App\Models\RegistroHallazgo;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class HistorialRegistrosConsulta
{
    /**
     * @param  array{
     *     fecha_inicio?: string|null,
     *     fecha_fin?: string|null,
     *     producto_id?: int|string|null,
     *     tipo_hallazgo_id?: int|string|null,
     *     numero_canal?: string|null,
     *     solo_criticos?: bool|string|int|null,
     * }  $filtros
     */
    public static function queryConFiltros(array $filtros): Builder
    {
        $fechaInicio = $filtros['fecha_inicio'] ?? Carbon::now()->format('Y-m-d');
        $fechaFin = $filtros['fecha_fin'] ?? Carbon::now()->format('Y-m-d');
        $productoId = $filtros['producto_id'] ?? '';
        $tipoHallazgoId = $filtros['tipo_hallazgo_id'] ?? '';
        $numeroCanal = trim((string) ($filtros['numero_canal'] ?? ''));
        $soloCriticos = filter_var($filtros['solo_criticos'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return RegistroHallazgo::query()
            ->with(['producto', 'tipoHallazgo', 'puestoTrabajo', 'operario', 'usuario', 'ubicacion', 'lado'])
            ->porRangoFechasConTurno($fechaInicio, $fechaFin)
            ->when($productoId !== '' && $productoId !== null, function ($query) use ($productoId) {
                $query->where('registros_hallazgos.producto_id', $productoId);
            })
            ->when($tipoHallazgoId !== '' && $tipoHallazgoId !== null, function ($query) use ($tipoHallazgoId) {
                $query->where('registros_hallazgos.tipo_hallazgo_id', $tipoHallazgoId);
            })
            ->when($numeroCanal !== '', function ($query) use ($numeroCanal) {
                $query->where('registros_hallazgos.codigo', 'like', "%{$numeroCanal}%");
            })
            ->when($soloCriticos, function ($query) {
                $query->whereHas('tipoHallazgo', function ($q) {
                    $q->where('es_critico', true);
                });
            });
    }

    /**
     * @param  array<string, mixed>  $filtros
     * @return array{total: int, criticos: int, leves: int, por_tipo: array<string, int>}
     */
    public static function estadisticas(array $filtros): array
    {
        $base = self::queryConFiltros($filtros);

        $stats = (clone $base)
            ->select(
                DB::raw('COUNT(registros_hallazgos.id) as total'),
                DB::raw('SUM(CASE WHEN tipos_hallazgo.es_critico = TRUE THEN 1 ELSE 0 END) as criticos')
            )
            ->join('tipos_hallazgo', 'registros_hallazgos.tipo_hallazgo_id', '=', 'tipos_hallazgo.id')
            ->first();

        $total = (int) ($stats->total ?? 0);
        $criticos = (int) ($stats->criticos ?? 0);

        $porTipo = (clone $base)
            ->select(
                'tipos_hallazgo.nombre',
                DB::raw('COUNT(registros_hallazgos.id) as cantidad')
            )
            ->join('tipos_hallazgo', 'registros_hallazgos.tipo_hallazgo_id', '=', 'tipos_hallazgo.id')
            ->groupBy('tipos_hallazgo.nombre')
            ->orderByDesc('cantidad')
            ->pluck('cantidad', 'nombre')
            ->map(fn ($c) => (int) $c)
            ->all();

        return [
            'total' => $total,
            'criticos' => $criticos,
            'leves' => $total - $criticos,
            'por_tipo' => $porTipo,
        ];
    }

    public static function ubicacionHallazgo(RegistroHallazgo $registro): string
    {
        $tipo = strtolower($registro->tipoHallazgo->nombre ?? '');

        if ($tipo === 'cobertura de grasa') {
            return $registro->ubicacion->nombre ?? 'N/A';
        }

        $obs = trim((string) ($registro->observacion ?? ''));

        return $obs !== '' ? $obs : 'N/A';
    }

    public static function detallePierna(RegistroHallazgo $registro): string
    {
        $tipo = strtolower($registro->tipoHallazgo->nombre ?? '');
        $ubicacion = strtolower($registro->ubicacion->nombre ?? '');

        if ($tipo === 'cobertura de grasa' && $ubicacion === 'cadera') {
            return '';
        }

        return $registro->lado->nombre ?? '';
    }

    public static function operarioResponsable(RegistroHallazgo $registro): string
    {
        $puestoTrabajoNombre = null;
        $tipoHallazgo = strtoupper($registro->tipoHallazgo->nombre ?? '');
        $producto = $registro->producto->nombre ?? '';
        $lado = strtoupper($registro->lado->nombre ?? '');
        $ubicacion = strtoupper($registro->ubicacion->nombre ?? '');

        $paridad = '';
        if (in_array($lado, ['PAR', 'IMPAR'], true)) {
            $paridad = $lado;
        } elseif (is_numeric($registro->codigo)) {
            $paridad = ((int) $registro->codigo % 2 === 0) ? 'PAR' : 'IMPAR';
        }

        $esMediaCanal1 = strtoupper($producto) === 'MEDIA CANAL 1 LENGUA';
        $esMediaCanal2 = strtoupper($producto) === 'MEDIA CANAL 2 COLA';

        switch (true) {
            case str_contains($tipoHallazgo, 'COBERTURA') && str_contains($tipoHallazgo, 'GRASA'):
                if ($esMediaCanal1) {
                    if ($ubicacion === 'CADERA') {
                        $puestoTrabajoNombre = 'CADERA 1';
                    } elseif ($ubicacion === 'PIERNA' && $paridad === 'IMPAR') {
                        $puestoTrabajoNombre = 'PRIMERA IMPAR';
                    } elseif ($ubicacion === 'PIERNA' && $paridad === 'PAR') {
                        $puestoTrabajoNombre = 'PRIMERA PAR';
                    }
                } elseif ($esMediaCanal2) {
                    if ($ubicacion === 'CADERA') {
                        $puestoTrabajoNombre = 'CADERA 2';
                    } elseif ($ubicacion === 'PIERNA' && $paridad === 'IMPAR') {
                        $puestoTrabajoNombre = 'SEGUNDA IMPAR';
                    } elseif ($ubicacion === 'PIERNA' && $paridad === 'PAR') {
                        $puestoTrabajoNombre = 'SEGUNDA PAR';
                    }
                }
                break;

            case str_contains($tipoHallazgo, 'CORTE') && str_contains($tipoHallazgo, 'PIERNA'):
                if ($esMediaCanal1) {
                    $puestoTrabajoNombre = ($paridad === 'PAR') ? 'PRIMERA PAR' : 'PRIMERA IMPAR';
                } elseif ($esMediaCanal2) {
                    $puestoTrabajoNombre = ($paridad === 'PAR') ? 'SEGUNDA PAR' : 'SEGUNDA IMPAR';
                }
                break;

            case str_contains($tipoHallazgo, 'SOBREBARRIGA'):
                if ($esMediaCanal1) {
                    $puestoTrabajoNombre = 'ZAPATA IZQUIERDA';
                } elseif ($esMediaCanal2) {
                    $puestoTrabajoNombre = 'ZAPATA DERECHA';
                }
                break;

            case str_contains($tipoHallazgo, 'HEMATOMA'):
                $puestoTrabajoNombre = 'LIMPIEZA SUPERIOR';
                break;
        }

        if ($puestoTrabajoNombre) {
            try {
                $puestoTrabajo = DB::table('puestos_trabajo')
                    ->whereRaw('UPPER(nombre) = ?', [strtoupper($puestoTrabajoNombre)])
                    ->first();

                if ($puestoTrabajo) {
                    $fechaOperacion = ! empty($registro->fecha_operacion)
                        ? Carbon::parse($registro->fecha_operacion)
                        : Carbon::parse($registro->created_at);

                    $asignacion = DB::table('operarios_por_dia')
                        ->where('puesto_trabajo_id', $puestoTrabajo->id)
                        ->whereDate('fecha_operacion', $fechaOperacion->toDateString())
                        ->first();

                    if ($asignacion) {
                        $operario = DB::table('operarios')
                            ->where('id', $asignacion->operario_id)
                            ->first();
                        if ($operario) {
                            return $operario->nombre;
                        }
                    }
                }
            } catch (\Exception $e) {
                //
            }
        }

        if ($registro->operario_id) {
            $operarioDirecto = DB::table('operarios')
                ->where('id', $registro->operario_id)
                ->first();
            if ($operarioDirecto) {
                return $operarioDirecto->nombre;
            }
        }

        return 'Aun no se ha ingresado operario a la fecha de hoy';
    }

    public static function evidenciaLegible(?string $path): string
    {
        if ($path === null || trim($path) === '') {
            return 'N/A';
        }

        $base = basename(str_replace('\\', '/', $path));

        return $base !== '' ? $base : 'N/A';
    }

    /**
     * Ruta absoluta en disco de la evidencia (storage/app/public/...).
     */
    public static function rutaAbsolutaEvidencia(?string $path): ?string
    {
        if ($path === null || trim($path) === '') {
            return null;
        }

        $ruta = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($ruta, 'storage/')) {
            $ruta = substr($ruta, strlen('storage/'));
        }
        if ($ruta === '' || str_contains($ruta, '..')) {
            return null;
        }
        if (! str_contains($ruta, 'hallazgos/')) {
            $ruta = 'hallazgos/'.$ruta;
        }

        $absolute = storage_path('app/public/'.$ruta);

        return is_file($absolute) ? $absolute : null;
    }

    /** Data URI para incrustar la imagen en PDF/HTML. */
    public static function evidenciaDataUri(?string $path): ?string
    {
        $absolute = self::rutaAbsolutaEvidencia($path);
        if ($absolute === null) {
            return null;
        }

        $mime = mime_content_type($absolute) ?: 'image/jpeg';
        if (! str_starts_with($mime, 'image/')) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($absolute));
    }

    /** Logo institucional (public/logo.png) como data URI para PDF/HTML. */
    public static function logoInstitucionalDataUri(): ?string
    {
        $path = public_path('logo.png');
        if (! is_file($path)) {
            return null;
        }

        $mime = mime_content_type($path) ?: 'image/png';

        return 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($path));
    }
}
