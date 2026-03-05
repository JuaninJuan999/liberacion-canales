<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\HallazgoToleranciaZero;
use App\Models\TipoHallazgo;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HistorialRegistrosToleranciaZero extends Component
{
    use WithPagination;
    
    // Filtros
    public $fecha_inicio;
    public $fecha_fin;
    public $producto_id = '';
    public $tipo_hallazgo_id = '';
    public $codigo = '';
    
    // Opciones de filtros
    public $productos = [];
    public $tiposHallazgo = [];
    
    // Paginación
    public $perPage = 15;

    // Estadísticas del filtro
    public $totalRegistros = 0;
    public $materiaFecalTotal = 0;
    public $contenidoRuminalTotal = 0;
    public $lecheVisibleTotal = 0;
    
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
        // Cargar solo productos de Tolerancia Cero
        $this->productos = Producto::whereIn('nombre', ['CUARTO ANTERIOR', 'CUARTO POSTERIOR'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        
        // Cargar solo tipos de Tolerancia Cero
        $this->tiposHallazgo = TipoHallazgo::whereIn('nombre', [
            'MATERIA FECAL',
            'CONTENIDO RUMINAL',
            'LECHE VISIBLE'
        ])
            ->orderBy('nombre')
            ->get();
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
            'codigo'
        ]);
        
        $this->fecha_inicio = Carbon::now()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->format('Y-m-d');
        
        $this->aplicarFiltros();
    }
    
    public function calcularEstadisticas()
    {
        $query = $this->construirQuery();

        $this->totalRegistros = (clone $query)->count();

        $this->materiaFecalTotal = (clone $query)
            ->whereHas('tipoHallazgo', function($q) {
                $q->where('nombre', 'MATERIA FECAL');
            })
            ->count();

        $this->contenidoRuminalTotal = (clone $query)
            ->whereHas('tipoHallazgo', function($q) {
                $q->where('nombre', 'CONTENIDO RUMINAL');
            })
            ->count();

        $this->lecheVisibleTotal = (clone $query)
            ->whereHas('tipoHallazgo', function($q) {
                $q->where('nombre', 'LECHE VISIBLE');
            })
            ->count();
    }
    
    protected function construirQuery()
    {
        return HallazgoToleranciaZero::query()
            ->with(['producto', 'tipoHallazgo', 'usuario'])
            ->whereBetween('fecha_operacion', [
                Carbon::parse($this->fecha_inicio)->startOfDay(),
                Carbon::parse($this->fecha_fin)->endOfDay()
            ])
            ->when($this->producto_id, function($query) {
                $query->where('producto_id', $this->producto_id);
            })
            ->when($this->tipo_hallazgo_id, function($query) {
                $query->where('tipo_hallazgo_id', $this->tipo_hallazgo_id);
            })
            ->when($this->codigo, function($query) {
                $query->where('codigo', 'like', "%{$this->codigo}%");
            })
            ->orderBy('fecha_registro', 'desc');
    }

    public function render()
    {
        $registros = $this->construirQuery()->paginate($this->perPage);
        
        return view('livewire.historial-registros-tolerancia-cero', [
            'registros' => $registros
        ]);
    }
}
