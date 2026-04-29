<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use App\Models\ConsumoAcidoLacticoRegistro;
use Livewire\Component;
use Livewire\WithPagination;

class ConsumoAcidoLactico extends Component
{
    use AuthorizaPorMenuModulo;
    use WithPagination;

    public ?string $litros_preparados = null;

    public ?string $cantidad_acido_lactico_ml = null;

    public string $observacion = '';

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->autorizarVistaMenu('consumo-acido-lactico');
    }

    public function guardar(): void
    {
        $this->litros_preparados = str_replace(',', '.', trim((string) ($this->litros_preparados ?? '')));
        $this->cantidad_acido_lactico_ml = str_replace(',', '.', trim((string) ($this->cantidad_acido_lactico_ml ?? '')));

        $this->validate([
            'litros_preparados' => ['required', 'numeric', 'min:0'],
            'cantidad_acido_lactico_ml' => ['required', 'numeric', 'min:0'],
            'observacion' => ['nullable', 'string', 'max:5000'],
        ], [
            'litros_preparados.required' => 'Indica los litros preparados.',
            'litros_preparados.numeric' => 'Los litros deben ser un número válido.',
            'litros_preparados.min' => 'Los litros no pueden ser negativos.',
            'cantidad_acido_lactico_ml.required' => 'Indica la cantidad de ácido láctico (ml).',
            'cantidad_acido_lactico_ml.numeric' => 'La cantidad debe ser un número válido.',
            'cantidad_acido_lactico_ml.min' => 'La cantidad no puede ser negativa.',
        ]);

        $instante = now();

        ConsumoAcidoLacticoRegistro::create([
            'fecha' => $instante->toDateString(),
            'hora' => $instante->format('H:i:s'),
            'litros_preparados' => $this->litros_preparados,
            'cantidad_acido_lactico_ml' => $this->cantidad_acido_lactico_ml,
            'observacion' => $this->observacion !== '' ? $this->observacion : null,
            'user_id' => auth()->id(),
        ]);

        $this->litros_preparados = null;
        $this->cantidad_acido_lactico_ml = null;
        $this->observacion = '';

        session()->flash('ok', 'Registro guardado correctamente.');
        $this->resetPage();
    }

    public function render()
    {
        $registros = ConsumoAcidoLacticoRegistro::query()
            ->with('usuario')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15);

        return view('livewire.consumo-acido-lactico', [
            'registros' => $registros,
        ])->layout('layouts.app');
    }
}
