<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\HallazgoToleranciaZero;
use App\Models\TipoHallazgo;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RegistroHallazgoToleranciaZero extends Component
{
    // Form Data
    public $codigo;
    public $producto_id;
    public $tipo_hallazgo_id;
    public $observacion;

    // Complementary Data
    public $fecha_actual;
    public $total_registros_dia = 0;
    public $nombreProductoSeleccionado = '';
    public $nombreTipoSeleccionado = '';

    // Collections for Selects
    public $productos = [];
    public $tiposHallazgo = [];

    // Control Flags
    public $mostrarObservacion = false;

    // Messages
    public $mensaje = '';
    public $tipoMensaje = 'success';

    // Validation Messages
    protected $messages = [
        'codigo.required' => 'Debe ingresar el código del canal.',
        'producto_id.required' => 'Debe seleccionar el cuarto (Anterior o Posterior).',
        'tipo_hallazgo_id.required' => 'Debe seleccionar el tipo de hallazgo.',
    ];

    // Validation Rules
    protected function rules()
    {
        return [
            'codigo' => 'required|string|max:50',
            'producto_id' => 'required|exists:productos,id',
            'tipo_hallazgo_id' => 'required|exists:tipos_hallazgo,id',
            'observacion' => 'nullable|string|max:500',
        ];
    }

    public function mount()
    {
        $this->fecha_actual = Carbon::now()->format('Y-m-d');
        $this->cargarDatos();
        $this->actualizarTotalRegistros();
    }

    public function cargarDatos()
    {
        // Cargar solo los productos para tolerancia cero
        $this->productos = Producto::whereIn('nombre', ['CUARTO ANTERIOR', 'CUARTO POSTERIOR'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get()
            ->toArray();

        // Cargar solo los tipos de hallazgo para tolerancia cero
        $this->tiposHallazgo = TipoHallazgo::whereIn('nombre', [
            'MATERIA FECAL',
            'CONTENIDO RUMINAL',
            'LECHE VISIBLE'
        ])
            ->orderBy('nombre')
            ->get()
            ->toArray();
    }

    public function updatedProductoId()
    {
        if ($this->producto_id) {
            $producto = Producto::find($this->producto_id);
            $this->nombreProductoSeleccionado = $producto?->nombre ?? '';
        }
    }

    public function updatedTipoHallazgoId()
    {
        if ($this->tipo_hallazgo_id) {
            $tipoHallazgo = TipoHallazgo::find($this->tipo_hallazgo_id);
            $this->nombreTipoSeleccionado = $tipoHallazgo?->nombre ?? '';
        }
    }

    public function registrar()
    {
        $this->validate();

        try {
            $fechaOperacion = Carbon::now();
            
            // Ajustar fecha si se registra en horario madrugada (00:00 a 06:59)
            if ($fechaOperacion->hour < 7) {
                $fechaOperacion = $fechaOperacion->subDay();
            }

            HallazgoToleranciaZero::create([
                'fecha_registro' => Carbon::now(),
                'fecha_operacion' => $fechaOperacion->toDateString(),
                'codigo' => strtoupper($this->codigo),
                'producto_id' => $this->producto_id,
                'tipo_hallazgo_id' => $this->tipo_hallazgo_id,
                'usuario_id' => Auth::id(),
                'observacion' => $this->observacion,
            ]);

            // Actualizar indicadores del día
            $this->actualizarIndicadorDiario($fechaOperacion->toDateString());

            $this->resetearFormulario();
            $this->actualizarTotalRegistros();
            $this->mostrarMensaje('✅ Hallazgo registrado exitosamente', 'success');

            // Emitir evento para actualizar dashboards
            $this->dispatch('hallazgo-tolerancia-cero-registrado');

        } catch (\Exception $e) {
            $this->mostrarMensaje('❌ Error al registrar: ' . $e->getMessage(), 'error');
        }
    }

    private function actualizarIndicadorDiario($fechaOperacion)
    {
        $indicador = \App\Models\IndicadorDiario::firstOrCreate(
            ['fecha_operacion' => $fechaOperacion],
            [
                'mes' => Carbon::parse($fechaOperacion)->format('m'),
                'año' => Carbon::parse($fechaOperacion)->year,
            ]
        );

        // Contar totales por tipo
        $materia_fecal = HallazgoToleranciaZero::where('fecha_operacion', $fechaOperacion)
            ->where('tipo_hallazgo_id', function ($query) {
                $query->select('id')->from('tipos_hallazgo')->where('nombre', 'MATERIA FECAL');
            })
            ->count();

        $contenido_ruminal = HallazgoToleranciaZero::where('fecha_operacion', $fechaOperacion)
            ->where('tipo_hallazgo_id', function ($query) {
                $query->select('id')->from('tipos_hallazgo')->where('nombre', 'CONTENIDO RUMINAL');
            })
            ->count();

        $leche_visible = HallazgoToleranciaZero::where('fecha_operacion', $fechaOperacion)
            ->where('tipo_hallazgo_id', function ($query) {
                $query->select('id')->from('tipos_hallazgo')->where('nombre', 'LECHE VISIBLE');
            })
            ->count();

        $total = $materia_fecal + $contenido_ruminal + $leche_visible;

        $indicador->update([
            'materia_fecal_tc' => $materia_fecal,
            'contenido_ruminal_tc' => $contenido_ruminal,
            'leche_visible_tc' => $leche_visible,
            'total_hallazgos_tolerancia_cero' => $total,
        ]);
    }

    public function actualizarTotalRegistros()
    {
        $this->total_registros_dia = HallazgoToleranciaZero::where('fecha_operacion', $this->fecha_actual)
            ->count();
    }

    private function resetearFormulario()
    {
        $this->codigo = '';
        $this->producto_id = '';
        $this->tipo_hallazgo_id = '';
        $this->observacion = '';
        $this->nombreProductoSeleccionado = '';
        $this->nombreTipoSeleccionado = '';
    }

    private function mostrarMensaje($mensaje, $tipo)
    {
        $this->mensaje = $mensaje;
        $this->tipoMensaje = $tipo;
        
        // Limpiar mensaje después de 5 segundos
        $this->dispatch('messageTimeout');
    }

    public function render()
    {
        return view('livewire.registro-hallazgo-tolerancia-cero');
    }
}
