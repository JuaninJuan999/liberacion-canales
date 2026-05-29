<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cierre operativo del turno PCC (hora del día de la aplicación)
    |--------------------------------------------------------------------------
    |
    | Hora H (0-23 inclusive): antes de esa hora, el día operativo PCC se toma como
    | el día calendario anterior. Permite cargar después de medianoche usando la misma
    | fecha de insensibilización en trazabilidad y no duplicar verificaciones de turno.
    | Alineado con el cierre de día usado en hallazgos (7:00). La cola PCC incluye
    | insensibilizaciones desde las H del día D hasta antes de las H del día D+1,
    | aunque trazabilidad guarde fecha_registro del día calendario siguiente.
    |
    */
    'turno_hora_fin' => (int) env('VERIFICACION_PCC_TURNO_HORA_FIN', 7),

];
