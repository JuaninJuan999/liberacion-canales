<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\OperarioPorDia;
use App\Models\Operario;
use App\Models\PuestoTrabajo;
use Illuminate\Support\Facades\DB;

class GestionOperariosDia extends Component
{
    public $fecha_operacion;
    public $asignaciones = [];
    public $operariosDisponibles = [];
    public $puestos = [];

    public function mount()
    {
        $this->fecha_operacion = now()->toDateString();
        $this->cargarDatos();
    }

    public function cargarDatos()
    {
        // Cargar puestos de trabajo ordenados por el campo 'orden'
        $this->puestos = PuestoTrabajo::orderBy('orden', 'asc')->get();
        
        // Cargar operarios activos
        $this->operariosDisponibles = Operario::where('activo', true)
            ->orderBy('nombre')
            ->get();

        // Cargar asignaciones existentes para la fecha
        $asignacionesExistentes = OperarioPorDia::where('fecha_operacion', $this->fecha_operacion)
            ->get();

        // Inicializar array de asignaciones
        $this->asignaciones = [];
        foreach ($this->puestos as $puesto) {
            $asignacion = $asignacionesExistentes->firstWhere('puesto_trabajo_id', $puesto->id);
            $this->asignaciones[$puesto->id] = $asignacion ? $asignacion->operario_id : null;
        }
    }

    public function updatedFechaOperacion()
    {
        $this->cargarDatos();
        $this->dispatch('fecha-cambiada');
    }

    public function guardarAsignaciones()
    {
        $this->validate([
            'fecha_operacion' => ['required', 'date'],
        ]);

        DB::beginTransaction();
        try {
            // Eliminar asignaciones existentes para esta fecha
            OperarioPorDia::where('fecha_operacion', $this->fecha_operacion)->delete();

            // Guardar nuevas asignaciones
            $guardadas = 0;
            foreach ($this->asignaciones as $puestoId => $operarioId) {
                if ($operarioId) {
                    OperarioPorDia::create([
                        'fecha_operacion' => $this->fecha_operacion,
                        'puesto_trabajo_id' => $puestoId,
                        'operario_id' => $operarioId,
                    ]);
                    $guardadas++;
                }
            }

            DB::commit();
            
            session()->flash('success', "✅ Se guardaron {$guardadas} asignaciones exitosamente");
            $this->cargarDatos();
            
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', '❌ Error al guardar las asignaciones: ' . $e->getMessage());
        }
    }

    public function limpiarAsignaciones()
    {
        $this->asignaciones = [];
        foreach ($this->puestos as $puesto) {
            $this->asignaciones[$puesto->id] = null;
        }
    }

    public function copiarDiaAnterior()
    {
        $fechaAnterior = date('Y-m-d', strtotime($this->fecha_operacion . ' -1 day'));
        
        $asignacionesAnteriores = OperarioPorDia::where('fecha_operacion', $fechaAnterior)
            ->get();

        if ($asignacionesAnteriores->isEmpty()) {
            session()->flash('info', '📌 No hay asignaciones en el día anterior');
            return;
        }

        foreach ($asignacionesAnteriores as $asignacion) {
            $this->asignaciones[$asignacion->puesto_trabajo_id] = $asignacion->operario_id;
        }

        session()->flash('info', '📋 Asignaciones copiadas del día anterior. No olvides guardar.');
    }

    public function render()
    {
        return view('livewire.gestion-operarios-dia')
            ->layout('layouts.app');
    }
}
