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
    public $ubicacionesFiltradas = [];
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
            'foto' => 'nullable|file|mimes:jpeg,jpg,png,gif,webp|max:10240',
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
        // Ajustar fecha según turno de trabajo (si es madrugada 00:00-06:59, usar fecha anterior)
        $ahora = Carbon::now();
        if ($ahora->hour < 7) {
            $this->fecha_actual = $ahora->subDay()->format('Y-m-d');
        } else {
            $this->fecha_actual = $ahora->format('Y-m-d');
        }
        $this->cargarDatos();
        $this->actualizarContadorDia();
    }

    public function cargarDatos()
    {
        $this->productos = Producto::whereIn('nombre', ['Media Canal 1 Lengua', 'Media Canal 2 Cola'])
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
        $this->tiposHallazgo = TipoHallazgo::whereNotIn('nombre', ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'])
            ->orderBy('nombre')
            ->get();
        $this->ubicaciones = Ubicacion::orderBy('nombre')->get();
        $this->lados = Lado::orderBy('nombre')->get();
    }

    public function updatedTipoHallazgoId($value)
    {
        $this->reset(['ubicacion_id', 'lado_id', 'nombreUbicacionSeleccionada']);
        $this->mostrarUbicacion = false;
        $this->mostrarLado = false;
        $this->ubicacionesFiltradas = [];

        if ($value) {
            $hallazgo = TipoHallazgo::find($value);
            $this->nombreHallazgoSeleccionado = $hallazgo ? trim(strtoupper($hallazgo->nombre)) : '';

            if ($this->nombreHallazgoSeleccionado === 'COBERTURA DE GRASA') {
                $this->mostrarUbicacion = true;
                // Filtrar solo Cadera y Pierna para Cobertura de Grasa (con los nombres exactos de la BD)
                $this->ubicacionesFiltradas = Ubicacion::whereIn('nombre', ['Cadera', 'Pierna'])
                    ->orderBy('nombre')
                    ->get();
            } elseif (str_contains($this->nombreHallazgoSeleccionado, 'CORTE') && str_contains($this->nombreHallazgoSeleccionado, 'PIERNA')) {
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
                try {
                    // Usar timestamp para nombre único de foto
                    $nombreFoto = 'foto_' . time() . '_' . uniqid() . '.' . $this->foto->getClientOriginalExtension();
                    $fotoPath = $this->foto->storeAs('hallazgos', $nombreFoto, 'public');
                    
                    if (!$fotoPath) {
                        throw new \Exception('No se pudo guardar la foto en el servidor.');
                    }
                } catch (\Exception $fotoError) {
                    $this->mensaje = 'Error al guardar la foto: ' . $fotoError->getMessage();
                    $this->tipoMensaje = 'error';
                    return;
                }
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

            $this->reset(['numero_canal', 'tipo_hallazgo_id', 'foto', 'observacion', 'ubicacion_id', 'lado_id']);
            $this->mostrarUbicacion = false;
            $this->mostrarLado = false;
            $this->nombreHallazgoSeleccionado = '';
            $this->nombreUbicacionSeleccionada = '';

            $this->mensaje = '¡Hallazgo registrado exitosamente!';
            $this->tipoMensaje = 'success';

            $this->actualizarContadorDia();
            
            // Evento global para actualizar todos los componentes
            $this->dispatch('hallazgo-registrado')->self();

        } catch (\Exception $e) {
            \Log::error('Error en registro de hallazgo: ' . $e->getMessage());
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
        $this->total_registros_dia = ModeloRegistroHallazgo::where('fecha_operacion', $this->fecha_actual)->count();
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
