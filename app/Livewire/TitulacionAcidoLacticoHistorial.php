<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use App\Models\TitulacionAcidoLacticoRegistro;
use Livewire\Component;
use Livewire\WithPagination;

class TitulacionAcidoLacticoHistorial extends Component
{
    use AuthorizaPorMenuModulo;
    use WithPagination;

    /** Filtros del historial (YYYY-MM-DD) */
    public string $fecha_desde = '';

    public string $fecha_hasta = '';

    /** Actividad del historial (key); vacío = todas */
    public string $actividad_filtro = '';

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->autorizarVistaMenu('titulacion-acido-lactico');
    }

    public function updatedFechaDesde(): void
    {
        $this->resetPage('histPage');
    }

    public function updatedFechaHasta(): void
    {
        $this->resetPage('histPage');
    }

    public function updatedActividadFiltro(): void
    {
        $this->resetPage('histPage');
    }

    public function limpiarFiltros(): void
    {
        $this->fecha_desde = '';
        $this->fecha_hasta = '';
        $this->actividad_filtro = '';
        $this->resetPage('histPage');
    }

    public function render()
    {
        $aplicarFiltros = fn ($q) => $q
            ->when($this->fecha_desde !== '', fn ($qq) => $qq->whereDate('fecha', '>=', $this->fecha_desde))
            ->when($this->fecha_hasta !== '', fn ($qq) => $qq->whereDate('fecha', '<=', $this->fecha_hasta))
            ->when($this->actividad_filtro !== '', fn ($qq) => $qq->where('actividad', $this->actividad_filtro));

        $totalRegistros = TitulacionAcidoLacticoRegistro::query()
            ->tap($aplicarFiltros)
            ->count();

        $registros = TitulacionAcidoLacticoRegistro::query()
            ->tap($aplicarFiltros)
            ->with(['usuario', 'verificadoPor'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15, ['*'], 'histPage');

        return view('livewire.titulacion-acido-lactico-historial', [
            'registros' => $registros,
            'totalRegistros' => $totalRegistros,
            'actividadesOpciones' => TitulacionAcidoLacticoRegistro::actividadesOpciones(),
        ])->layout('layouts.app');
    }
}

