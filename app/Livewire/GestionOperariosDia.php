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

    public bool $modalNuevoOperario = false;

    /** Puesto desde el que se abrió el modal (se asigna el nuevo operario si queda activo). */
    public ?int $puestoIdModalNuevoOperario = null;

    public string $nuevo_operario_nombre = '';

    public string $nuevo_operario_documento = '';

    public bool $nuevo_operario_activo = true;

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

    public function abrirModalNuevoOperario(int $puestoId): void
    {
        $this->resetValidation();
        $this->puestoIdModalNuevoOperario = $puestoId;
        $this->nuevo_operario_nombre = '';
        $this->nuevo_operario_documento = '';
        $this->nuevo_operario_activo = true;
        $this->modalNuevoOperario = true;
    }

    public function cerrarModalNuevoOperario(): void
    {
        $this->modalNuevoOperario = false;
        $this->puestoIdModalNuevoOperario = null;
        $this->nuevo_operario_nombre = '';
        $this->nuevo_operario_documento = '';
        $this->nuevo_operario_activo = true;
        $this->resetValidation();
    }

    /**
     * Alta rápida igual criterios que OperarioController::store (nombre, documento único opcional, activo).
     */
    public function guardarNuevoOperario(): void
    {
        $this->nuevo_operario_documento = trim($this->nuevo_operario_documento);

        $this->validate([
            'nuevo_operario_nombre' => ['required', 'string', 'max:100'],
            'nuevo_operario_documento' => ['nullable', 'string', 'max:20', 'unique:operarios,documento'],
            'nuevo_operario_activo' => ['boolean'],
        ]);

        $puestoDestino = $this->puestoIdModalNuevoOperario;

        $documento = $this->nuevo_operario_documento !== '' ? $this->nuevo_operario_documento : null;

        $operario = Operario::create([
            'nombre' => trim($this->nuevo_operario_nombre),
            'documento' => $documento,
            'activo' => $this->nuevo_operario_activo,
        ]);

        $this->operariosDisponibles = Operario::where('activo', true)
            ->orderBy('nombre')
            ->get();

        if ($operario->activo && $puestoDestino !== null) {
            $this->asignaciones[$puestoDestino] = $operario->id;
        }

        session()->flash('success', '✅ Operario registrado en el catálogo'.($operario->activo && $puestoDestino !== null ? ' y asignado a este puesto.' : '.'));

        $this->cerrarModalNuevoOperario();
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
            $this->dispatch('operarios-asignados-guardados');
            
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
