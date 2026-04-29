<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use App\Models\TitulacionAcidoLacticoRegistro;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TitulacionAcidoLactico extends Component
{
    use AuthorizaPorMenuModulo;
    use WithPagination;

    public ?string $volumen_naoh_ml = null;

    public ?string $concentracion_sol_pct = null;

    /** @var '0'|'1' Livewire enlaza mejor radios/select como string */
    public string $cumple = '1';

    public string $correccion = '';

    public string $actividad = 'operativo';

    public string $verificado_nombre = '';

    protected string $paginationTheme = 'tailwind';

    public function mount(): void
    {
        $this->autorizarVistaMenu('titulacion-acido-lactico');
    }

    public function guardar(): void
    {
        $this->volumen_naoh_ml = str_replace(',', '.', trim((string) ($this->volumen_naoh_ml ?? '')));
        $this->concentracion_sol_pct = str_replace(',', '.', trim((string) ($this->concentracion_sol_pct ?? '')));

        $opcionesActividad = array_keys(TitulacionAcidoLacticoRegistro::actividadesOpciones());

        $this->validate([
            'volumen_naoh_ml' => ['required', 'numeric', 'between:2.2,2.3'],
            'concentracion_sol_pct' => ['required', 'numeric', 'between:1.9,2.1'],
            'cumple' => ['required', Rule::in(['0', '1'])],
            'correccion' => ['nullable', 'string', 'max:5000'],
            'actividad' => ['required', 'string', 'in:'.implode(',', $opcionesActividad)],
            'verificado_nombre' => ['required', 'string', 'max:255'],
        ], [
            'volumen_naoh_ml.required' => 'Indica el volumen de NaOH.',
            'volumen_naoh_ml.between' => 'El volumen debe estar entre 2,2 y 2,3 ml.',
            'concentracion_sol_pct.required' => 'Indica la concentración.',
            'concentracion_sol_pct.between' => 'La concentración debe estar entre 1,9 % y 2,1 % (2 % ± 0,1).',
            'actividad.required' => 'Selecciona la actividad.',
            'verificado_nombre.required' => 'Indica quién verifica.',
        ]);

        $instante = now();

        TitulacionAcidoLacticoRegistro::create([
            'fecha' => $instante->toDateString(),
            'hora' => $instante->format('H:i:s'),
            'volumen_naoh_ml' => $this->volumen_naoh_ml,
            'concentracion_sol_pct' => $this->concentracion_sol_pct,
            'cumple' => $this->cumple === '1',
            'correccion' => $this->correccion !== '' ? $this->correccion : null,
            'actividad' => $this->actividad,
            'user_id' => auth()->id(),
            'verificado_nombre' => $this->verificado_nombre,
        ]);

        $this->volumen_naoh_ml = null;
        $this->concentracion_sol_pct = null;
        $this->cumple = '1';
        $this->correccion = '';
        $this->actividad = 'operativo';
        $this->verificado_nombre = '';

        session()->flash('ok', 'Registro guardado correctamente.');
        $this->resetPage();
    }

    public function render()
    {
        $registros = TitulacionAcidoLacticoRegistro::query()
            ->with('usuario')
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15);

        return view('livewire.titulacion-acido-lactico', [
            'registros' => $registros,
            'actividadesOpciones' => TitulacionAcidoLacticoRegistro::actividadesOpciones(),
        ])->layout('layouts.app');
    }
}
