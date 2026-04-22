<?php

namespace App\Console\Commands;

use App\Models\AnimalProcesado;
use App\Models\User;
use App\Observers\RegistroHallazgoObserver;
use Carbon\Carbon;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportAnimalesProcesadosFromExcel extends Command
{
    protected $signature = 'import:animales-procesados-excel
                            {path : Ruta absoluta al archivo .xlsx (hoja Resumen)}
                            {--user-id= : ID de usuario (users) para los registros importados}
                            {--dry-run : Solo valida y muestra totales, no escribe en la BD}
                            {--skip-existing : No modifica fechas que ya existen en animales_procesados}
                            {--sheet=Resumen : Nombre de la hoja (por defecto Resumen)}';

    protected $description = 'Importa animales procesados desde la hoja Resumen (columnas F.Operacion y Animales).';

    public function handle(): int
    {
        @ini_set('memory_limit', '1024M');

        $path = $this->argument('path');
        if (! is_file($path)) {
            $this->error("No existe el archivo: {$path}");

            return 1;
        }

        $userId = $this->option('user-id');
        if ($userId === null || $userId === '') {
            $first = User::query()->orderBy('id')->value('id');
            if (! $first) {
                $this->error('No hay usuarios en la base de datos. Use --user-id=');

                return 1;
            }
            $userId = (int) $first;
            $this->warn("No se indicó --user-id; se usará el usuario id={$userId}.");
        } else {
            $userId = (int) $userId;
            if (! User::query()->whereKey($userId)->exists()) {
                $this->error("user-id={$userId} no existe en users.");

                return 1;
            }
        }

        $sheetName = (string) $this->option('sheet');
        $reader = new Xlsx;
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly([$sheetName]);

        try {
            $spreadsheet = $reader->load($path);
        } catch (\Throwable $e) {
            $this->error('No se pudo leer la hoja indicada. Compruebe el nombre con --sheet= (p. ej. Resumen). '.$e->getMessage());

            return 1;
        }

        $sheet = $spreadsheet->getSheetByName($sheetName);
        if (! $sheet) {
            $this->error("No existe la hoja \"{$sheetName}\". Hojas disponibles: ".implode(', ', $spreadsheet->getSheetNames()));

            return 1;
        }

        if (! $this->encabezadosValidos($sheet)) {
            $this->error('La fila 1 de la hoja no contiene los encabezados esperados (F.Operacion / Animales).');

            return 1;
        }

        $agregadoPorFecha = $this->leerFilas($sheet);
        $skipExisting = (bool) $this->option('skip-existing');
        $dryRun = (bool) $this->option('dry-run');

        $insertados = 0;
        $actualizados = 0;
        $omitidos = 0;
        $fechasEnExcel = count($agregadoPorFecha);

        if ($dryRun) {
            foreach ($agregadoPorFecha as $fecha => $cantidad) {
                $existe = AnimalProcesado::query()->where('fecha_operacion', $fecha)->exists();
                if ($skipExisting && $existe) {
                    $omitidos++;
                } elseif ($existe) {
                    $actualizados++;
                } else {
                    $insertados++;
                }
            }
            $this->mostrarResumen($dryRun, $insertados, $actualizados, $omitidos, $fechasEnExcel);

            return 0;
        }

        $fechasSincronizar = [];

        AnimalProcesado::withoutEvents(function () use ($agregadoPorFecha, $userId, $skipExisting, &$insertados, &$actualizados, &$omitidos, &$fechasSincronizar) {
            foreach ($agregadoPorFecha as $fecha => $cantidad) {
                $existente = AnimalProcesado::query()->where('fecha_operacion', $fecha)->first();

                if ($skipExisting && $existente) {
                    $omitidos++;

                    continue;
                }

                if ($existente) {
                    $existente->update([
                        'cantidad_animales' => $cantidad,
                        'usuario_id' => $userId,
                    ]);
                    $actualizados++;
                } else {
                    AnimalProcesado::create([
                        'fecha_operacion' => $fecha,
                        'cantidad_animales' => $cantidad,
                        'usuario_id' => $userId,
                    ]);
                    $insertados++;
                }

                $fechasSincronizar[] = $fecha;
            }
        });

        $observer = app(RegistroHallazgoObserver::class);
        foreach (array_unique($fechasSincronizar) as $fecha) {
            $observer->sincronizarIndicadoresParaFecha($fecha);
        }

        $this->mostrarResumen($dryRun, $insertados, $actualizados, $omitidos, $fechasEnExcel);

        return 0;
    }

    private function encabezadosValidos(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): bool
    {
        $b = trim((string) $sheet->getCell('B1')->getValue());
        $c = trim((string) $sheet->getCell('C1')->getValue());

        return str_contains($b, 'F.Operacion') && str_contains($c, 'Animales');
    }

    /**
     * @return array<string, int> fecha Y-m-d => suma de animales
     */
    private function leerFilas(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): array
    {
        $maxRow = (int) $sheet->getHighestDataRow();
        $porFecha = [];

        for ($row = 2; $row <= $maxRow; $row++) {
            $rawFecha = $sheet->getCell("B{$row}")->getValue();
            $rawCant = $sheet->getCell("C{$row}")->getValue();

            if ($rawFecha === null || $rawFecha === '') {
                continue;
            }
            if ($rawCant === null || $rawCant === '') {
                continue;
            }

            try {
                $fecha = $this->parseFecha($rawFecha);
            } catch (\Throwable) {
                continue;
            }

            $cant = (int) round((float) $rawCant);
            if ($cant < 1) {
                continue;
            }

            $key = $fecha->format('Y-m-d');
            $porFecha[$key] = ($porFecha[$key] ?? 0) + $cant;
        }

        ksort($porFecha);

        return $porFecha;
    }

    private function parseFecha(mixed $raw): Carbon
    {
        if (is_numeric($raw)) {
            $dt = ExcelDate::excelToDateTimeObject((float) $raw);

            return Carbon::instance($dt)->startOfDay();
        }

        return Carbon::parse((string) $raw)->startOfDay();
    }

    private function mostrarResumen(bool $dryRun, int $insertados, int $actualizados, int $omitidos, int $fechas): void
    {
        $this->info($dryRun ? 'Simulación terminada.' : 'Importación terminada.');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Fechas distintas en el Excel', (string) $fechas],
                ['Nuevos registros'.($dryRun ? ' (simulado)' : ''), (string) $insertados],
                ['Registros actualizados'.($dryRun ? ' (simulado)' : ''), (string) $actualizados],
                ['Omitidos (--skip-existing)'.($dryRun ? ' (simulado)' : ''), (string) $omitidos],
            ]
        );
    }
}
