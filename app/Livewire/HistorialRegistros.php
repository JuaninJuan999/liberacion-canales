<?php

namespace App\Livewire;

use App\Models\Lado;
use App\Models\Producto;
use App\Models\RegistroHallazgo;
use App\Models\TipoHallazgo;
use App\Models\Ubicacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithPagination;

class HistorialRegistros extends Component
{
    use WithPagination;

    // Filtros
    public $fecha_inicio;

    public $fecha_fin;

    public $producto_id = '';

    public $tipo_hallazgo_id = '';

    public $numero_canal = '';

    public $solo_criticos = false;

    // Opciones de filtros
    public $productos = [];

    public $tiposHallazgo = [];

    public $ubicacionesLista = [];

    public $ladosLista = [];

    // Modal edición (administrador o calidad)
    public $mostrarModalEditar = false;

    public $edit_id;

    public $edit_codigo = '';

    public $edit_producto_id;

    public $edit_tipo_hallazgo_id;

    public $edit_ubicacion_id;

    public $edit_lado_id;

    public $edit_cantidad = 1;

    public $edit_fecha_operacion;

    public $edit_observacion = '';

    public $mostrarUbicacionEdit = false;

    public $mostrarLadoEdit = false;

    public $editUbicacionesFiltradas = [];

    /** @var string */
    public $nombreHallazgoEditSeleccionado = '';

    /** @var string */
    public $nombreUbicacionEditSeleccionada = '';

    // Paginación
    public $perPage = 15;

    // Modal de evidencia
    public $mostrarModalEvidencia = false;

    public $evidenciaMostradaUrl = '';

    /** Modal historial de ediciones (trazabilidad) */
    public $mostrarModalHistorialEdiciones = false;

    /** @var array<int, array<string, mixed>> */
    public $historialEdicionesVista = [];

    // Estadísticas del filtro
    public $totalRegistros = 0;

    public $totalCriticos = 0;

    public $totalLeves = 0;

    public $estadisticasPorTipo = [];

    protected $queryString = [
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
        'producto_id' => ['except' => ''],
        'tipo_hallazgo_id' => ['except' => ''],
    ];

    public function mount()
    {
        // Establecer fechas por defecto (hoy)
        $this->fecha_inicio = $this->fecha_inicio ?: Carbon::now()->format('Y-m-d');
        $this->fecha_fin = $this->fecha_fin ?: Carbon::now()->format('Y-m-d');

        $this->cargarOpciones();
        $this->calcularEstadisticas();
    }

    public function cargarOpciones()
    {
        $this->productos = Producto::orderBy('nombre')->get();
        $this->tiposHallazgo = TipoHallazgo::orderBy('nombre')->get();
        $this->ubicacionesLista = Ubicacion::orderBy('nombre')->get();
        $this->ladosLista = Lado::orderBy('nombre')->get();
    }

    public function usuarioEsAdministrador(): bool
    {
        $u = auth()->user();

        return $u && $u->rol && $u->rol->nombre === 'ADMINISTRADOR';
    }

    /** Administrador o Calidad: editar y ver trazabilidad de ediciones. */
    public function usuarioPuedeEditarOHistorialHallazgos(): bool
    {
        $u = auth()->user();
        if (! $u || ! $u->rol) {
            return false;
        }

        return in_array($u->rol->nombre, ['ADMINISTRADOR', 'CALIDAD'], true);
    }

    public function abrirEditar(int $id): void
    {
        if (! $this->usuarioPuedeEditarOHistorialHallazgos()) {
            session()->flash('error', 'No tienes permiso para editar registros.');

            return;
        }

        $r = RegistroHallazgo::query()->findOrFail($id);
        $this->edit_id = $r->id;
        $this->edit_codigo = $r->codigo;
        $this->edit_producto_id = $r->producto_id;
        $this->edit_tipo_hallazgo_id = $r->tipo_hallazgo_id;
        $this->edit_ubicacion_id = $r->ubicacion_id;
        $this->edit_lado_id = $r->lado_id;
        $this->edit_cantidad = max(1, (int) $r->cantidad);
        $this->edit_fecha_operacion = $r->fecha_operacion?->format('Y-m-d');
        $this->edit_observacion = (string) ($r->observacion ?? '');

        $this->sincronizarFlagsEdicionSinResetearValores();
        $this->mostrarModalEditar = true;
    }

    public function cerrarEditar(): void
    {
        $this->mostrarModalEditar = false;
        $this->reset([
            'edit_id', 'edit_codigo', 'edit_producto_id', 'edit_tipo_hallazgo_id',
            'edit_ubicacion_id', 'edit_lado_id', 'edit_cantidad', 'edit_fecha_operacion',
            'edit_observacion', 'mostrarUbicacionEdit', 'mostrarLadoEdit', 'editUbicacionesFiltradas',
            'nombreHallazgoEditSeleccionado', 'nombreUbicacionEditSeleccionada',
        ]);
    }

    public function updatedEditTipoHallazgoId($value): void
    {
        $this->reset(['edit_ubicacion_id', 'edit_lado_id', 'nombreUbicacionEditSeleccionada']);
        $this->mostrarUbicacionEdit = false;
        $this->mostrarLadoEdit = false;
        $this->editUbicacionesFiltradas = [];

        if ($value) {
            $hallazgo = TipoHallazgo::find($value);
            $this->nombreHallazgoEditSeleccionado = $hallazgo ? trim(strtoupper($hallazgo->nombre)) : '';

            if ($this->nombreHallazgoEditSeleccionado === 'COBERTURA DE GRASA') {
                $this->mostrarUbicacionEdit = true;
                $this->editUbicacionesFiltradas = Ubicacion::whereIn('nombre', ['Cadera', 'Pierna'])
                    ->orderBy('nombre')
                    ->get();
            } elseif (str_contains($this->nombreHallazgoEditSeleccionado, 'CORTE') && str_contains($this->nombreHallazgoEditSeleccionado, 'PIERNA')) {
                $this->mostrarLadoEdit = true;
            }
        } else {
            $this->nombreHallazgoEditSeleccionado = '';
        }
    }

    public function updatedEditUbicacionId($value): void
    {
        $this->reset('edit_lado_id');
        $this->mostrarLadoEdit = false;

        if ($value) {
            $ubicacion = Ubicacion::find($value);
            $this->nombreUbicacionEditSeleccionada = $ubicacion ? strtoupper($ubicacion->nombre) : '';

            if ($this->nombreUbicacionEditSeleccionada === 'PIERNA') {
                $this->mostrarLadoEdit = true;
            }
        } else {
            $this->nombreUbicacionEditSeleccionada = '';
        }
    }

    /**
     * Actualiza visibilidad de ubicación / lado según tipo y ubicación actuales, sin vaciar los IDs (apertura del modal y validación).
     */
    protected function sincronizarFlagsEdicionSinResetearValores(): void
    {
        $this->mostrarUbicacionEdit = false;
        $this->mostrarLadoEdit = false;
        $this->editUbicacionesFiltradas = [];
        $this->nombreHallazgoEditSeleccionado = '';
        $this->nombreUbicacionEditSeleccionada = '';

        if (! $this->edit_tipo_hallazgo_id) {
            return;
        }

        $hallazgo = TipoHallazgo::find($this->edit_tipo_hallazgo_id);
        $this->nombreHallazgoEditSeleccionado = $hallazgo ? trim(strtoupper($hallazgo->nombre)) : '';

        if ($this->nombreHallazgoEditSeleccionado === 'COBERTURA DE GRASA') {
            $this->mostrarUbicacionEdit = true;
            $this->editUbicacionesFiltradas = Ubicacion::whereIn('nombre', ['Cadera', 'Pierna'])
                ->orderBy('nombre')
                ->get();
        } elseif (str_contains($this->nombreHallazgoEditSeleccionado, 'CORTE') && str_contains($this->nombreHallazgoEditSeleccionado, 'PIERNA')) {
            $this->mostrarLadoEdit = true;
        }

        if ($this->edit_ubicacion_id) {
            $ubicacion = Ubicacion::find($this->edit_ubicacion_id);
            $this->nombreUbicacionEditSeleccionada = $ubicacion ? strtoupper($ubicacion->nombre) : '';
            if ($this->nombreUbicacionEditSeleccionada === 'PIERNA') {
                $this->mostrarLadoEdit = true;
            }
        }
    }

    public function guardarEdicion(): void
    {
        if (! $this->usuarioPuedeEditarOHistorialHallazgos()) {
            session()->flash('error', 'No tienes permiso para editar registros.');

            return;
        }

        $this->sincronizarFlagsEdicionSinResetearValores();

        foreach (['edit_producto_id', 'edit_tipo_hallazgo_id', 'edit_ubicacion_id', 'edit_lado_id'] as $campo) {
            if ($this->{$campo} === '' || $this->{$campo} === null) {
                $this->{$campo} = null;
            }
        }

        $rules = [
            'edit_codigo' => 'required|string|max:50',
            'edit_producto_id' => 'required|exists:productos,id',
            'edit_tipo_hallazgo_id' => 'required|exists:tipos_hallazgo,id',
            'edit_fecha_operacion' => 'required|date',
            'edit_cantidad' => 'required|integer|min:1',
            'edit_observacion' => 'nullable|string',
        ];

        if ($this->mostrarUbicacionEdit) {
            $rules['edit_ubicacion_id'] = 'required|exists:ubicaciones,id';
        } else {
            $rules['edit_ubicacion_id'] = 'nullable|exists:ubicaciones,id';
        }

        if ($this->mostrarLadoEdit) {
            $rules['edit_lado_id'] = 'required|exists:lados,id';
        } else {
            $rules['edit_lado_id'] = 'nullable|exists:lados,id';
        }

        $this->validate($rules, [
            'edit_producto_id.required' => 'Debe seleccionar un producto.',
            'edit_tipo_hallazgo_id.required' => 'Debe seleccionar el tipo de hallazgo.',
            'edit_codigo.required' => 'Debe ingresar el código del canal.',
            'edit_ubicacion_id.required' => 'Debe seleccionar la ubicación.',
            'edit_lado_id.required' => 'Debe indicar si es par o impar.',
        ]);

        $r = RegistroHallazgo::query()
            ->with(['producto', 'tipoHallazgo', 'ubicacion', 'lado'])
            ->findOrFail($this->edit_id);

        $antes = $this->snapshotLegible($r);
        $despues = $this->snapshotDesdeFormulario();

        if ($antes === $despues) {
            session()->flash('message', 'No se detectaron cambios en el registro.');
            $this->cerrarEditar();

            return;
        }

        $historial = $r->ediciones_historial;
        if (! is_array($historial)) {
            $historial = [];
        }

        $historial[] = [
            'editado_en' => now()->toIso8601String(),
            'usuario_id' => auth()->id(),
            'usuario_nombre' => auth()->user()->name ?? '—',
            'antes' => $antes,
            'despues' => $despues,
        ];

        $r->update([
            'codigo' => $this->edit_codigo,
            'producto_id' => $this->edit_producto_id,
            'tipo_hallazgo_id' => $this->edit_tipo_hallazgo_id,
            'ubicacion_id' => $this->mostrarUbicacionEdit ? $this->edit_ubicacion_id : null,
            'lado_id' => $this->mostrarLadoEdit ? $this->edit_lado_id : null,
            'cantidad' => $this->edit_cantidad,
            'fecha_operacion' => $this->edit_fecha_operacion,
            'observacion' => $this->edit_observacion !== '' ? $this->edit_observacion : null,
            'ediciones_historial' => $historial,
        ]);

        session()->flash('message', 'Registro actualizado correctamente.');
        $this->cerrarEditar();
        $this->calcularEstadisticas();
    }

    public function abrirHistorialEdiciones(int $id): void
    {
        if (! $this->usuarioPuedeEditarOHistorialHallazgos()) {
            session()->flash('error', 'No tienes permiso para ver el historial de ediciones.');

            return;
        }

        $r = RegistroHallazgo::query()->findOrFail($id);
        $items = $r->ediciones_historial;
        if (! is_array($items) || $items === []) {
            session()->flash('error', 'Este registro no tiene historial de ediciones.');

            return;
        }

        $this->historialEdicionesVista = $items;
        $this->mostrarModalHistorialEdiciones = true;
    }

    public function cerrarHistorialEdiciones(): void
    {
        $this->mostrarModalHistorialEdiciones = false;
        $this->historialEdicionesVista = [];
    }

    /**
     * @return array<string, string>
     */
    protected function snapshotLegible(RegistroHallazgo $r): array
    {
        return [
            'fecha_operacion' => $r->fecha_operacion?->format('Y-m-d') ?? '',
            'codigo' => (string) $r->codigo,
            'producto' => $r->producto?->nombre ?? '—',
            'tipo_hallazgo' => $r->tipoHallazgo?->nombre ?? '—',
            'ubicacion' => $r->ubicacion?->nombre ?? '—',
            'lado' => $r->lado?->nombre ?? '—',
            'cantidad' => (string) (int) $r->cantidad,
            'observacion' => trim((string) ($r->observacion ?? '')) !== '' ? trim((string) $r->observacion) : '—',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function snapshotDesdeFormulario(): array
    {
        $ubicacionNombre = '—';
        if ($this->mostrarUbicacionEdit && $this->edit_ubicacion_id) {
            $ubicacionNombre = Ubicacion::query()->whereKey($this->edit_ubicacion_id)->value('nombre') ?? '—';
        }

        $ladoNombre = '—';
        if ($this->mostrarLadoEdit && $this->edit_lado_id) {
            $ladoNombre = Lado::query()->whereKey($this->edit_lado_id)->value('nombre') ?? '—';
        }

        $obs = trim((string) $this->edit_observacion);

        return [
            'fecha_operacion' => (string) $this->edit_fecha_operacion,
            'codigo' => (string) $this->edit_codigo,
            'producto' => Producto::query()->whereKey($this->edit_producto_id)->value('nombre') ?? '—',
            'tipo_hallazgo' => TipoHallazgo::query()->whereKey($this->edit_tipo_hallazgo_id)->value('nombre') ?? '—',
            'ubicacion' => $ubicacionNombre,
            'lado' => $ladoNombre,
            'cantidad' => (string) (int) $this->edit_cantidad,
            'observacion' => $obs !== '' ? $obs : '—',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function etiquetasCampoHistorial(): array
    {
        return [
            'fecha_operacion' => 'Fecha de operación',
            'codigo' => 'Código (canal)',
            'producto' => 'Producto',
            'tipo_hallazgo' => 'Tipo de hallazgo',
            'ubicacion' => 'Ubicación',
            'lado' => 'Lado',
            'cantidad' => 'Cantidad',
            'observacion' => 'Observación',
        ];
    }

    public function mostrarEvidencia($registroId)
    {
        $registro = RegistroHallazgo::findOrFail($registroId);
        if ($registro->evidencia_path) {
            $ruta = $registro->evidencia_path;

            // Si la ruta no contiene "hallazgos/", agregarla
            if (! str_contains($ruta, 'hallazgos/')) {
                $ruta = 'hallazgos/'.$ruta;
            }

            $this->evidenciaMostradaUrl = '/storage/'.$ruta;
            $this->mostrarModalEvidencia = true;
        }
    }

    public function cerrarModalEvidencia()
    {
        $this->mostrarModalEvidencia = false;
        $this->evidenciaMostradaUrl = '';
    }

    public function buscar()
    {
        $this->aplicarFiltros();
    }

    public function aplicarFiltros()
    {
        $this->resetPage();
        $this->calcularEstadisticas();
    }

    public function limpiarFiltros()
    {
        $this->reset([
            'producto_id',
            'tipo_hallazgo_id',
            'numero_canal',
            'solo_criticos',
        ]);

        $this->fecha_inicio = Carbon::now()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->format('Y-m-d');

        $this->aplicarFiltros();
    }

    public function calcularEstadisticas()
    {
        $query = $this->construirQuery();

        $stats = (clone $query)
            ->select(
                DB::raw('COUNT(registros_hallazgos.id) as total'),
                DB::raw('SUM(CASE WHEN tipos_hallazgo.es_critico = TRUE THEN 1 ELSE 0 END) as criticos')
            )
            ->join('tipos_hallazgo', 'registros_hallazgos.tipo_hallazgo_id', '=', 'tipos_hallazgo.id')
            ->first();

        $this->totalRegistros = $stats->total ?? 0;
        $this->totalCriticos = $stats->criticos ?? 0;
        $this->totalLeves = $this->totalRegistros - $this->totalCriticos;

        // Calcular estadísticas por tipo de hallazgo
        $estadisticas = (clone $query)
            ->select(
                'tipos_hallazgo.nombre',
                DB::raw('COUNT(registros_hallazgos.id) as cantidad')
            )
            ->join('tipos_hallazgo', 'registros_hallazgos.tipo_hallazgo_id', '=', 'tipos_hallazgo.id')
            ->groupBy('tipos_hallazgo.nombre')
            ->orderByDesc('cantidad')
            ->get();

        $this->estadisticasPorTipo = $estadisticas->keyBy('nombre')->map(function ($item) {
            return $item->cantidad;
        })->toArray();
    }

    protected function construirQuery()
    {
        return RegistroHallazgo::query()
            ->with(['producto', 'tipoHallazgo', 'puestoTrabajo', 'operario', 'usuario', 'ubicacion', 'lado'])
            ->porRangoFechasConTurno($this->fecha_inicio, $this->fecha_fin)
            ->when($this->producto_id, function ($query) {
                $query->where('registros_hallazgos.producto_id', $this->producto_id);
            })
            ->when($this->tipo_hallazgo_id, function ($query) {
                $query->where('registros_hallazgos.tipo_hallazgo_id', $this->tipo_hallazgo_id);
            })
            ->when($this->numero_canal, function ($query) {
                $query->where('registros_hallazgos.codigo', 'like', "%{$this->numero_canal}%");
            })
            ->when($this->solo_criticos, function ($query) {
                $query->whereHas('tipoHallazgo', function ($q) {
                    $q->where('es_critico', true);
                });
            });
    }

    public function obtenerOperarioResponsable($registro)
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
        } elseif (is_numeric($registro->codigo)) {
            $paridad = ((int) $registro->codigo % 2 == 0) ? 'PAR' : 'IMPAR';
        }

        $esMediaCanal1 = strtoupper($producto) === 'MEDIA CANAL 1 LENGUA';
        $esMediaCanal2 = strtoupper($producto) === 'MEDIA CANAL 2 COLA';

        switch (true) {
            // COBERTURA DE GRASA
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

        return 'Aun no se ha ingresado operario a la fecha de hoy';
    }

    public function eliminarRegistro($id): void
    {
        if (! $this->usuarioEsAdministrador()) {
            session()->flash('error', 'No tienes permiso para eliminar registros.');

            return;
        }

        try {
            $registro = RegistroHallazgo::findOrFail($id);
            $ruta = $registro->evidencia_path;
            if ($ruta) {
                $rel = ltrim(str_replace('\\', '/', (string) $ruta), '/');
                if (str_starts_with($rel, 'storage/')) {
                    $rel = substr($rel, strlen('storage/'));
                }
                if ($rel !== '' && ! str_contains($rel, '..') && Storage::disk('public')->exists($rel)) {
                    Storage::disk('public')->delete($rel);
                }
            }
            $registro->delete();

            session()->flash('message', 'Registro eliminado correctamente');

            $this->calcularEstadisticas();
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el registro');
        }
    }

    public function exportarExcel()
    {
        return redirect()->route('exportar.hallazgos', [
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'producto_id' => $this->producto_id,
            'tipo_hallazgo_id' => $this->tipo_hallazgo_id,
            'numero_canal' => $this->numero_canal,
            'solo_criticos' => $this->solo_criticos,
        ]);
    }

    public function render()
    {
        $registros = $this->construirQuery()
            ->orderBy('registros_hallazgos.created_at', 'desc')
            ->paginate($this->perPage);

        return view('livewire.historial-registros', [
            'registros' => $registros,
        ])->layout('layouts.app');
    }
}
