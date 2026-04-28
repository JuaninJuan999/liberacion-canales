<?php

namespace App\Console\Commands;

use App\Models\RegistroHallazgo;
use App\Observers\RegistroHallazgoObserver;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RegistrosHallazgosDedupeCommand extends Command
{
    protected $signature = 'registros-hallazgos:dedupe
                            {--dry-run : Solo mostrar cuántos se borrarían y salir}
                            {--since= : Solo considerar dupes cuyo registro tiene created_at >= fecha (Y-m-d o Y-m-d H:i:s)}';

    protected $description = 'Elimina hallazgos duplicados manteniendo el menor id. Considera igual: fecha operación + código + tipo + producto + ubicación + lado + cantidad + evidencia.';

    public function handle(RegistroHallazgoObserver $observer): int
    {
        $since = null;
        $rawSince = trim((string) $this->option('since'));
        if ($rawSince !== '') {
            try {
                $since = Carbon::parse($rawSince);
            } catch (\Throwable) {
                $this->error('Fecha inválida en --since= (ej. 2026-04-20 o 2026-04-20 14:30:00).');

                return 1;
            }
        }

        $q = DB::table('registros_hallazgos as dup')
            ->when($since !== null, function ($qb) use ($since) {
                $qb->where('dup.created_at', '>=', $since->format('Y-m-d H:i:s'));
            })
            ->whereExists(function (\Illuminate\Database\Query\Builder $sub) {
                $sub->from('registros_hallazgos as older')
                    ->whereColumn('older.fecha_operacion', 'dup.fecha_operacion')
                    ->whereColumn('older.codigo', 'dup.codigo')
                    ->whereColumn('older.tipo_hallazgo_id', 'dup.tipo_hallazgo_id')
                    ->whereColumn('older.producto_id', 'dup.producto_id')
                    ->whereRaw('((older.ubicacion_id IS NULL AND dup.ubicacion_id IS NULL) OR older.ubicacion_id = dup.ubicacion_id)')
                    ->whereRaw('((older.lado_id IS NULL AND dup.lado_id IS NULL) OR older.lado_id = dup.lado_id)')
                    ->whereColumn('older.cantidad', 'dup.cantidad')
                    ->whereRaw('((older.evidencia_path IS NULL AND dup.evidencia_path IS NULL) OR older.evidencia_path = dup.evidencia_path)')
                    ->whereColumn('older.id', '<', 'dup.id');
            });

        $duplicateIds = $q->orderBy('dup.id')->pluck('dup.id')->unique()->values();
        $n = $duplicateIds->count();

        $line = $since
            ? "Registros duplicados a eliminar (created_at >= {$since->toDateTimeString()}): {$n}"
            : "Registros duplicados a eliminar (se conserva el id menor por grupo): {$n}";
        $this->info($line);

        if ($this->option('dry-run')) {
            $this->warn('Dry-run: no se modificó la base de datos.');

            return 0;
        }

        if ($n === 0) {
            return 0;
        }

        $fechasOperacion = RegistroHallazgo::query()
            ->whereIn('id', $duplicateIds->all())
            ->pluck('fecha_operacion')
            ->map(fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : (string) $d)
            ->unique()
            ->values()
            ->all();

        RegistroHallazgo::withoutEvents(static function () use ($duplicateIds): void {
            RegistroHallazgo::query()->whereIn('id', $duplicateIds->all())->delete();
        });

        foreach ($fechasOperacion as $fecha) {
            $observer->sincronizarIndicadoresParaFecha($fecha);
        }

        $this->info("Eliminados: {$n}.");

        return 0;
    }
}
