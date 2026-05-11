<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use App\Models\VerificacionPccRegistro;
use App\Support\TurnoVerificacionPcc;
use Livewire\Component;
use Livewire\WithPagination;

class VerificacionPccHistorial extends Component
{
    use AuthorizaPorMenuModulo;
    use WithPagination;

    /** Filtro por día operativo PCC (ventana desde turno_hora_fin); vacío = todos los registros. */
    public string $fecha_filtro = '';

    public function mount(): void
    {
        $this->autorizarVistaMenu('verificacion-pcc');
    }

    public function updatedFechaFiltro(): void
    {
        if ($this->fecha_filtro !== '' && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->fecha_filtro)) {
            $this->fecha_filtro = '';
        }
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
            function ($qq) {
                [$desde, $hasta] = TurnoVerificacionPcc::ventanaCreacionParaFechaOperativa($this->fecha_filtro);
                $qq->whereBetween('created_at', [$desde, $hasta]);
            }
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
