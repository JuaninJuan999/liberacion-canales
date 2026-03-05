<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\HallazgoToleranciaZero;
use App\Models\IndicadorDiario;
use Carbon\Carbon;

class GraficoToleranciaZeroMes extends Component
{
    public $mes;
    public $anio;
    public $materiaFecalTotal = 0;
    public $contenidoRuminalTotal = 0;
    public $lecheVisibleTotal = 0;
    public $totalHallazgosMes = 0;
    public $metaMensual = 1.0; // Meta: 1.0 hallazgo por día
    public $diasConDatos = 0;
    public $promedioDiario = 0;
    public $cumpleMeta = false;

    protected $listeners = ['hallazgo-tolerancia-cero-registrado' => 'actualizar'];

    public function mount($mes = null, $anio = null)
    {
        $this->mes = $mes ?: Carbon::now()->month;
        $this->anio = $anio ?: Carbon::now()->year;
        $this->cargarDatos();
    }

    public function actualizar()
    {
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        $inicio = Carbon::create($this->anio, $this->mes, 1)->startOfMonth();
        $fin = Carbon::create($this->anio, $this->mes, 1)->endOfMonth();

        // Contar por tipo en el mes
        $this->materiaFecalTotal = HallazgoToleranciaZero::whereBetween('fecha_operacion', [$inicio, $fin])
            ->whereHas('tipoHallazgo', function ($query) {
                $query->where('nombre', 'MATERIA FECAL');
            })
            ->count();

        $this->contenidoRuminalTotal = HallazgoToleranciaZero::whereBetween('fecha_operacion', [$inicio, $fin])
            ->whereHas('tipoHallazgo', function ($query) {
                $query->where('nombre', 'CONTENIDO RUMINAL');
            })
            ->count();

        $this->lecheVisibleTotal = HallazgoToleranciaZero::whereBetween('fecha_operacion', [$inicio, $fin])
            ->whereHas('tipoHallazgo', function ($query) {
                $query->where('nombre', 'LECHE VISIBLE');
            })
            ->count();

        $this->totalHallazgosMes = $this->materiaFecalTotal + $this->contenidoRuminalTotal + $this->lecheVisibleTotal;

        // Contar días con registros
        $this->diasConDatos = HallazgoToleranciaZero::whereBetween('fecha_operacion', [$inicio, $fin])
            ->distinct('fecha_operacion')
            ->count();

        // Calcular promedio
        if ($this->diasConDatos > 0) {
            $this->promedioDiario = $this->totalHallazgosMes / $this->diasConDatos;
            $this->cumpleMeta = $this->promedioDiario <= $this->metaMensual;
        } else {
            $this->promedioDiario = 0;
            $this->cumpleMeta = true;
        }
    }

    public function render()
    {
        return view('livewire.grafico-tolerancia-cero-mes');
    }
}
