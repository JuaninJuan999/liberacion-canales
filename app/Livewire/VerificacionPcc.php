<?php

namespace App\Livewire;

use App\Livewire\Concerns\AuthorizaPorMenuModulo;
use App\Models\VerificacionPccRegistro;
use App\Services\TrazabilidadInsensibilizacionReader;
use Illuminate\Support\Collection;
use Livewire\Component;

class VerificacionPcc extends Component
{
    use AuthorizaPorMenuModulo;

    /** @var '0'|'1' Por defecto "Cumple" para agilizar el registro; el usuario elige "No cumple" si aplica. */
    public string $cumple_media_canal_1 = '1';

    /** @var '0'|'1' */
    public string $cumple_media_canal_2 = '1';

    public string $observacion = '';

    public string $accion_correctiva = '';

    public function mount(): void
    {
        $this->autorizarVistaMenu('verificacion-pcc');
    }

    protected function coleccionExternaDelDia(): Collection
    {
        $reader = app(TrazabilidadInsensibilizacionReader::class);

        if (! $reader->configuracionLista()) {
            return collect();
        }

        return collect(array_map(
            fn ($r) => json_decode(json_encode($r), true),
            $reader->filasDelDiaActual()
        ));
    }

    /**
     * IDs ins. ya guardados como verificación PCC en este sistema (hoy).
     *
     * @return list<int>
     */
    protected function idsInsVerificadosHoy(): array
    {
        return VerificacionPccRegistro::query()
            ->whereDate('created_at', now()->toDateString())
            ->whereNotNull('external_ins_id')
            ->pluck('external_ins_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /** Cola: productos del día en BD externa que aún no tienen verificación PCC guardada hoy. */
    protected function pendientesParaVerificar(): Collection
    {
        $todas = $this->coleccionExternaDelDia();
        $hechos = $this->idsInsVerificadosHoy();

        return $todas->filter(function (array $row) use ($hechos) {
            $insId = (int) ($row['id'] ?? 0);

            return $insId > 0 && ! in_array($insId, $hechos, true);
        })->values();
    }

    public function guardar(): void
    {
        $pendientes = $this->pendientesParaVerificar();
        $actual = $pendientes->first();

        if ($actual === null) {
            session()->flash('error', 'No hay productos pendientes de verificación para el día de hoy (o ya fueron registrados).');

            return;
        }

        $codigoProducto = $this->normalizarCodigoProducto($actual['id_producto'] ?? null);

        if ($codigoProducto === '') {
            session()->flash('error', 'El registro seleccionado no tiene código de producto válido.');

            return;
        }

        $responsable = VerificacionPccRegistro::operarioDesinfeccionParaFecha(now());

        $this->validate([
            'cumple_media_canal_1' => ['required', 'in:0,1'],
            'cumple_media_canal_2' => ['required', 'in:0,1'],
            'observacion' => ['nullable', 'string', 'max:5000'],
            'accion_correctiva' => ['nullable', 'string', 'max:5000'],
        ], [
            'cumple_media_canal_1.required' => 'Indica si media canal 1 cumple o no cumple.',
            'cumple_media_canal_2.required' => 'Indica si media canal 2 cumple o no cumple.',
        ]);

        $obs = trim($this->observacion);
        $acc = trim($this->accion_correctiva);

        VerificacionPccRegistro::create([
            'user_id' => auth()->id(),
            'external_ins_id' => isset($actual['id']) ? (int) $actual['id'] : null,
            'id_producto' => $codigoProducto,
            'snapshot_externo' => $actual,
            'cumple_media_canal_1' => $this->cumple_media_canal_1 === '1',
            'cumple_media_canal_2' => $this->cumple_media_canal_2 === '1',
            'responsable_puesto_trabajo' => $responsable,
            'observacion' => $obs !== '' ? $obs : null,
            'accion_correctiva' => $acc !== '' ? $acc : null,
        ]);

        session()->flash('ok', 'Verificación guardada. Se muestra el siguiente ID producto pendiente del día.');
        $this->cumple_media_canal_1 = '1';
        $this->cumple_media_canal_2 = '1';
        $this->observacion = '';
        $this->accion_correctiva = '';
    }

    protected function normalizarCodigoProducto(mixed $raw): string
    {
        if ($raw === null) {
            return '';
        }

        return trim((string) $raw);
    }

    public function render()
    {
        $reader = app(TrazabilidadInsensibilizacionReader::class);
        $externoDisponible = $reader->configuracionLista();

        $todasDelDia = $this->coleccionExternaDelDia();
        $pendientes = $this->pendientesParaVerificar();
        $filaActual = $pendientes->first();

        $totalExternosHoy = $todasDelDia->count();
        $pendientesCount = $pendientes->count();
        $verificadosEnEstaAppHoy = max(0, $totalExternosHoy - $pendientesCount);

        return view('livewire.verificacion-pcc', [
            'externoDisponible' => $externoDisponible,
            'filaActual' => $filaActual,
            'totalExternosHoy' => $totalExternosHoy,
            'pendientesCount' => $pendientesCount,
            'verificadosEnEstaAppHoy' => $verificadosEnEstaAppHoy,
        ])->layout('layouts.app');
    }
}
