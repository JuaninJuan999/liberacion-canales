<?php

namespace App\Console\Commands;

use App\Models\Operario;
use App\Models\Producto;
use App\Models\RegistroHallazgo;
use App\Models\TipoHallazgo;
use App\Models\Ubicacion;
use App\Models\User;
use App\Observers\RegistroHallazgoObserver;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ImportRegistrosHallazgosFromExcel extends Command
{
    protected $signature = 'import:registros-hallazgos-excel
                            {path : Ruta absoluta al archivo .xlsx (hoja Registros)}
                            {--user-id= : ID de usuario (users) para registros cuyo Usuario del Excel no exista en la BD}
                            {--dry-run : Solo valida y cuenta filas, no inserta}
                            {--limit=0 : Máximo de filas de datos a importar (0 = sin límite)}
                            {--chunk=400 : Tamaño del lote para inserción}
                            {--omitir-duplicados : No inserta si ya existe igual en BD (huella como en registros-hallazgos:dedupe)}';

    protected $description = 'Importa hallazgos desde la hoja "Registros" de un Excel (p. ej. exportado de AppSheet).';

    /**
     * Serial de fecha de operación (columna J del Excel) => valores de operario por índice VLOOKUP (2..10 → columnas C..K).
     *
     * @var array<int, array<int, mixed>>
     */
    private array $operariosPorFechaSerial = [];

    /** @var array<string, int> nombre tipo hallazgo normalizado => id */
    private array $tipoPorNombre = [];

    /** @var array<string, int> nombre producto => id */
    private array $productoPorNombre = [];

    /** @var array<string, int> nombre ubicación normalizado => id */
    private array $ubicacionPorNombre = [];

    /** @var array<string, int> Par|Impar normalizado => id */
    private array $ladoPorNombre = [];

    /** @var array<string, int> nombre operario normalizado => id */
    private array $operarioPorNombre = [];

    /** @var array<string, true> */
    private array $usuariosNoEncontrados = [];

    /** @var array<string, true> */
    private array $tiposNoEncontrados = [];

    /** @var array<string, true> */
    private array $productosNoEncontrados = [];

    /** @var array<string, true> */
    private array $operariosNoEncontrados = [];

    /** Huellas ya existentes en BD (solo con --omitir-duplicados) */
    /** @var array<string, true> */
    private array $huellasExistentes = [];

    private int $omitidosYaExistentes = 0;

    public function handle(): int
    {
        @ini_set('memory_limit', '1024M');

        $path = $this->argument('path');
        if (! is_file($path)) {
            $this->error("No existe el archivo: {$path}");

            return 1;
        }

        $defaultUserId = $this->option('user-id');
        if ($defaultUserId === null || $defaultUserId === '') {
            $first = User::query()->orderBy('id')->value('id');
            if (! $first) {
                $this->error('No hay usuarios en la base de datos. Cree al menos un usuario o use --user-id=');

                return 1;
            }
            $defaultUserId = (int) $first;
            $this->warn("No se indicó --user-id; se usará el usuario id={$defaultUserId} cuando el Excel no coincida con ningún usuario.");
        } else {
            $defaultUserId = (int) $defaultUserId;
            if (! User::query()->whereKey($defaultUserId)->exists()) {
                $this->error("user-id={$defaultUserId} no existe en users.");

                return 1;
            }
        }

        $this->cargarCatalogos();

        $reader = new Xlsx;
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly(['Registros', 'Operarios']);

        $spreadsheet = $reader->load($path);
        $registros = $spreadsheet->getSheetByName('Registros');
        $operariosSheet = $spreadsheet->getSheetByName('Operarios');

        if (! $registros || ! $operariosSheet) {
            $this->error('El archivo debe contener las hojas "Registros" y "Operarios".');

            return 1;
        }

        $this->indexarOperarios($operariosSheet);

        $highestRow = (int) $registros->getHighestDataRow();
        $dryRun = (bool) $this->option('dry-run');
        $omitirDuplicados = (bool) $this->option('omitir-duplicados');
        $limit = max(0, (int) $this->option('limit'));
        $chunkSize = max(50, (int) $this->option('chunk'));

        if ($omitirDuplicados) {
            $this->warmupHuellasExistentes($registros, $highestRow);
        }

        $insertados = 0;
        $omitidos = 0;
        $fechasAfectadas = [];
        $buffer = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $fila = $this->leerFilaRegistro($registros, $row);
            if ($fila === null) {
                continue;
            }

            $payload = $this->mapearFila($fila, (int) $defaultUserId);
            if ($payload === null) {
                $omitidos++;

                continue;
            }

            if ($limit > 0 && $insertados >= $limit) {
                break;
            }

            $huella = self::claveHuellaDesdePayload($payload);
            if ($omitirDuplicados) {
                if (isset($this->huellasExistentes[$huella])) {
                    $this->omitidosYaExistentes++;

                    continue;
                }
                $this->huellasExistentes[$huella] = true;
            }

            if ($dryRun) {
                $insertados++;
                $fechasAfectadas[$payload['fecha_operacion']] = true;

                continue;
            }

            $buffer[] = $payload;
            $fechasAfectadas[$payload['fecha_operacion']] = true;

            if (count($buffer) >= $chunkSize) {
                $this->insertarSinEventos($buffer);
                $insertados += count($buffer);
                $buffer = [];
                $this->info("Insertadas {$insertados} filas...");
            }
        }

        if (! $dryRun && $buffer !== []) {
            $this->insertarSinEventos($buffer);
            $insertados += count($buffer);
        }

        if (! $dryRun && $insertados > 0) {
            $observer = app(RegistroHallazgoObserver::class);
            foreach (array_keys($fechasAfectadas) as $fecha) {
                $observer->sincronizarIndicadoresParaFecha($fecha);
            }
        }

        $this->info($dryRun ? 'Simulación terminada.' : 'Importación terminada.');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Filas insertadas (o válidas en dry-run)', (string) $insertados],
                ['Filas omitidas (datos incompletos o catálogo)', (string) $omitidos],
                ['Omitidas (ya existían en BD, --omitir-duplicados)', (string) $this->omitidosYaExistentes],
                ['Fechas operación afectadas', (string) count($fechasAfectadas)],
            ]
        );

        $this->imprimirAdvertencias();

        return 0;
    }

    private function cargarCatalogos(): void
    {
        foreach (TipoHallazgo::query()->get(['id', 'nombre']) as $t) {
            $this->tipoPorNombre[$this->normalizarClave($t->nombre)] = $t->id;
        }
        foreach (Producto::query()->get(['id', 'nombre']) as $p) {
            $this->productoPorNombre[trim($p->nombre)] = $p->id;
        }
        foreach (Ubicacion::query()->get(['id', 'nombre']) as $u) {
            $this->ubicacionPorNombre[$this->normalizarClave($u->nombre)] = $u->id;
        }
        foreach (\App\Models\Lado::query()->get(['id', 'nombre']) as $l) {
            $this->ladoPorNombre[$this->normalizarClave($l->nombre)] = $l->id;
        }
        foreach (Operario::query()->get(['id', 'nombre']) as $o) {
            $this->operarioPorNombre[$this->normalizarClave($o->nombre)] = $o->id;
        }
    }

    private function indexarOperarios(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $operariosSheet): void
    {
        $max = (int) $operariosSheet->getHighestDataRow();
        for ($r = 2; $r <= $max; $r++) {
            $raw = $operariosSheet->getCell("B{$r}")->getValue();
            if ($raw === null || $raw === '') {
                continue;
            }
            $key = (int) round((float) $raw);
            $porIndice = [];
            for ($colIdx = 2; $colIdx <= 10; $colIdx++) {
                $columnIndex = 1 + $colIdx;
                $porIndice[$colIdx] = $operariosSheet->getCellByColumnAndRow($columnIndex, $r)->getValue();
            }
            $this->operariosPorFechaSerial[$key] = $porIndice;
        }
    }

    /**
     * @return null|array{a:string,b:mixed,c:mixed,d:mixed,e:mixed,f:mixed,g:mixed,h:mixed,i:mixed,j:mixed,k:mixed}
     */
    private function leerFilaRegistro(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws, int $row): ?array
    {
        $a = $ws->getCell("A{$row}")->getValue();
        $c = $ws->getCell("C{$row}")->getValue();
        $d = $ws->getCell("D{$row}")->getValue();
        if (($c === null || $c === '') && ($d === null || $d === '')) {
            return null;
        }

        return [
            'a' => (string) $a,
            'b' => $ws->getCell("B{$row}")->getValue(),
            'c' => $c,
            'd' => $d,
            'e' => $ws->getCell("E{$row}")->getValue(),
            'f' => $ws->getCell("F{$row}")->getValue(),
            'g' => $ws->getCell("G{$row}")->getValue(),
            'h' => $ws->getCell("H{$row}")->getValue(),
            'i' => $ws->getCell("I{$row}")->getValue(),
            'j' => $ws->getCell("J{$row}")->getValue(),
            'k' => $ws->getCell("K{$row}")->getValue(),
        ];
    }

    /**
     * @param  array{a:string,b:mixed,c:mixed,d:mixed,e:mixed,f:mixed,g:mixed,h:mixed,i:mixed,j:mixed,k:mixed}  $fila
     * @return null|array<string, mixed>
     */
    private function mapearFila(array $fila, int $defaultUserId): ?array
    {
        $tipoNombre = $this->normalizarClave((string) $fila['d']);
        $tipoId = $this->tipoPorNombre[$tipoNombre] ?? null;
        if ($tipoId === null) {
            $this->tiposNoEncontrados[(string) $fila['d']] = true;

            return null;
        }

        $productoNombre = trim((string) $fila['e']);
        $productoId = $this->productoPorNombre[$productoNombre] ?? null;
        if ($productoId === null) {
            $this->productosNoEncontrados[$productoNombre] = true;

            return null;
        }

        $codigo = trim((string) $fila['c']);
        if ($codigo === '') {
            return null;
        }

        $usuarioId = $this->resolverUsuarioId((string) $fila['i'], $defaultUserId);

        try {
            $fechaRegistro = $this->excelADateTime($fila['b']);
        } catch (\Throwable) {
            return null;
        }

        try {
            $fechaOperacion = $this->excelADateTime($fila['j'])->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }

        $cantidad = (int) round((float) ($fila['f'] ?? 1));
        if ($cantidad < 1) {
            $cantidad = 1;
        }

        [$ubicacionId, $ladoId] = $this->resolverUbicacionYLado(
            $tipoNombre,
            $fila['g'],
            $fila['h']
        );

        $operarioId = $this->resolverOperarioId(
            $tipoNombre,
            $productoNombre,
            $fila['g'],
            $fila['h'],
            $fila['j']
        );

        $evidencia = $fila['k'];
        $evidenciaPath = ($evidencia !== null && $evidencia !== '') ? trim((string) $evidencia) : null;

        $now = Carbon::now()->format('Y-m-d H:i:s');
        $fechaRegStr = $fechaRegistro->format('Y-m-d H:i:s');

        return [
            'fecha_registro' => $fechaRegStr,
            'fecha_operacion' => $fechaOperacion,
            'codigo' => $codigo,
            'producto_id' => $productoId,
            'tipo_hallazgo_id' => $tipoId,
            'ubicacion_id' => $ubicacionId,
            'lado_id' => $ladoId,
            'cantidad' => $cantidad,
            'evidencia_path' => $evidenciaPath,
            'operario_id' => $operarioId,
            'usuario_id' => $usuarioId,
            'observacion' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function resolverUsuarioId(string $excelUsuario, int $defaultUserId): int
    {
        $excelUsuario = trim($excelUsuario);
        if ($excelUsuario === '') {
            return $defaultUserId;
        }

        $u = User::query()
            ->whereRaw('LOWER(username) = ?', [mb_strtolower($excelUsuario)])
            ->orWhereRaw('LOWER(email) = ?', [mb_strtolower($excelUsuario)])
            ->orWhereRaw('LOWER(name) = ?', [mb_strtolower($excelUsuario)])
            ->first();

        if ($u) {
            return (int) $u->id;
        }

        $this->usuariosNoEncontrados[$excelUsuario] = true;

        return $defaultUserId;
    }

    /**
     * Replica la columna devuelta por VLOOKUP en la fórmula de la columna Operario del Excel.
     */
    private function indiceColumnaOperarios(string $tipoNorm, string $producto, mixed $g, mixed $h): int
    {
        $mc1 = 'Media Canal 1 Lengua';
        $mc2 = 'Media Canal 2 Cola';
        $gStr = $g !== null && $g !== '' ? $this->normalizarClave((string) $g) : '';
        $hStr = $h !== null && $h !== '' ? trim((string) $h) : '';

        $hPar = $hStr !== '' && strcasecmp($hStr, 'Par') === 0;
        $hImpar = $hStr !== '' && strcasecmp($hStr, 'Impar') === 0;

        if ($tipoNorm === $this->normalizarClave('COBERTURA DE GRASA') && $producto === $mc1 && $hPar) {
            return 2;
        }
        if ($tipoNorm === $this->normalizarClave('CORTE EN PIERNAS') && $producto === $mc1 && $hPar) {
            return 2;
        }
        if ($tipoNorm === $this->normalizarClave('COBERTURA DE GRASA') && $producto === $mc1 && $hImpar) {
            return 3;
        }
        if ($tipoNorm === $this->normalizarClave('CORTE EN PIERNAS') && $producto === $mc1 && $hImpar) {
            return 3;
        }
        if ($tipoNorm === $this->normalizarClave('COBERTURA DE GRASA') && $producto === $mc2 && $hPar) {
            return 4;
        }
        if ($tipoNorm === $this->normalizarClave('CORTE EN PIERNAS') && $producto === $mc2 && $hPar) {
            return 4;
        }
        if ($tipoNorm === $this->normalizarClave('COBERTURA DE GRASA') && $producto === $mc2 && $hImpar) {
            return 5;
        }
        if ($tipoNorm === $this->normalizarClave('CORTE EN PIERNAS') && $producto === $mc2 && $hImpar) {
            return 5;
        }
        if ($tipoNorm === $this->normalizarClave('SOBREBARRIGA ROTA') && $producto === $mc1) {
            return 6;
        }
        if ($tipoNorm === $this->normalizarClave('SOBREBARRIGA ROTA') && $producto === $mc2) {
            return 7;
        }
        if ($tipoNorm === $this->normalizarClave('COBERTURA DE GRASA') && $producto === $mc1 && $gStr === 'CADERA') {
            return 8;
        }
        if ($tipoNorm === $this->normalizarClave('COBERTURA DE GRASA') && $producto === $mc2 && $gStr === 'CADERA') {
            return 9;
        }

        return 10;
    }

    private function resolverOperarioId(string $tipoNorm, string $producto, mixed $g, mixed $h, mixed $jSerial): ?int
    {
        $colIdx = $this->indiceColumnaOperarios($tipoNorm, $producto, $g, $h);
        $key = (int) round((float) $jSerial);
        $celda = $this->operariosPorFechaSerial[$key][$colIdx] ?? null;
        if ($celda === null || $celda === '') {
            return null;
        }

        $nombreExcel = trim((string) $celda);
        if ($nombreExcel === '') {
            return null;
        }

        $clave = $this->normalizarClave($nombreExcel);
        $id = $this->operarioPorNombre[$clave] ?? null;
        if ($id === null) {
            $this->operariosNoEncontrados[$nombreExcel] = true;
        }

        return $id;
    }

    private function excelADateTime(mixed $value): Carbon
    {
        if ($value === null || $value === '') {
            throw new \InvalidArgumentException;
        }
        if (is_numeric($value)) {
            $dt = ExcelDate::excelToDateTimeObject((float) $value);

            return Carbon::instance($dt);
        }

        return Carbon::parse((string) $value);
    }

    /**
     * @return array{0: ?int, 1: ?int} [ubicacion_id, lado_id]
     */
    private function resolverUbicacionYLado(string $tipoNorm, mixed $g, mixed $h): array
    {
        $ubicacionId = null;
        $ladoId = null;

        $cobertura = $this->normalizarClave('COBERTURA DE GRASA');
        $cortePiernas = $this->normalizarClave('CORTE EN PIERNAS');

        if ($tipoNorm === $cobertura && $g !== null && $g !== '') {
            $uKey = $this->normalizarClave((string) $g);
            if (isset($this->ubicacionPorNombre[$uKey])) {
                $ubicacionId = $this->ubicacionPorNombre[$uKey];
            }
            if ($uKey === 'PIERNA' && $h !== null && $h !== '') {
                $lKey = $this->normalizarClave((string) $h);
                $ladoId = $this->ladoPorNombre[$lKey] ?? null;
            }
        } elseif ($tipoNorm === $cortePiernas && $h !== null && $h !== '') {
            $lKey = $this->normalizarClave((string) $h);
            $ladoId = $this->ladoPorNombre[$lKey] ?? null;
        }

        return [$ubicacionId, $ladoId];
    }

    private function normalizarClave(string $s): string
    {
        $s = preg_replace('/\s+/u', ' ', trim($s)) ?? '';

        return mb_strtoupper($s, 'UTF-8');
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertarSinEventos(array $rows): void
    {
        RegistroHallazgo::withoutEvents(function () use ($rows) {
            DB::table('registros_hallazgos')->insert($rows);
        });
    }

    /**
     * Lee columna J (fecha operación) y precarga huellas en BD solo para esas fechas (evitar duplicados al reimportar).
     */
    private function warmupHuellasExistentes(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $registros, int $highestRow): void
    {
        $fechasUnique = [];
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                $j = $registros->getCell("J{$row}")->getValue();
                if ($j === null || $j === '') {
                    continue;
                }
                $fechaOperacion = $this->excelADateTime($j)->format('Y-m-d');
                $fechasUnique[$fechaOperacion] = true;
            } catch (\Throwable) {
                continue;
            }
        }

        foreach (array_keys($fechasUnique) as $f) {
            RegistroHallazgo::query()
                ->where('fecha_operacion', $f)
                ->get([
                    'fecha_operacion',
                    'codigo',
                    'tipo_hallazgo_id',
                    'producto_id',
                    'ubicacion_id',
                    'lado_id',
                    'cantidad',
                    'evidencia_path',
                ])
                ->each(function (RegistroHallazgo $r): void {
                    $p = [
                        'fecha_operacion' => $r->fecha_operacion instanceof \DateTimeInterface
                            ? $r->fecha_operacion->format('Y-m-d')
                            : (string) $r->fecha_operacion,
                        'codigo' => $r->codigo,
                        'tipo_hallazgo_id' => $r->tipo_hallazgo_id,
                        'producto_id' => $r->producto_id,
                        'ubicacion_id' => $r->ubicacion_id,
                        'lado_id' => $r->lado_id,
                        'cantidad' => $r->cantidad,
                        'evidencia_path' => $r->evidencia_path,
                    ];
                    $this->huellasExistentes[self::claveHuellaDesdePayload($p)] = true;
                });
        }
    }

    /**
     * Igual dedupe comando `registros-hallazgos:dedupe`.
     *
     * @param  array<string, mixed>  $payload
     */
    private static function claveHuellaDesdePayload(array $payload): string
    {
        $fecha = isset($payload['fecha_operacion']) && is_object($payload['fecha_operacion']) && method_exists($payload['fecha_operacion'], 'format')
            ? $payload['fecha_operacion']->format('Y-m-d')
            : (string) $payload['fecha_operacion'];

        $evid = isset($payload['evidencia_path']) && $payload['evidencia_path'] !== null && $payload['evidencia_path'] !== ''
            ? (string) $payload['evidencia_path']
            : '';

        return implode("\t", [
            $fecha,
            (string) $payload['codigo'],
            (string) $payload['tipo_hallazgo_id'],
            (string) $payload['producto_id'],
            (string) (($payload['ubicacion_id'] !== null ? $payload['ubicacion_id'] : '')),
            (string) (($payload['lado_id'] !== null ? $payload['lado_id'] : '')),
            (string) $payload['cantidad'],
            $evid,
        ]);
    }

    private function imprimirAdvertencias(): void
    {
        if ($this->usuariosNoEncontrados !== []) {
            $this->warn('Usuarios del Excel sin coincidencia en BD (se usó --user-id por defecto):');
            $this->line('  '.implode(', ', array_keys($this->usuariosNoEncontrados)));
        }
        if ($this->tiposNoEncontrados !== []) {
            $this->warn('Tipos de hallazgo no reconocidos (filas omitidas):');
            $this->line('  '.implode(', ', array_keys($this->tiposNoEncontrados)));
        }
        if ($this->productosNoEncontrados !== []) {
            $this->warn('Productos no reconocidos (filas omitidas):');
            $this->line('  '.implode(', ', array_keys($this->productosNoEncontrados)));
        }
        if ($this->operariosNoEncontrados !== []) {
            $this->warn('Nombres de operario del Excel sin coincidencia en BD (operario_id quedó null):');
            $this->line('  '.implode(', ', array_keys($this->operariosNoEncontrados)));
        }
    }
}
