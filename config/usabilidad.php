<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cierre por inactividad (módulo Tiempo de usabilidad)
    |--------------------------------------------------------------------------
    |
    | Minutos sin actividad (ultima_actividad) tras los cuales la sesión de
    | tracking se marca como finalizada aunque el usuario no haya cerrado sesión.
    | Por defecto usa SESSION_LIFETIME; override con USABILIDAD_INACTIVIDAD_MINUTOS.
    |
    */
    'inactividad_minutos' => (int) env(
        'USABILIDAD_INACTIVIDAD_MINUTOS',
        env('SESSION_LIFETIME', 120)
    ),

];
