<?php

namespace App\Observers;

use App\Events\HallazgoPublicado;
use App\Models\HallazgoToleranciaZero;
use Illuminate\Support\Facades\Log;

class HallazgoToleranciaZeroObserver
{
    public function created(HallazgoToleranciaZero $hallazgo): void
    {
        try {
            $hallazgo->loadMissing(['tipoHallazgo', 'usuario', 'producto']);
            $mc = $hallazgo->media_canal !== null && $hallazgo->media_canal !== ''
                ? (string) $hallazgo->media_canal
                : null;
            $mediaEtiqueta = match ($mc) {
                '1' => 'Media Canal 1',
                '2' => 'Media Canal 2',
                default => match ((int) ($hallazgo->producto_id ?? 0)) {
                    1 => 'Media Canal 1',
                    2 => 'Media Canal 2',
                    default => '—',
                },
            };
            $parImparRaw = trim((string) ($hallazgo->par_impar ?? ''));
            $ladoNombre = $parImparRaw !== ''
                ? ucfirst(strtolower($parImparRaw))
                : null;
            broadcast(new HallazgoPublicado([
                'origen' => 'tolerancia_cero',
                'usuario_registro_id' => $hallazgo->usuario_id,
                'tipo_nombre' => $hallazgo->tipoHallazgo?->nombre,
                'usuario_nombre' => $hallazgo->usuario?->name,
                'codigo' => $hallazgo->codigo,
                'producto_nombre' => $hallazgo->producto?->nombre,
                'lado_nombre' => $ladoNombre,
                'media_etiqueta' => $mediaEtiqueta,
                'registro_id' => $hallazgo->id,
                'registrado_en' => now()->toIso8601String(),
            ]));
        } catch (\Throwable $e) {
            Log::warning('Broadcast tolerancia cero: '.$e->getMessage());
        }
    }
}
