<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\OperarioPorDia;
use App\Models\Operario;
use App\Models\PuestoTrabajo;
use Illuminate\Support\Collection;
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

    /**
     * Operarios que pueden elegirse en este puesto: no asignados ya a otro puesto el mismo día (excepto el elegido aquí).
     */
    public function operariosOpcionesPara(int $puestoId): Collection
    {
        $actualRaw = $this->asignaciones[$puestoId] ?? null;
        $actualId = $actualRaw !== null && $actualRaw !== '' ? (int) $actualRaw : null;

        $idsEnOtrosPuestos = collect($this->asignaciones)
            ->filter(fn ($oid, $pid) => (int) $pid !== $puestoId && $oid !== null && $oid !== '')
            ->map(fn ($oid) => (int) $oid)
            ->values()
            ->all();

        return $this->operariosDisponibles->filter(function ($op) use ($idsEnOtrosPuestos, $actualId) {
            $id = (int) $op->id;
            if ($actualId !== null && $id === $actualId) {
                return true;
            }

            return ! in_array($id, $idsEnOtrosPuestos, true);
        })->values();
    }

    /** Recarga catálogo de operarios sin perder las asignaciones actuales en pantalla (tras crear uno en otra pestaña). */
    public function refrescarListaOperarios(): void
    {
        $this->operariosDisponibles = Operario::where('activo', true)
            ->orderBy('nombre')
            ->get();

        session()->flash('info', '🔄 Lista de operarios actualizada.');
    }

    public function guardarAsignaciones()
    {
        $this->validate([
            'fecha_operacion' => ['required', 'date'],
        ]);

        foreach ($this->asignaciones as $pid => $oid) {
            if ($oid === '__crear__') {
                $this->asignaciones[$pid] = '';
            }
        }

        $usados = [];
        foreach ($this->asignaciones as $puestoId => $operarioId) {
            if ($operarioId === null || $operarioId === '' || $operarioId === '__crear__') {
                continue;
            }
            $oid = (int) $operarioId;
            if (isset($usados[$oid])) {
                session()->flash('error', '❌ El mismo operario no puede estar en dos puestos a la vez. Revise las asignaciones o use «Limpiar».');

                return;
            }
            $usados[$oid] = true;
        }

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
