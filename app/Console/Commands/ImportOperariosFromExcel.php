<?php

namespace App\Console\Commands;

use App\Models\Operario;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportOperariosFromExcel extends Command
{
    protected $signature = 'import:operarios-excel
                            {path : Ruta absoluta al archivo .xlsx}
                            {--dry-run : Solo muestra cuántos se crearían sin escribir en la BD}
                            {--skip-existing : No inserta si ya existe el mismo nombre en BD (mayús./espacios ignorados)}
                            {--column=B : Columna del nombre del operario (por defecto B)}
                            {--start-row=2 : Primera fila de datos}';

    protected $description = 'Importa operarios al catálogo desde Excel (nombre en una columna).';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! is_readable($path)) {
            $this->error("No se puede leer el archivo: {$path}");

            return self::FAILURE;
        }

        $column = strtoupper((string) $this->option('column'));
        if (! preg_match('/^[A-Z]+$/', $column)) {
            $this->error('La opción --column debe ser una letra de columna (ej. B).');

            return self::FAILURE;
        }

        $startRow = max(1, (int) $this->option('start-row'));

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $highestRow = (int) $sheet->getHighestRow();

        $dryRun = (bool) $this->option('dry-run');
        $skipExisting = (bool) $this->option('skip-existing');

        $existentesBd = [];
        if ($skipExisting) {
            $existentesBd = Operario::query()
                ->pluck('nombre')
                ->mapWithKeys(fn (string $n) => [$this->normalizarNombre($n) => true])
                ->all();
        }

        $vistoEnExcel = [];

        $crear = 0;
        $omitidos = 0;
        $vacíos = 0;

        for ($row = $startRow; $row <= $highestRow; $row++) {
            $nombreRaw = $sheet->getCell($column.$row)->getFormattedValue();
            $nombre = is_string($nombreRaw) ? trim($nombreRaw) : trim((string) $nombreRaw);
            $nombre = preg_replace('/\s+/u', ' ', $nombre) ?? '';

            if ($nombre === '') {
                $vacíos++;

                continue;
            }

            $clave = $this->normalizarNombre($nombre);

            if (isset($vistoEnExcel[$clave])) {
                $omitidos++;

                continue;
            }
            $vistoEnExcel[$clave] = true;

            if ($skipExisting && isset($existentesBd[$clave])) {
                $omitidos++;

                continue;
            }

            if ($dryRun) {
                $crear++;

                continue;
            }

            Operario::create([
                'nombre' => $nombre,
                'documento' => null,
                'activo' => true,
            ]);

            $existentesBd[$clave] = true;
            $crear++;
        }

        $this->info('Última fila leída del Excel: '.$highestRow.' (datos desde fila '.$startRow.')');
        $this->info('Filas vacías omitidas: '.$vacíos);

        if ($dryRun) {
            $this->warn('[DRY-RUN] Registros nuevos que se crearían: '.$crear);
            $this->warn('[DRY-RUN] Omitidos (duplicado en archivo o ya en BD): '.$omitidos);

            return self::SUCCESS;
        }

        $this->info('Operarios creados: '.$crear);
        $this->info('Omitidos (duplicado en archivo o ya existían): '.$omitidos);

        return self::SUCCESS;
    }

    private function normalizarNombre(string $nombre): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $nombre) ?? ''));
    }
}
