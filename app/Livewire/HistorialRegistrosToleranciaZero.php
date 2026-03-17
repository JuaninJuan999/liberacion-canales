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

    private function normalizarRol(?string $rol): string
    {
        $rolNormalizado = strtoupper(trim((string) $rol));
        return $rolNormalizado === 'ADMIN' ? 'ADMINISTRADOR' : $rolNormalizado;
    }
    
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
        // Verificar que solo Admin, Operaciones, Calidad y Gerencia pueden acceder
        if (!auth()->check()) {
            abort(401, 'Debes estar autenticado.');
        }

        $usuario = auth()->user();
        $rolesPermitidos = ['ADMINISTRADOR', 'OPERACIONES', 'CALIDAD', 'GERENCIA'];

        $rolUsuario = $this->normalizarRol($usuario->rol?->nombre);
        if (!$usuario->rol || !in_array($rolUsuario, $rolesPermitidos, true)) {
            abort(403, 'No tienes permiso para acceder a este módulo. Se requiere rol ADMINISTRADOR, OPERACIONES, CALIDAD o GERENCIA.');
        }

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
        return HallazgoToleranciaZero::porRangoFechasConTurno($this->fecha_inicio, $this->fecha_fin)
            ->with(['producto', 'tipoHallazgo', 'usuario', 'ubicacion.puestoTrabajo'])
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

    /**
     * Obtiene el operario responsable basado en la ubicación del registro
     */
    public function obtenerOperarioResponsable($registro)
    {
        // Si no hay ubicación, no podemos determinar el operario
        if (!$registro->ubicacion) {
            return '-';
        }

        // Si la ubicación no tiene puesto_trabajo asignado, retornar guion
        if (!$registro->ubicacion->puesto_trabajo_id) {
            return '-';
        }

        // Buscar el operario asignado a ese puesto en esa fecha
        $operarioPorDia = \App\Models\OperarioPorDia::whereDate('fecha_operacion', $registro->fecha_operacion)
            ->where('puesto_trabajo_id', $registro->ubicacion->puesto_trabajo_id)
            ->with('operario')
            ->first();

        if ($operarioPorDia && $operarioPorDia->operario) {
            return $operarioPorDia->operario->nombre;
        }

        return '-';
    }
}
