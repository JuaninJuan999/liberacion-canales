<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use App\Models\ConsumoAcidoLacticoRegistro;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class ConsumoAcidoLactico extends Component
{
    use AuthorizaPorMenuModulo;
    use WithPagination;

    public ?string $litros_preparados = null;

    public ?string $cantidad_acido_lactico_ml = null;

    public string $observacion = '';

    /** Formato YYYY-MM para <input type="month"> */
    public string $mes_seleccionado = '';

    /** Formato YYYY-MM-DD para <input type="date"> */
    public string $dia_seleccionado = '';

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->autorizarVistaMenu('consumo-acido-lactico');
        $this->mes_seleccionado = now()->format('Y-m');
        $this->dia_seleccionado = now()->format('Y-m-d');
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
        try {
            $diaRef = Carbon::createFromFormat('Y-m-d', $this->dia_seleccionado)->startOfDay();
        } catch (\Throwable) {
            $diaRef = now()->startOfDay();
            $this->dia_seleccionado = $diaRef->format('Y-m-d');
        }

        $fechaDia = $diaRef->toDateString();
        $diaEtiqueta = $diaRef->locale(app()->getLocale())->translatedFormat('d/m/Y');

        try {
            $mes = Carbon::createFromFormat('Y-m', $this->mes_seleccionado)->startOfMonth();
        } catch (\Throwable) {
            $mes = now()->startOfMonth();
            $this->mes_seleccionado = $mes->format('Y-m');
        }

        $inicioMes = $mes->copy()->startOfMonth()->toDateString();
        $finMes = $mes->copy()->endOfMonth()->toDateString();
        $mesEtiqueta = $mes->translatedFormat('F Y');

        $base = ConsumoAcidoLacticoRegistro::query()
            ->selectRaw('COALESCE(SUM(litros_preparados),0) as litros, COALESCE(SUM(cantidad_acido_lactico_ml),0) as ml');

        $totalesHoy = (clone $base)->whereDate('fecha', $fechaDia)->first();
        $totalesMes = (clone $base)->whereBetween('fecha', [$inicioMes, $finMes])->first();
        $totalesTotal = (clone $base)->first();

        $registros = ConsumoAcidoLacticoRegistro::query()
            ->with('usuario')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15);

        return view('livewire.consumo-acido-lactico', [
            'registros' => $registros,
            'totalesHoy' => $totalesHoy,
            'totalesMes' => $totalesMes,
            'totalesTotal' => $totalesTotal,
            'mesEtiqueta' => $mesEtiqueta,
            'diaEtiqueta' => $diaEtiqueta,
        ])->layout('layouts.app');
    }
}
