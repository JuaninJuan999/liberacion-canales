<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use Livewire\Component;
use App\Models\HallazgoToleranciaZero;
use App\Models\TipoHallazgo;
use App\Models\Producto;
use App\Models\Ubicacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class RegistroHallazgoToleranciaZero extends Component
{
    use AuthorizaPorMenuModulo;

    // Form Data
    public $producto_id;
    public $tipo_hallazgo_id;
    public $ubicacion_id;

    /** Código de referencia (obligatorio) */
    public $codigo_ingresado = '';

    /** Media canal: '1' | '2' */
    public $media_canal = '';

    /** par | impar */
    public $par_impar = '';

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
        'producto_id.required' => 'Debe seleccionar el cuarto (Anterior o Posterior).',
        'tipo_hallazgo_id.required' => 'Debe seleccionar el tipo de hallazgo.',
        'ubicacion_id.required' => 'Debe seleccionar la ubicación específica.',
        'media_canal.required' => 'Indique si el hallazgo es en media canal 1 o media canal 2.',
        'codigo_ingresado.required' => 'Debe ingresar el código.',
        'codigo_ingresado.max' => 'El código no puede superar 120 caracteres.',
        'par_impar.required' => 'Indique si el canal es par o impar.',
    ];

    // Validation Rules
    protected function rules()
    {
        $rules = [
            'producto_id' => 'required|exists:productos,id',
            'tipo_hallazgo_id' => [
                'required',
                'exists:tipos_hallazgo,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $producto = Producto::find($this->producto_id);
                    $tipo = TipoHallazgo::find($value);
                    if ($producto && $tipo && $this->combinacionTcNoPermiteTipo($producto->nombre, $tipo->nombre)) {
                        $fail('Este tipo de hallazgo no aplica al cuarto seleccionado.');
                    }
                },
            ],
            'codigo_ingresado' => 'required|string|max:120',
            'media_canal' => 'required|in:1,2',
            'par_impar' => 'required|in:par,impar',
            'ubicacion_id' => 'required|exists:ubicaciones,id',
        ];

        return $rules;
    }

    /**
     * Combinaciones no permitidas en TC (alineado con actualizarUbicacionesDisponibles).
     */
    private function combinacionTcNoPermiteTipo(string $nombreProducto, string $nombreTipo): bool
    {
        $producto = trim($nombreProducto);
        $tipo = trim($nombreTipo);

        if ($producto === 'CUARTO POSTERIOR' && $tipo === 'CONTENIDO RUMINAL') {
            return true;
        }

        return false;
    }

    public function mount()
    {
        $this->autorizarVistaMenu('tolerancia-cero.registrar');

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

        // Cargar todas las ubicaciones
        $this->ubicaciones = Ubicacion::orderBy('nombre')->get();

        $this->actualizarTiposHallazgoDisponibles();
    }

    /**
     * Tipos TC según cuarto: posterior no incluye CONTENIDO RUMINAL.
     */
    private function actualizarTiposHallazgoDisponibles(): void
    {
        if (! $this->producto_id) {
            $this->tiposHallazgo = [];

            return;
        }

        $producto = Producto::find($this->producto_id);
        $nombre = trim($producto->nombre ?? '');

        $nombresTipo = match ($nombre) {
            'CUARTO ANTERIOR' => ['CONTENIDO RUMINAL', 'LECHE VISIBLE', 'MATERIA FECAL'],
            'CUARTO POSTERIOR' => ['LECHE VISIBLE', 'MATERIA FECAL'],
            default => [],
        };

        $this->tiposHallazgo = TipoHallazgo::whereIn('nombre', $nombresTipo)
            ->orderBy('nombre')
            ->get()
            ->toArray();
    }

    public function updatedProductoId()
    {
        $tipoPrevio = $this->tipo_hallazgo_id ? (int) $this->tipo_hallazgo_id : null;

        $this->reset(['ubicacion_id']);
        $this->mostrarUbicacion = false;

        if ($this->producto_id) {
            $producto = Producto::find($this->producto_id);
            $this->nombreProductoSeleccionado = $producto?->nombre ?? '';
        } else {
            $this->nombreProductoSeleccionado = '';
        }

        $this->actualizarTiposHallazgoDisponibles();

        $idsPermitidos = collect($this->tiposHallazgo)->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($tipoPrevio && in_array($tipoPrevio, $idsPermitidos, true)) {
            $this->tipo_hallazgo_id = (string) $tipoPrevio;
            $tipoHallazgo = TipoHallazgo::find($tipoPrevio);
            $this->nombreTipoSeleccionado = $tipoHallazgo?->nombre ?? '';
        } else {
            $this->tipo_hallazgo_id = '';
            $this->nombreTipoSeleccionado = '';
        }

        $this->actualizarUbicacionesDisponibles();
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
        // CUARTO ANTERIOR/POSTERIOR + LECHE VISIBLE → TRANSFERENCIA (sin mostrar selector)
        elseif ($nombreTipo === 'LECHE VISIBLE') {
            // Asignar automáticamente TRANSFERENCIA para LECHE VISIBLE
            $transferencia = Ubicacion::where('nombre', 'TRANSFERENCIA')->first();
            if ($transferencia) {
                $this->ubicacion_id = $transferencia->id;
            }
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
        $this->codigo_ingresado = trim((string) $this->codigo_ingresado);
        $this->validate();

        try {
            $codigoFinal = $this->codigo_ingresado;

            HallazgoToleranciaZero::create([
                'fecha_registro' => Carbon::now(),
                'fecha_operacion' => $this->fecha_actual,
                'codigo' => $codigoFinal,
                'producto_id' => $this->producto_id,
                'tipo_hallazgo_id' => $this->tipo_hallazgo_id,
                'ubicacion_id' => $this->ubicacion_id,
                'media_canal' => $this->media_canal,
                'par_impar' => $this->par_impar,
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

    public function limpiarPantalla(): void
    {
        $this->resetearFormulario();
        $this->mensaje = '';
        $this->tipoMensaje = 'success';
    }

    private function resetearFormulario()
    {
        $this->producto_id = '';
        $this->tipo_hallazgo_id = '';
        $this->ubicacion_id = '';
        $this->codigo_ingresado = '';
        $this->media_canal = '';
        $this->par_impar = '';
        $this->nombreProductoSeleccionado = '';
        $this->nombreTipoSeleccionado = '';
        $this->mostrarUbicacion = false;
        $this->ubicacionesDisponibles = [];
        $this->actualizarTiposHallazgoDisponibles();
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
