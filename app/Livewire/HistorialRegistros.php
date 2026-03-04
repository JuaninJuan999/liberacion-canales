<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RegistroHallazgo;
use App\Models\TipoHallazgo;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
    
    // Paginación
    public $perPage = 15;

    // Modal de evidencia
    public $mostrarModalEvidencia = false;
    public $evidenciaMostradaUrl = '';
    
    // Estadísticas del filtro
    public $totalRegistros = 0;
    public $totalCriticos = 0;
    public $totalLeves = 0;
    
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
    }

    public function mostrarEvidencia($registroId)
    {
        $registro = RegistroHallazgo::findOrFail($registroId);
        if ($registro->evidencia_path) {
            $this->evidenciaMostradaUrl = Storage::url($registro->evidencia_path);
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
            'solo_criticos'
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
                DB::raw('SUM(CASE WHEN tipos_hallazgo.es_critico = 1 THEN 1 ELSE 0 END) as criticos')
            )
            ->join('tipos_hallazgo', 'registros_hallazgos.tipo_hallazgo_id', '=', 'tipos_hallazgo.id')
            ->first();

        $this->totalRegistros = $stats->total ?? 0;
        $this->totalCriticos = $stats->criticos ?? 0;
        $this->totalLeves = $this->totalRegistros - $this->totalCriticos;
    }
    
    protected function construirQuery()
    {
        return RegistroHallazgo::query()
            ->with(['producto', 'tipoHallazgo', 'puestoTrabajo', 'operario', 'usuario', 'ubicacion', 'lado'])
            ->porRangoFechasConTurno($this->fecha_inicio, $this->fecha_fin)
            ->when($this->producto_id, function($query) {
                $query->where('registros_hallazgos.producto_id', $this->producto_id);
            })
            ->when($this->tipo_hallazgo_id, function($query) {
                $query->where('registros_hallazgos.tipo_hallazgo_id', $this->tipo_hallazgo_id);
            })
            ->when($this->numero_canal, function($query) {
                $query->where('registros_hallazgos.numero_canal', 'like', "%{$this->numero_canal}%");
            })
            ->when($this->solo_criticos, function($query) {
                $query->whereHas('tipoHallazgo', function($q) {
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

        return 'Aun no se ha ingresado operario a la fecha de hoy';
    }

    public function eliminarRegistro($id)
    {
        try {
            $registro = RegistroHallazgo::findOrFail($id);
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
            'registros' => $registros
        ])->layout('layouts.app');
    }
}
