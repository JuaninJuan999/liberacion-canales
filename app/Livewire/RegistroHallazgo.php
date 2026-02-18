<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\RegistroHallazgo as ModeloRegistroHallazgo;
use App\Models\TipoHallazgo;
use App\Models\Ubicacion;
use App\Models\Lado;
use App\Models\PuestoTrabajo;
use App\Models\Operario;
use App\Models\Producto;
use App\Models\AnimalProcesado;
use App\Services\CalculadoraIndicadores;
use Carbon\Carbon;

class RegistroHallazgo extends Component
{
    // Datos del formulario
    public $producto_id;
    public $tipo_hallazgo_id;
    public $ubicacion_id;
    public $lado_id;
    public $puesto_trabajo_id;
    public $operario_id;
    public $numero_canal;
    public $observaciones;
    public $foto;
    
    // Datos complementarios
    public $fecha_actual;
    public $total_registros_dia = 0;
    
    // Colección para select
    public $productos = [];
    public $tiposHallazgo = [];
    public $ubicaciones = [];
    public $lados = [];
    public $puestosTrabajo = [];
    public $operarios = [];
    
    // Mensajes
    public $mensaje = '';
    public $tipoMensaje = 'success';
    
    protected $rules = [
        'producto_id' => 'required|exists:productos,id',
        'tipo_hallazgo_id' => 'required|exists:tipos_hallazgo,id',
        'ubicacion_id' => 'required|exists:ubicaciones,id',
        'lado_id' => 'required|exists:lados,id',
        'puesto_trabajo_id' => 'required|exists:puestos_trabajo,id',
        'operario_id' => 'required|exists:operarios,id',
        'numero_canal' => 'required|string|max:50',
        'observaciones' => 'nullable|string|max:500',
    ];
    
    protected $messages = [
        'producto_id.required' => 'Debe seleccionar un producto',
        'tipo_hallazgo_id.required' => 'Debe seleccionar el tipo de hallazgo',
        'ubicacion_id.required' => 'Debe seleccionar la ubicación',
        'lado_id.required' => 'Debe seleccionar el lado',
        'puesto_trabajo_id.required' => 'Debe seleccionar el puesto de trabajo',
        'operario_id.required' => 'Debe seleccionar el operario',
        'numero_canal.required' => 'Debe ingresar el número de canal',
    ];
    
    public function mount()
    {
        $this->fecha_actual = Carbon::now()->format('Y-m-d');
        $this->cargarDatos();
        $this->actualizarContadorDia();
    }
    
    public function cargarDatos()
    {
        $this->productos = Producto::where('activo', true)->orderBy('nombre')->get();
        $this->tiposHallazgo = TipoHallazgo::orderBy('nombre')->get();
        $this->ubicaciones = Ubicacion::orderBy('nombre')->get();
        $this->lados = Lado::orderBy('nombre')->get();
        $this->puestosTrabajo = PuestoTrabajo::orderBy('orden')->get();
        $this->operarios = Operario::where('activo', true)->orderBy('nombre_completo')->get();
    }
    
    public function actualizarContadorDia()
    {
        $this->total_registros_dia = ModeloRegistroHallazgo::whereDate('created_at', $this->fecha_actual)->count();
    }
    
    public function registrar()
    {
        $this->validate();
        
        try {
            // Crear el registro de hallazgo
            $registro = ModeloRegistroHallazgo::create([
                'producto_id' => $this->producto_id,
                'tipo_hallazgo_id' => $this->tipo_hallazgo_id,
                'ubicacion_id' => $this->ubicacion_id,
                'lado_id' => $this->lado_id,
                'puesto_trabajo_id' => $this->puesto_trabajo_id,
                'operario_id' => $this->operario_id,
                'numero_canal' => $this->numero_canal,
                'observaciones' => $this->observaciones,
                'registrado_por' => auth()->id(),
            ]);
            
            // Registrar el animal procesado si no existe
            AnimalProcesado::firstOrCreate(
                [
                    'numero_canal' => $this->numero_canal,
                    'fecha_procesamiento' => $this->fecha_actual
                ],
                [
                    'producto_id' => $this->producto_id,
                    'estado' => 'procesado'
                ]
            );
            
            $this->mensaje = '¡Hallazgo registrado exitosamente!';
            $this->tipoMensaje = 'success';
            
            // Limpiar formulario
            $this->reset([
                'tipo_hallazgo_id',
                'ubicacion_id',
                'lado_id',
                'observaciones'
            ]);
            
            // Incrementar número de canal automáticamente
            if (is_numeric($this->numero_canal)) {
                $this->numero_canal = (int)$this->numero_canal + 1;
            }
            
            $this->actualizarContadorDia();
            
            // Emitir evento para actualizar otros componentes
            $this->dispatch('hallazgo-registrado');
            
        } catch (\Exception $e) {
            $this->mensaje = 'Error al registrar hallazgo: ' . $e->getMessage();
            $this->tipoMensaje = 'error';
        }
    }
    
    public function limpiarFormulario()
    {
        $this->reset([
            'producto_id',
            'tipo_hallazgo_id',
            'ubicacion_id',
            'lado_id',
            'puesto_trabajo_id',
            'operario_id',
            'numero_canal',
            'observaciones'
        ]);
        
        $this->mensaje = '';
    }
    
    public function actualizarOperariosPorPuesto()
    {
        if ($this->puesto_trabajo_id) {
            // Filtrar operarios asignados a este puesto en el día actual
            $this->operarios = Operario::whereHas('operariosPorDia', function($query) {
                $query->where('fecha', $this->fecha_actual)
                      ->where('puesto_trabajo_id', $this->puesto_trabajo_id);
            })
            ->where('activo', true)
            ->orderBy('nombre_completo')
            ->get();
            
            // Resetear operario seleccionado
            $this->operario_id = null;
        }
    }
    
    public function render()
    {
        return view('livewire.registro-hallazgo');
    }
}