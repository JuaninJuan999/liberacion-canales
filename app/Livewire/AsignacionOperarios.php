<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Operario;
use App\Models\PuestoTrabajo;
use App\Models\OperarioPorDia;
use Carbon\Carbon;

class AsignacionOperarios extends Component
{
    public $fecha;
    public $puestos;
    public $operarios;
    public $asignaciones = [];

    public function mount()
    {
        $this->fecha = now()->toDateString();
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        // Obtener puestos ordenados
        $this->puestos = PuestoTrabajo::orderBy('orden')->get();
        
        // Obtener operarios activos
        $this->operarios = Operario::where('estado', 'Activo')
            ->orderBy('nombre')
            ->get();

        // Cargar asignaciones existentes para la fecha
        $asignacionesDB = OperarioPorDia::where('fecha', $this->fecha)
            ->with('operario')
            ->get();

        // Inicializar array de asignaciones
        $this->asignaciones = [];
        foreach ($this->puestos as $puesto) {
            $asignacion = $asignacionesDB->where('puesto_trabajo_id', $puesto->id)->first();
            $this->asignaciones[$puesto->id] = $asignacion ? $asignacion->operario_id : null;
        }
    }

    public function actualizarFecha()
    {
        $this->cargarDatos();
    }

    public function asignarOperario($puestoId, $operarioId)
    {
        if (!$operarioId) {
            // Eliminar asignación si se selecciona vacío
            OperarioPorDia::where('fecha', $this->fecha)
                ->where('puesto_trabajo_id', $puestoId)
                ->delete();
        } else {
            // Actualizar o crear asignación
            OperarioPorDia::updateOrCreate(
                [
                    'fecha' => $this->fecha,
                    'puesto_trabajo_id' => $puestoId,
                ],
                [
                    'operario_id' => $operarioId,
                ]
            );
        }

        $this->asignaciones[$puestoId] = $operarioId;
        session()->flash('message', 'Asignación actualizada correctamente.');
    }

    public function render()
    {
        return view('livewire.asignacion-operarios');
    }
}
