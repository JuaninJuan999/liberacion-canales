<?php

namespace App\Livewire;

use App\Models\IndicadorDiario;
use App\Models\HallazgoToleranciaZero;
use Carbon\Carbon;
use Livewire\Component;

class DashboardDia extends Component
{
    public $fecha;
    public $indicadores;
    public $hallazgosTZPorHora = [];

    // Escucha el evento 'fechaCambiada' para actualizar la fecha
    protected $listeners = ['fechaCambiada' => 'actualizarFecha'];

    public function mount()
    {
        $this->fecha = Carbon::now()->format('Y-m-d');
        $this->cargarIndicadores();
    }

    public function cargarIndicadores()
    {
        $this->indicadores = IndicadorDiario::where('fecha_operacion', $this->fecha)->first();
        $this->cargarHallazgosTZ();
    }

    public function cargarHallazgosTZ()
    {
        // Obtener hallazgos de tolerancia cero para la fecha
        $hallazgos = HallazgoToleranciaZero::where('fecha_operacion', $this->fecha)
            ->with('tipoHallazgo')
            ->get();

        // Inicializar array con horas (0-23)
        $horas = [];
        for ($i = 0; $i < 24; $i++) {
            $horas[$i] = [
                'MATERIA FECAL' => 0,
                'CONTENIDO RUMINAL' => 0,
                'LECHE VISIBLE' => 0
            ];
        }

        // Agrupar hallazgos por hora y tipo
        foreach ($hallazgos as $hallazgo) {
            $hora = (int) Carbon::parse($hallazgo->created_at)->format('H');
            $tipo = $hallazgo->tipoHallazgo->nombre ?? 'Desconocido';
            
            // Solo contar los 3 tipos de tolerancia cero
            if (in_array($tipo, ['MATERIA FECAL', 'CONTENIDO RUMINAL', 'LECHE VISIBLE'])) {
                if (!isset($horas[$hora][$tipo])) {
                    $horas[$hora][$tipo] = 0;
                }
                $horas[$hora][$tipo]++;
            }
        }

        $this->hallazgosTZPorHora = $horas;
    }

    public function actualizarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarIndicadores();
    }

    public function cambiarFecha($nuevaFecha)
    {
        $this->fecha = $nuevaFecha;
        $this->cargarIndicadores();
    }

    /**
     * Recalcula los indicadores para la fecha actual.
     * Este método es solo un disparador y no realiza cálculos directamente.
     */
    public function recalcularIndicadores()
    {
        $this->cargarIndicadores(); // Simplemente vuelve a cargar desde la DB
        session()->flash('message', 'Datos del dashboard actualizados.');
    }
    
    public function render()
    {
        // Asegurarse de que siempre haya un objeto, incluso si está vacío
        if (!$this->indicadores) {
            $this->indicadores = new IndicadorDiario();
        }

        return view('livewire.dashboard-dia');
    }
}
