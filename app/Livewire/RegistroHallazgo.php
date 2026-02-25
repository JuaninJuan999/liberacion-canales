<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\RegistroHallazgo as ModeloRegistroHallazgo;
use App\Models\TipoHallazgo;
use App\Models\Producto;
use App\Models\AnimalProcesado;
use App\Models\Ubicacion;
use App\Models\Lado;
use Carbon\Carbon;

class RegistroHallazgo extends Component
{
    use WithFileUploads;

    // --- Form Data ---
    public $producto_id;
    public $tipo_hallazgo_id;
    public $numero_canal;
    public $foto;
    public $observacion;
    public $ubicacion_id;
    public $lado_id;

    // --- Complementary Data ---
    public $fecha_actual;
    public $total_registros_dia = 0;
    public $nombreHallazgoSeleccionado = '';
    public $nombreUbicacionSeleccionada = '';

    // --- Collections for Selects ---
    public $productos = [];
    public $tiposHallazgo = [];
    public $ubicaciones = [];
    public $lados = [];

    // --- Control Flags ---
    public $mostrarUbicacion = false;
    public $mostrarLado = false;

    // --- Messages ---
    public $mensaje = '';
    public $tipoMensaje = 'success';

    // --- Validation Messages ---
    protected $messages = [
        'producto_id.required' => 'Debe seleccionar un producto.',
        'tipo_hallazgo_id.required' => 'Debe seleccionar el tipo de hallazgo.',
        'numero_canal.required' => 'Debe ingresar el código del canal.',
        'ubicacion_id.required' => 'Debe seleccionar la ubicación.',
        'lado_id.required' => 'Debe indicar si es par o impar.',
    ];

    // --- Validation Rules ---
    protected function rules()
    {
        $rules = [
            'producto_id' => 'required|exists:productos,id',
            'tipo_hallazgo_id' => 'required|exists:tipos_hallazgo,id',
            'numero_canal' => 'required|string|max:50',
            'foto' => 'nullable|image|max:2048',
            'observacion' => 'nullable|string',
        ];

        if ($this->mostrarUbicacion) {
            $rules['ubicacion_id'] = 'required|exists:ubicaciones,id';
        }

        if ($this->mostrarLado) {
            $rules['lado_id'] = 'required|exists:lados,id';
        }

        return $rules;
    }

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
    }

    public function updatedTipoHallazgoId($value)
    {
        $this->reset(['ubicacion_id', 'lado_id', 'nombreUbicacionSeleccionada']);
        $this->mostrarUbicacion = false;
        $this->mostrarLado = false;

        if ($value) {
            $hallazgo = TipoHallazgo::find($value);
            $this->nombreHallazgoSeleccionado = $hallazgo ? strtoupper($hallazgo->nombre) : '';

            if ($this->nombreHallazgoSeleccionado === 'COBERTURA DE GRASA') {
                $this->mostrarUbicacion = true;
            } elseif ($this->nombreHallazgoSeleccionado === 'CORTE EN PIERNAS') {
                $this->mostrarLado = true;
            }
        } else {
            $this->nombreHallazgoSeleccionado = '';
        }
        $this->resetValidation(['ubicacion_id', 'lado_id']);
    }

    public function updatedUbicacionId($value)
    {
        $this->reset('lado_id');
        $this->mostrarLado = false;

        if ($value) {
            $ubicacion = Ubicacion::find($value);
            $this->nombreUbicacionSeleccionada = $ubicacion ? strtoupper($ubicacion->nombre) : '';

            if ($this->nombreUbicacionSeleccionada === 'PIERNA') {
                $this->mostrarLado = true;
            }
        } else {
            $this->nombreUbicacionSeleccionada = '';
        }
        $this->resetValidation('lado_id');
    }

    public function registrar()
    {
        $this->validate();

        try {
            $fotoPath = null;
            if ($this->foto) {
                $fotoPath = $this->foto->store('evidencias', 'public');
            }

            ModeloRegistroHallazgo::create([
                'fecha_registro' => now(),
                'fecha_operacion' => $this->fecha_actual,
                'codigo' => $this->numero_canal,
                'producto_id' => $this->producto_id,
                'tipo_hallazgo_id' => $this->tipo_hallazgo_id,
                'evidencia_path' => $fotoPath,
                'usuario_id' => auth()->id(),
                'observacion' => $this->observacion,
                'ubicacion_id' => $this->ubicacion_id,
                'lado_id' => $this->lado_id,
            ]);

            $animalProcesado = AnimalProcesado::firstOrCreate(
                ['fecha_operacion' => $this->fecha_actual],
                ['cantidad_animales' => 0, 'usuario_id' => auth()->id()]
            );
            $animalProcesado->increment('cantidad_animales');

            $this->reset(['numero_canal', 'tipo_hallazgo_id', 'foto', 'observacion', 'ubicacion_id', 'lado_id']);
            $this->mostrarUbicacion = false;
            $this->mostrarLado = false;
            $this->nombreHallazgoSeleccionado = '';
            $this->nombreUbicacionSeleccionada = '';

            $this->mensaje = '¡Hallazgo registrado exitosamente!';
            $this->tipoMensaje = 'success';

            $this->actualizarContadorDia();
            $this->dispatch('hallazgo-registrado');

        } catch (\Exception $e) {
            $this->mensaje = 'Error al registrar hallazgo: ' . $e->getMessage();
            $this->tipoMensaje = 'error';
        }
    }

    public function limpiarFormulario()
    {
        $this->reset();
        $this->resetValidation();
        $this->mensaje = '';
        $this->mostrarUbicacion = false;
        $this->mostrarLado = false;
    }

    public function actualizarContadorDia()
    {
        $this->total_registros_dia = ModeloRegistroHallazgo::whereDate('created_at', $this->fecha_actual)->count();
    }

    public function limpiarMensaje()
    {
        $this->mensaje = '';
    }

    public function render()
    {
        return view('livewire.registro-hallazgo')
            ->layout('layouts.app');
    }
}
