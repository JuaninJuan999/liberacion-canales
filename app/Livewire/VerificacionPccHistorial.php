<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use App\Models\VerificacionPccRegistro;
use Livewire\Component;
use Livewire\WithPagination;

class VerificacionPccHistorial extends Component
{
    use AuthorizaPorMenuModulo;
    use WithPagination;

    /** Filtro por día (columna created_at); vacío = todos los registros. */
    public string $fecha_filtro = '';

    public function mount(): void
    {
        $this->autorizarVistaMenu('verificacion-pcc');
    }

    public function updatedFechaFiltro(): void
    {
        $this->resetPage('histPage');
    }

    public function limpiarFecha(): void
    {
        $this->fecha_filtro = '';
        $this->resetPage('histPage');
    }

    public function render()
    {
        $filtrarPorDia = fn ($q) => $q->when(
            $this->fecha_filtro !== '',
            fn ($qq) => $qq->whereDate('created_at', $this->fecha_filtro)
        );

        $totalRegistros = VerificacionPccRegistro::query()
            ->tap($filtrarPorDia)
            ->count();

        $historial = VerificacionPccRegistro::query()
            ->tap($filtrarPorDia)
            ->with('usuario')
            ->latest()
            ->paginate(20, ['*'], 'histPage');

        return view('livewire.verificacion-pcc-historial', [
            'historial' => $historial,
            'totalRegistros' => $totalRegistros,
        ])->layout('layouts.app');
    }
}
