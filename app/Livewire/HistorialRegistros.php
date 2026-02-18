<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\RegistroHallazgo;
use App\Models\TipoHallazgo;
use App\Models\PuestoTrabajo;
use App\Models\Operario;
use App\Models\Producto;
use Carbon\Carbon;

class HistorialRegistros extends Component
{
    use WithPagination;
    
    // Filtros
    public $fecha_inicio;
    public $fecha_fin;
    public $producto_id = '';
    public $tipo_hallazgo_id = '';
    public $puesto_trabajo_id = '';
    public $operario_id = '';
    public $numero_canal = '';
    public $solo_criticos = false;
    
    // Opciones de filtros
    public $productos = [];
    public $tiposHallazgo = [];
    public $puestos = [];
    public $operarios = [];
    
    // Paginación
    public $perPage = 15;
    
    // Estadísticas del filtro
    public $totalRegistros = 0;
    public $totalCriticos = 0;
    public $totalLeves = 0;
    
    protected $queryString = [
        'fecha_inicio' => ['except' => ''],
        'fecha_fin' => ['except' => ''],
        'producto_id' => ['except' => ''],
        'tipo_hallazgo_id' => ['except' => ''],
        'puesto_trabajo_id' => ['except' => ''],
        'operario_id' => ['except' => ''],
    ];
    
    public function mount()
    {
        // Establecer fechas por defecto (hoy)
        $this->fecha_inicio = $this->fecha_inicio ?: Carbon::now()->format('Y-m-d');
        $this->fecha_fin = $this->fecha_fin ?: Carbon::now()->format('Y-m-d');
        
        $this->cargarOpciones();
    }
    
    public function cargarOpciones()
    {
        $this->productos = Producto::orderBy('nombre')->get();
        $this->tiposHallazgo = TipoHallazgo::orderBy('nombre')->get();
        $this->puestos = PuestoTrabajo::orderBy('orden')->get();
        $this->operarios = Operario::where('activo', true)->orderBy('nombre_completo')->get();
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
            'puesto_trabajo_id',
            'operario_id',
            'numero_canal',
            'solo_criticos'
        ]);
        
        $this->fecha_inicio = Carbon::now()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->format('Y-m-d');
        
        $this->resetPage();
    }
    
    public function calcularEstadisticas()
    {
        $query = $this->construirQuery();
        
        $this->totalRegistros = $query->count();
        $this->totalCriticos = $query->whereHas('tipoHallazgo', function($q) {
            $q->where('es_critico', true);
        })->count();
        $this->totalLeves = $this->totalRegistros - $this->totalCriticos;
    }
    
    protected function construirQuery()
    {
        return RegistroHallazgo::query()
            ->with(['producto', 'tipoHallazgo', 'ubicacion', 'lado', 'puestoTrabajo', 'operario'])
            ->when($this->fecha_inicio, function($query) {
                $query->whereDate('created_at', '>=', $this->fecha_inicio);
            })
            ->when($this->fecha_fin, function($query) {
                $query->whereDate('created_at', '<=', $this->fecha_fin);
            })
            ->when($this->producto_id, function($query) {
                $query->where('producto_id', $this->producto_id);
            })
            ->when($this->tipo_hallazgo_id, function($query) {
                $query->where('tipo_hallazgo_id', $this->tipo_hallazgo_id);
            })
            ->when($this->puesto_trabajo_id, function($query) {
                $query->where('puesto_trabajo_id', $this->puesto_trabajo_id);
            })
            ->when($this->operario_id, function($query) {
                $query->where('operario_id', $this->operario_id);
            })
            ->when($this->numero_canal, function($query) {
                $query->where('numero_canal', 'like', "%{$this->numero_canal}%");
            })
            ->when($this->solo_criticos, function($query) {
                $query->whereHas('tipoHallazgo', function($q) {
                    $q->where('es_critico', true);
                });
            });
    }
    
    public function eliminarRegistro($id)
    {
        try {
            $registro = RegistroHallazgo::findOrFail($id);
            $registro->delete();
            
            session()->flash('message', 'Registro eliminado correctamente');
            
            // Recalcular estadísticas
            $this->calcularEstadisticas();
            
        } catch (\Exception $e) {
            session()->flash('error', 'Error al eliminar el registro');
        }
    }
    
    public function exportarExcel()
    {
        return redirect()->route('hallazgos.exportar', [
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'producto_id' => $this->producto_id,
            'tipo_hallazgo_id' => $this->tipo_hallazgo_id,
            'puesto_trabajo_id' => $this->puesto_trabajo_id,
            'operario_id' => $this->operario_id
        ]);
    }
    
    public function updated($propertyName)
    {
        // Recalcular estadísticas cuando cambia un filtro
        if (in_array($propertyName, [
            'fecha_inicio', 'fecha_fin', 'producto_id', 'tipo_hallazgo_id',
            'puesto_trabajo_id', 'operario_id', 'numero_canal', 'solo_criticos'
        ])) {
            $this->calcularEstadisticas();
        }
    }
    
    public function render()
    {
        $registros = $this->construirQuery()
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
        
        return view('livewire.historial-registros', [
            'registros' => $registros
        ]);
    }
}