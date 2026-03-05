<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\HallazgoToleranciaZero;
use App\Models\TipoHallazgo;
use App\Models\Producto;
use App\Models\Ubicacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RegistroHallazgoToleranciaZero extends Component
{
    // Form Data
    public $codigo;
    public $producto_id;
    public $tipo_hallazgo_id;
    public $ubicacion_id;

    // Complementary Data
    public $fecha_actual;
    public $total_registros_dia = 0;
    public $nombreProductoSeleccionado = '';
    public $nombreTipoSeleccionado = '';

    // Collections for Selects
    public $productos = [];
    public $tiposHallazgo = [];
    public $ubicaciones = [];
    public $ubicacionesDisponibles = [];

    // Control Flags
    public $mostrarUbicacion = false;

    // Messages
    public $mensaje = '';
    public $tipoMensaje = 'success';

    // Validation Messages
    protected $messages = [
        'codigo.required' => 'Debe ingresar el código del canal.',
        'producto_id.required' => 'Debe seleccionar el cuarto (Anterior o Posterior).',
        'tipo_hallazgo_id.required' => 'Debe seleccionar el tipo de hallazgo.',
        'ubicacion_id.required' => 'Debe seleccionar la ubicación específica.',
    ];

    // Validation Rules
    protected function rules()
    {
        $rules = [
            'codigo' => 'required|string|max:50',
            'producto_id' => 'required|exists:productos,id',
            'tipo_hallazgo_id' => 'required|exists:tipos_hallazgo,id',
        ];

        if ($this->mostrarUbicacion) {
            $rules['ubicacion_id'] = 'required|exists:ubicaciones,id';
        }

        return $rules;
    }

    public function mount()
    {
        // Ajustar fecha según turno de trabajo (si es madrugada 00:00-06:59, usar fecha anterior)
        $ahora = Carbon::now();
        if ($ahora->hour < 7) {
            $this->fecha_actual = $ahora->subDay()->format('Y-m-d');
        } else {
            $this->fecha_actual = $ahora->format('Y-m-d');
        }
        $this->cargarDatos();
        $this->actualizarTotalRegistros();
    }

    public function cargarDatos()
    {
        // Cargar solo los productos para tolerancia cero
        $this->productos = Producto::whereIn('nombre', ['CUARTO ANTERIOR', 'CUARTO POSTERIOR'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Cargar solo los tipos de hallazgo para tolerancia cero
        $this->tiposHallazgo = TipoHallazgo::whereIn('nombre', [
            'MATERIA FECAL',
            'CONTENIDO RUMINAL',
            'LECHE VISIBLE'
        ])
            ->orderBy('nombre')
            ->get()
            ->toArray();

        // Cargar todas las ubicaciones
        $this->ubicaciones = Ubicacion::orderBy('nombre')->get();
    }

    public function updatedProductoId()
    {
        $this->reset(['ubicacion_id']);
        $this->mostrarUbicacion = false;
        
        if ($this->producto_id) {
            $producto = Producto::find($this->producto_id);
            $this->nombreProductoSeleccionado = $producto?->nombre ?? '';
            $this->actualizarUbicacionesDisponibles();
        }
    }

    public function updatedTipoHallazgoId()
    {
        $this->reset(['ubicacion_id']);
        $this->mostrarUbicacion = false;
        
        if ($this->tipo_hallazgo_id) {
            $tipoHallazgo = TipoHallazgo::find($this->tipo_hallazgo_id);
            $this->nombreTipoSeleccionado = $tipoHallazgo?->nombre ?? '';
            $this->actualizarUbicacionesDisponibles();
        }
    }

    private function actualizarUbicacionesDisponibles()
    {
        if (!$this->producto_id || !$this->tipo_hallazgo_id) {
            $this->mostrarUbicacion = false;
            $this->ubicacionesDisponibles = [];
            return;
        }

        // Obtener nombres de producto y tipo de hallazgo
        $producto = Producto::find($this->producto_id);
        $tipoHallazgo = TipoHallazgo::find($this->tipo_hallazgo_id);

        if (!$producto || !$tipoHallazgo) {
            $this->mostrarUbicacion = false;
            $this->ubicacionesDisponibles = [];
            return;
        }

        $nombreProducto = trim($producto->nombre);
        $nombreTipo = trim($tipoHallazgo->nombre);

        // Definir ubicaciones permitidas según combinación de producto y tipo
        $nombresUbicacionesPermitidas = [];

        // CUARTO ANTERIOR + CONTENIDO RUMINAL → CLIPADO DE ESOFAGO - EVISERADO DE BLANCAS - CORTE DE ESTERNON
        if ($nombreProducto === 'CUARTO ANTERIOR' && $nombreTipo === 'CONTENIDO RUMINAL') {
            $nombresUbicacionesPermitidas = [
                'CLIPADO DE ESOFAGO',
                'EVISERADO DE BLANCAS',
                'CORTE DE ESTERNON'
            ];
        }
        // CUARTO POSTERIOR + MATERIA FECAL → CORTE DE PATAS - MANIPULACION - CHOQUE DE CANAL - DESPEJE DE RECTO
        elseif ($nombreProducto === 'CUARTO POSTERIOR' && $nombreTipo === 'MATERIA FECAL') {
            $nombresUbicacionesPermitidas = [
                'CORTE DE PATAS',
                'MANIPULACION',
                'CHOQUE DE CANAL',
                'DESPEJE DE RECTO'
            ];
        }
        // CUARTO ANTERIOR + MATERIA FECAL → RAYADO DE PECHO - DESUELLO DE MANOS - DESOLLADORA
        elseif ($nombreProducto === 'CUARTO ANTERIOR' && $nombreTipo === 'MATERIA FECAL') {
            $nombresUbicacionesPermitidas = [
                'RAYADO DE PECHO',
                'DESUELLO DE MANOS',
                'DESOLLADORA'
            ];
        }
        // CUARTO ANTERIOR/POSTERIOR + LECHE VISIBLE → Sin ubicaciones específicas
        elseif ($nombreTipo === 'LECHE VISIBLE') {
            $this->mostrarUbicacion = false;
            $this->ubicacionesDisponibles = [];
            return;
        }

        // Si hay ubicaciones permitidas, buscarlas y mostrar el campo
        if (!empty($nombresUbicacionesPermitidas)) {
            $this->ubicacionesDisponibles = $this->ubicaciones
                ->whereIn('nombre', $nombresUbicacionesPermitidas)
                ->values()
                ->all();

            $this->mostrarUbicacion = true;
        } else {
            $this->mostrarUbicacion = false;
            $this->ubicacionesDisponibles = [];
        }
    }

    public function registrar()
    {
        $this->validate();

        try {
            HallazgoToleranciaZero::create([
                'fecha_registro' => Carbon::now(),
                'fecha_operacion' => $this->fecha_actual,
                'codigo' => strtoupper($this->codigo),
                'producto_id' => $this->producto_id,
                'tipo_hallazgo_id' => $this->tipo_hallazgo_id,
                'ubicacion_id' => $this->ubicacion_id ?? null,
                'usuario_id' => Auth::id(),
            ]);

            // Actualizar indicadores del día
            $this->actualizarIndicadorDiario($this->fecha_actual);

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
        $this->ubicacion_id = '';
        $this->nombreProductoSeleccionado = '';
        $this->nombreTipoSeleccionado = '';
        $this->mostrarUbicacion = false;
        $this->ubicacionesDisponibles = [];
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
