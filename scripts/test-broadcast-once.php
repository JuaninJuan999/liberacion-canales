<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    broadcast(new App\Events\HallazgoPublicado([
        'origen' => 'hallazgos',
        'usuario_registro_id' => 999999,
        'tipo_nombre' => 'Prueba broadcast',
        'usuario_nombre' => 'CLI',
        'codigo' => 'TEST',
        'producto_nombre' => 'Media Canal 2 Cola',
        'lado_nombre' => 'Impar',
        'media_etiqueta' => 'Media Canal 2',
        'registro_id' => 0,
        'registrado_en' => now()->toIso8601String(),
    ]));
    fwrite(STDOUT, "Broadcast enviado sin excepción.\n");
} catch (Throwable $e) {
    fwrite(STDERR, 'ERROR: '.$e->getMessage()."\n");
    exit(1);
}
