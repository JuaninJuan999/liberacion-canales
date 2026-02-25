<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\RegistroHallazgo as ModeloRegistroHallazgo;
use App\Models\TipoHallazgo;
use App\Models\Producto;
use App\Models\AnimalProcesado;
use Carbon\Carbon;

class RegistroHallazgo extends Component
{
    use WithFileUploads;

    // --- Simplified Form Data ---
    public $producto_id;
    public $tipo_hallazgo_id;
    public $numero_canal; // This is 'Código'
    public $foto; // This is 'Evidencia'
    
    // --- Complementary Data ---
    public $fecha_actual;
    public $total_registros_dia = 0;
    
    // --- Collections for Selects ---
    public $productos = [];
    public $tiposHallazgo = [];
    
    // --- Messages ---
    public $mensaje = '';
    public $tipoMensaje = 'success';
    
    // --- Validation Rules ---
    protected $rules = [
        'producto_id' => 'required|exists:productos,id',
        'tipo_hallazgo_id' => 'required|exists:tipos_hallazgo,id',
        'numero_canal' => 'required|string|max:50',
        'foto' => 'nullable|image|max:2048', // Allow nullable image, max 2MB
    ];
    
    // --- Validation Messages ---
    protected $messages = [
        'producto_id.required' => 'Debe seleccionar un producto.',
        'tipo_hallazgo_id.required' => 'Debe seleccionar el tipo de hallazgo.',
        'numero_canal.required' => 'Debe ingresar el código del canal.',
    ];
    
    public function mount()
    {
        $this->fecha_actual = Carbon::now()->format('Y-m-d');
        $this->cargarDatos();
        $this->actualizarContadorDia();
    }
    
    public function cargarDatos()
    {
        // Load only the necessary data for the simplified form
        $this->productos = Producto::where('activo', true)->orderBy('nombre')->get();
        $this->tiposHallazgo = TipoHallazgo::orderBy('nombre')->get();
    }
    
    public function actualizarContadorDia()
    {
        $this->total_registros_dia = ModeloRegistroHallazgo::whereDate('created_at', $this->fecha_actual)->count();
    }
    
    public function registrar()
    {
        $this->validate();
        
        try {
            $fotoPath = null;
            if ($this->foto) {
                // Store in 'storage/app/public/evidencias'
                $fotoPath = $this->foto->store('evidencias', 'public');
            }

            // Create the record with simplified data
            ModeloRegistroHallazgo::create([
                'producto_id' => $this->producto_id,
                'tipo_hallazgo_id' => $this->tipo_hallazgo_id,
                'numero_canal' => $this->numero_canal,
                'foto' => $fotoPath, // Save the path to the DB
                'registrado_por' => auth()->id(),
            ]);
            
            AnimalProcesado::firstOrCreate(
                ['numero_canal' => $this->numero_canal, 'fecha_procesamiento' => $this->fecha_actual],
                ['producto_id' => $this->producto_id, 'estado' => 'procesado']
            );
            
            $this->mensaje = '¡Hallazgo registrado exitosamente!';
            $this->tipoMensaje = 'success';
            
            $this->reset(['tipo_hallazgo_id', 'foto']);
            
            if (is_numeric($this->numero_canal)) {
                $this->numero_canal = (int)$this->numero_canal + 1;
            }
            
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
