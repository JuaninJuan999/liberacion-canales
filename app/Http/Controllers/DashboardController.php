<?php

namespace App\Http\Controllers;

use App\Models\IndicadorDiario;
use App\Models\RegistroHallazgo;
use App\Models\HallazgoToleranciaZero;
use App\Models\AnimalProcesado;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return $this->index($request);
    }
    
    public function index(Request $request)
    {
        // Dashboard diario: por defecto fecha del día actual; días anteriores se consultan manualmente
        $hoy = Carbon::now()->toDateString();
        $fecha_inicio = $request->get('fecha_inicio', $hoy);
        $fecha_fin = $request->get('fecha_fin', $hoy);

        // Consultas - Usar el scope que considera el turno de 12 PM a 7 AM
        $indicadoresRango = IndicadorDiario::whereBetween('fecha_operacion', [$fecha_inicio, $fecha_fin])->get();
        $hallazgosRango = RegistroHallazgo::porRangoFechasConTurno($fecha_inicio, $fecha_fin)
            ->with(['tipoHallazgo', 'producto', 'ubicacion', 'lado', 'operario', 'usuario', 'puestoTrabajo'])
            ->latest('created_at')
            ->get();
        
        // Sumarizados para las tarjetas
        $animalesProcesados = AnimalProcesado::whereBetween('fecha_operacion', [$fecha_inicio, $fecha_fin])->sum('cantidad_animales');
        $indicador = (object)[
            'total_hallazgos'      => $indicadoresRango->sum('total_hallazgos'),
            'participacion_total'  => $indicadoresRango->avg('participacion_total'),
            'medias_canales_total' => $indicadoresRango->sum('medias_canales_total'),
        ];
        
        $topHallazgos = $hallazgosRango->groupBy('tipoHallazgo.nombre')
            ->map(fn($item) => $item->count())
            ->sortDesc()
            ->take(5);

        // --- Datos para Gráficos ---
        $hallazgosChartDataCanal1 = $hallazgosRango->filter(function ($hallazgo) {
            return $hallazgo->producto && str_contains($hallazgo->producto->nombre, 'Media Canal 1');
        })->groupBy('tipoHallazgo.nombre')->map(fn($item) => $item->count());

        $hallazgosChartDataCanal2 = $hallazgosRango->filter(function ($hallazgo) {
            return $hallazgo->producto && str_contains($hallazgo->producto->nombre, 'Media Canal 2');
        })->groupBy('tipoHallazgo.nombre')->map(fn($item) => $item->count());

        $productosChartData = $hallazgosRango->groupBy('producto.nombre')->map(fn($item) => $item->count());

        // Hallazgos por operario y tipo (usando la lógica de obtenerOperarioResponsable)
        $hallazgosPorOperarioYTipo = $this->calcularHallazgosPorOperarioYTipo($hallazgosRango);
        
        // Hallazgos de tolerancia cero por día/hora
        $hallazgosTZPorDia = $this->contarHallazgosTZPorDia($fecha_inicio, $fecha_fin);
        
        // Hallazgos de tolerancia cero por operario y tipo
        $hallazgosTZPorOperario = $this->contarHallazgosTZPorOperario($fecha_inicio, $fecha_fin);
        
        // Promedios del mes
        $indicadoresMes = IndicadorDiario::whereMonth('fecha_operacion', Carbon::parse($fecha_fin)->month)
            ->whereYear('fecha_operacion', Carbon::parse($fecha_fin)->year)
            ->get();
        
        $promediosMes = [
            'participacion' => $indicadoresMes->avg('participacion_total'),
        ];
        
        return view('dashboard.index', [
            'fecha_inicio' => $fecha_inicio,
            'fecha_fin' => $fecha_fin,
            'indicador' => $indicador,
            'animalesProcesados' => $animalesProcesados,
            'hallazgosDia' => $hallazgosRango,
            'topHallazgos' => $topHallazgos,
            'promediosMes' => $promediosMes,
            'hallazgosChartDataCanal1' => $hallazgosChartDataCanal1,
            'hallazgosChartDataCanal2' => $hallazgosChartDataCanal2,
            'productosChartData' => $productosChartData,
            'hallazgosPorOperarioYTipo' => $hallazgosPorOperarioYTipo,
            'hallazgosTZPorDia' => $hallazgosTZPorDia,
            'hallazgosTZPorOperario' => $hallazgosTZPorOperario,
        ]);
    }

    /**
     * Cuenta hallazgos de tolerancia cero agrupados por producto
     */
    private function contarHallazgosTZPorDia($fecha_inicio, $fecha_fin)
    {
        $hallazgos = HallazgoToleranciaZero::porRangoFechasConTurno($fecha_inicio, $fecha_fin)
            ->with(['tipoHallazgo', 'producto'])
            ->get();

        // Inicializar estructura por producto
        $resultado = [
            'CUARTO ANTERIOR' => [
                'MATERIA FECAL' => 0,
                'CONTENIDO RUMINAL' => 0,
                'LECHE VISIBLE' => 0
            ],
            'CUARTO POSTERIOR' => [
                'MATERIA FECAL' => 0,
                'CONTENIDO RUMINAL' => 0,
                'LECHE VISIBLE' => 0
            ]
        ];

        // Agrupar por producto y tipo
        foreach ($hallazgos as $hallazgo) {
            $producto = $hallazgo->producto->nombre ?? 'Desconocido';
            $tipo = $hallazgo->tipoHallazgo->nombre ?? 'Desconocido';

            if (isset($resultado[$producto]) && in_array($tipo, ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'])) {
                $resultado[$producto][$tipo]++;
            }
        }

        return $resultado;
    }

    /**
     * Cuenta hallazgos de tolerancia cero por operario y tipo de hallazgo
     */
    private function contarHallazgosTZPorOperario($fecha_inicio, $fecha_fin)
    {
        $hallazgos = HallazgoToleranciaZero::porRangoFechasConTurno($fecha_inicio, $fecha_fin)
            ->with(['tipoHallazgo', 'ubicacion.puestoTrabajo'])
            ->get();

        $resultado = [];

        foreach ($hallazgos as $hallazgo) {
            $tipo = $hallazgo->tipoHallazgo->nombre ?? 'Desconocido';
            $operario = 'Sin asignación';

            // Obtener operario a través de: ubicacion → puesto_trabajo → operarios_por_dia
            if ($hallazgo->ubicacion && $hallazgo->ubicacion->puestoTrabajo) {
                $fechaEfectiva = $hallazgo->getFechaOperacionEfectiva()->toDateString();

                $operarioPorDia = DB::table('operarios_por_dia')
                    ->where('puesto_trabajo_id', $hallazgo->ubicacion->puestoTrabajo->id)
                    ->whereDate('fecha_operacion', $fechaEfectiva)
                    ->first();

                if ($operarioPorDia) {
                    $operarioObj = DB::table('operarios')
                        ->where('id', $operarioPorDia->operario_id)
                        ->first();
                    if ($operarioObj) {
                        $operario = $operarioObj->nombre;
                    }
                }
            }

            if (in_array($tipo, ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'])) {
                $clave = $operario . ' | ' . $tipo;
                $resultado[$clave] = ($resultado[$clave] ?? 0) + 1;
            }
        }

        return $resultado;
    }

    /**
     * Calcula hallazgos agrupados por operario y tipo usando la lógica de obtenerOperarioResponsable
     */
    private function calcularHallazgosPorOperarioYTipo($hallazgosRango)
    {
        $resultado = [];

        foreach ($hallazgosRango as $registro) {
            $operarioResponsable = $this->obtenerOperarioResponsable($registro);
            $tipoHallazgo = $registro->tipoHallazgo->nombre ?? 'Desconocido';
            $cantidad = $registro->cantidad ?? 1;

            $clave = $operarioResponsable . ' | ' . $tipoHallazgo;

            if (!isset($resultado[$clave])) {
                $resultado[$clave] = 0;
            }

            $resultado[$clave] += $cantidad;
        }

        return collect($resultado)->sortDesc();
    }

    /**
     * Obtiene el operario responsable basado en el tipo de hallazgo, ubicación, producto, etc.
     * Copia de la lógica del HistorialRegistros
     */
    private function obtenerOperarioResponsable($registro)
    {
        $puestoTrabajoNombre = null;
        $tipoHallazgo = strtoupper($registro->tipoHallazgo->nombre ?? '');
        $producto = $registro->producto->nombre ?? '';
        $lado = strtoupper($registro->lado->nombre ?? '');
        $ubicacion = strtoupper($registro->ubicacion->nombre ?? '');

        // Determinar la paridad (PAR o IMPAR)
        $paridad = '';
        if (in_array($lado, ['PAR', 'IMPAR'])) {
            $paridad = $lado;
        } elseif (is_numeric($registro->numero_canal)) {
            $paridad = ($registro->numero_canal % 2 == 0) ? 'PAR' : 'IMPAR';
        }

        $esMediaCanal1 = strtoupper($producto) === 'MEDIA CANAL 1 LENGUA';
        $esMediaCanal2 = strtoupper($producto) === 'MEDIA CANAL 2 COLA';

        switch (true) {
            // COBERTURA DE GRASA
            case (str_contains($tipoHallazgo, 'COBERTURA') && str_contains($tipoHallazgo, 'GRASA')):
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

            // CORTE EN PIERNA
            case str_contains($tipoHallazgo, 'CORTE') && str_contains($tipoHallazgo, 'PIERNA'):
                if ($esMediaCanal1) {
                    $puestoTrabajoNombre = ($paridad === 'PAR') ? 'PRIMERA PAR' : 'PRIMERA IMPAR';
                } elseif ($esMediaCanal2) {
                    $puestoTrabajoNombre = ($paridad === 'PAR') ? 'SEGUNDA PAR' : 'SEGUNDA IMPAR';
                }
                break;

            // SOBREBARRIGA ROTA
            case str_contains($tipoHallazgo, 'SOBREBARRIGA'):
                if ($esMediaCanal1) {
                    $puestoTrabajoNombre = 'ZAPATA IZQUIERDA';
                } elseif ($esMediaCanal2) {
                    $puestoTrabajoNombre = 'ZAPATA DERECHA';
                }
                break;

            // HEMATOMAS (cualquier variante)
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
                    $fechaOperacion = !empty($registro->fecha_operacion) 
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
                // Log the exception for debugging
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

        return 'Sin asignar';
    }

    /**
     * Cuenta los hallazgos nuevos (MATERIA FECAL, CONTENIDO RUMINAL, LECHE VISIBLE)
     */
    private function contarHallazgosNuevos($hallazgosRango)
    {
        $tiposNuevos = ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'];
        
        $resultado = [];
        foreach ($tiposNuevos as $tipo) {
            $resultado[$tipo] = $hallazgosRango
                ->filter(function ($h) use ($tipo) {
                    return stripos($h->tipoHallazgo->nombre ?? '', $tipo) !== false;
                })
                ->count();
        }
        
        return $resultado;
    }
}
