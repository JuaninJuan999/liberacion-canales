<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\HallazgoToleranciaZero;
use Carbon\Carbon;

class GraficoToleranciaZeroDia extends Component
{
    public $fecha;
    public $materiaFecal = 0;
    public $contenidoRuminal = 0;
    public $lecheVisible = 0;
    public $totalHallazgos = 0;

    protected $listeners = ['hallazgo-tolerancia-cero-registrado' => 'actualizar'];

    public function mount($fecha = null)
    {
        $this->fecha = $fecha ?: Carbon::now()->toDateString();
        $this->cargarDatos();
    }

    public function actualizar()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        // Contar hallazgos por tipo del día
        $this->materiaFecal = HallazgoToleranciaZero::where('fecha_operacion', $this->fecha)
            ->whereHas('tipoHallazgo', function ($query) {
                $query->where('nombre', 'MATERIA FECAL');
            })
            ->count();

        $this->contenidoRuminal = HallazgoToleranciaZero::where('fecha_operacion', $this->fecha)
            ->whereHas('tipoHallazgo', function ($query) {
                $query->where('nombre', 'CONTENIDO RUMINAL');
            })
            ->count();

        $this->lecheVisible = HallazgoToleranciaZero::where('fecha_operacion', $this->fecha)
            ->whereHas('tipoHallazgo', function ($query) {
                $query->where('nombre', 'LECHE VISIBLE');
            })
            ->count();

        $this->totalHallazgos = $this->materiaFecal + $this->contenidoRuminal + $this->lecheVisible;
    }

    public function render()
    {
        return view('livewire.grafico-tolerancia-cero-dia');
    }
}
