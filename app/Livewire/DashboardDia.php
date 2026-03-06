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
    public $hallazgosToleranciaZeroPorCuarto = [];

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
        $this->cargarHallazgosToleranciaZeroPorCuarto();
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

    public function cargarHallazgosToleranciaZeroPorCuarto()
    {
        // Obtener hallazgos de tolerancia cero para la fecha, solo Cuarto Anterior y Posterior
        $hallazgos = HallazgoToleranciaZero::where('fecha_operacion', $this->fecha)
            ->with(['producto', 'tipoHallazgo'])
            ->get();

        // Inicializar array para tipos de hallazgo
        $resultados = [];

        // Agrupar hallazgos por tipo y contar por cuarto
        foreach ($hallazgos as $hallazgo) {
            $tipo = $hallazgo->tipoHallazgo->nombre ?? 'Desconocido';
            $producto = $hallazgo->producto->nombre ?? 'Desconocido';

            // Solo incluir CUARTO ANTERIOR y CUARTO POSTERIOR
            if (!in_array($producto, ['CUARTO ANTERIOR', 'CUARTO POSTERIOR'])) {
                continue;
            }

            if (!isset($resultados[$tipo])) {
                $resultados[$tipo] = [
                    'tipo' => $tipo,
                    'CUARTO ANTERIOR' => 0,
                    'CUARTO POSTERIOR' => 0,
                ];
            }

            $resultados[$tipo][$producto]++;
        }

        $this->hallazgosToleranciaZeroPorCuarto = array_values($resultados);
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
