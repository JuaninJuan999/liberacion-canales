<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use App\Models\TitulacionAcidoLacticoRegistro;
use App\Models\User;
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

    public ?int $verificado_user_id = null;

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
            'verificado_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('puede_verificar_titulacion', true)->where('activo', true)),
            ],
        ], [
            'volumen_naoh_ml.required' => 'Indica el volumen de NaOH.',
            'volumen_naoh_ml.between' => 'El volumen debe estar entre 2,2 y 2,3 ml.',
            'concentracion_sol_pct.required' => 'Indica la concentración.',
            'concentracion_sol_pct.between' => 'La concentración debe estar entre 1,9 % y 2,1 % (2 % ± 0,1).',
            'actividad.required' => 'Selecciona la actividad.',
            'verificado_user_id.required' => 'Selecciona quién verifica.',
        ]);

        $instante = now();

        $verificador = User::findOrFail($this->verificado_user_id);

        TitulacionAcidoLacticoRegistro::create([
            'fecha' => $instante->toDateString(),
            'hora' => $instante->format('H:i:s'),
            'volumen_naoh_ml' => $this->volumen_naoh_ml,
            'concentracion_sol_pct' => $this->concentracion_sol_pct,
            'cumple' => $this->cumple === '1',
            'correccion' => $this->correccion !== '' ? $this->correccion : null,
            'actividad' => $this->actividad,
            'user_id' => auth()->id(),
            'verificado_user_id' => $verificador->id,
            'verificado_nombre' => $verificador->name,
        ]);

        $this->volumen_naoh_ml = null;
        $this->concentracion_sol_pct = null;
        $this->cumple = '1';
        $this->correccion = '';
        $this->actividad = 'operativo';
        $this->verificado_user_id = null;

        session()->flash('ok', 'Registro guardado correctamente.');
        $this->resetPage();
    }

    public function render()
    {
        $registros = TitulacionAcidoLacticoRegistro::query()
            ->with(['usuario', 'verificadoPor'])
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->paginate(15);

        return view('livewire.titulacion-acido-lactico', [
            'registros' => $registros,
            'actividadesOpciones' => TitulacionAcidoLacticoRegistro::actividadesOpciones(),
            'verificadoresAutorizados' => User::query()
                ->where('activo', true)
                ->where('puede_verificar_titulacion', true)
                ->orderBy('name')
                ->get(),
        ])->layout('layouts.app');
    }
}
